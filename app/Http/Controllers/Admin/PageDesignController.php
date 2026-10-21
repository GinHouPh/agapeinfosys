<?php

namespace App\Http\Controllers\Admin;

use App\Builder\DesignTokens;
use App\Http\Controllers\Controller;
use App\Models\Concerns\RecordsActivity;
use App\Models\Page;
use App\Services\ThemeCompiler;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PageDesignController extends Controller
{
    use RecordsActivity;

    /**
     * The page's own colour overrides.
     */
    public function edit(Page $page): View
    {
        return view('admin.pages.design', [
            'page' => $page,
            'palette' => DesignTokens::COLORS,
            'overrides' => $page->tokenOverrides(),
        ]);
    }

    /**
     * Save the page's colour overrides.
     *
     * A page may change the accent it renders with without changing the
     * theme every other page uses. Anything left blank falls back to the
     * theme.
     */
    public function update(Request $request, Page $page, ThemeCompiler $compiler): RedirectResponse
    {
        $attributes = $request->validate([
            'overrides' => ['nullable', 'array'],
            'overrides.dark' => ['nullable', 'array'],
            'overrides.light' => ['nullable', 'array'],
        ]);

        $overrides = [];

        foreach (['dark', 'light'] as $scheme) {
            $values = is_array($attributes['overrides'][$scheme] ?? null)
                ? $attributes['overrides'][$scheme]
                : [];

            foreach ($values as $name => $value) {
                if (! DesignTokens::isColor($name) || ! is_string($value) || trim($value) === '') {
                    continue;
                }

                if (! $this->isColour(trim($value))) {
                    continue;
                }

                $overrides[$scheme][$name] = trim($value);
            }
        }

        $page->update(['token_overrides' => $overrides === [] ? null : $overrides]);

        $this->recordActivity('updated', $page, 'Updated the colours of "'.$page->title.'".');

        return redirect()
            ->route('admin.pages.design', $page)
            ->with('status', 'Page colours saved.');
    }

    /**
     * Whether a value is a hex or rgb(a) colour.
     */
    private function isColour(string $value): bool
    {
        return preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $value) === 1
            || preg_match('/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*[\d.]+\s*)?\)$/i', $value) === 1;
    }
}
