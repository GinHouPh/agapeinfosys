<?php

namespace App\Http\Controllers\Admin;

use App\Builder\DesignTokens;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDesignThemeRequest;
use App\Http\Requests\UpdateDesignThemeRequest;
use App\Models\Concerns\RecordsActivity;
use App\Models\DesignTheme;
use App\Services\ThemeCompiler;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class DesignController extends Controller
{
    use RecordsActivity;

    /**
     * Every theme, with the one currently live marked.
     */
    public function index(): View
    {
        $themes = DesignTheme::query()
            ->orderBy('name')
            ->get();

        return view('admin.design.index', compact('themes'));
    }

    /**
     * Create a theme seeded with the default token values.
     */
    public function store(StoreDesignThemeRequest $request): RedirectResponse
    {
        $theme = DesignTheme::query()->create([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'is_active' => $request->boolean('is_active'),
            'tokens' => $this->defaultTokens(),
            'created_by' => $request->user()?->getKey(),
        ]);

        $this->recordActivity('created', $theme, 'Created the design theme "'.$theme->name.'".');

        return redirect()
            ->route('admin.design.edit', $theme)
            ->with('status', 'Theme created — tune its tokens below.');
    }

    /**
     * The token editor.
     */
    public function edit(DesignTheme $theme, ThemeCompiler $compiler): View
    {
        return view('admin.design.edit', [
            'theme' => $theme,
            'palette' => DesignTokens::COLORS,
            'fonts' => config('design.fonts', []),
            'scales' => config('design.scales', []),
            'defaults' => config('design.tokens', []),
            'preview' => $compiler->compile($theme),
        ]);
    }

    /**
     * Update a theme's details and tokens.
     */
    public function update(UpdateDesignThemeRequest $request, DesignTheme $theme): RedirectResponse
    {
        $attributes = $request->validated();
        unset($attributes['tokens']);

        $attributes['is_active'] = $request->boolean('is_active');

        $theme->fill($attributes);

        if (is_array($request->input('tokens'))) {
            $theme->tokens = $this->mergeTokens($theme->tokens, $request->input('tokens'));
        }

        $theme->save();

        $this->recordActivity('updated', $theme, 'Updated the design theme "'.$theme->name.'".');

        return redirect()
            ->route('admin.design.edit', $theme)
            ->with('status', 'Theme updated.');
    }

    /**
     * Delete a theme.
     */
    public function destroy(DesignTheme $theme): RedirectResponse
    {
        if ($theme->is_active) {
            return back()->withErrors(['theme' => 'Switch another theme on before deleting this one.']);
        }

        $name = $theme->name;

        $theme->delete();

        $this->recordActivity('deleted', null, 'Deleted the design theme "'.$name.'".');

        return redirect()
            ->route('admin.design.index')
            ->with('status', 'Theme deleted.');
    }

    /**
     * Make one theme the live one.
     */
    public function activate(DesignTheme $theme): RedirectResponse
    {
        DB::transaction(function () use ($theme): void {
            DesignTheme::query()->where('id', '!=', $theme->id)->update(['is_active' => false]);

            $theme->update(['is_active' => true, 'is_default' => true]);
        });

        $this->recordActivity('updated', $theme, 'Made the design theme "'.$theme->name.'" live.');

        return redirect()
            ->route('admin.design.index')
            ->with('status', 'Theme "'.$theme->name.'" is now live.');
    }

    /**
     * The token values a new theme starts from.
     *
     * @return array<string, mixed>
     */
    private function defaultTokens(): array
    {
        $tokens = config('design.tokens', []);

        $tokens['font'] = [
            'display' => array_key_first(config('design.fonts.display', [])),
            'body' => array_key_first(config('design.fonts.body', [])),
        ];

        $tokens['radius'] = ['card' => 'none'];
        $tokens['shadow'] = ['card' => 'none'];

        return $tokens;
    }

    /**
     * Merge posted tokens over the theme's own.
     *
     * @param  array<string, mixed>  $current
     * @param  array<string, mixed>  $incoming
     * @return array<string, mixed>
     */
    private function mergeTokens(array $current, array $incoming): array
    {
        foreach (['dark', 'light'] as $scheme) {
            if (! is_array($incoming[$scheme] ?? null)) {
                continue;
            }

            $current[$scheme] = array_merge(
                is_array($current[$scheme] ?? null) ? $current[$scheme] : [],
                $incoming[$scheme],
            );
        }

        foreach (['font', 'radius', 'shadow'] as $group) {
            if (! is_array($incoming[$group] ?? null)) {
                continue;
            }

            $current[$group] = array_merge(
                is_array($current[$group] ?? null) ? $current[$group] : [],
                $incoming[$group],
            );
        }

        return $current;
    }
}
