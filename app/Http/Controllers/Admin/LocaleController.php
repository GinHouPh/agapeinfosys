<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Concerns\RecordsActivity;
use App\Models\Locale;
use App\Services\LocaleService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    use RecordsActivity;

    /**
     * The languages the site offers.
     */
    public function index(LocaleService $locales): View
    {
        return view('admin.locales.index', [
            'locales' => Locale::query()->orderByDesc('is_default')->orderBy('code')->get(),
            'current' => $locales->current(),
        ]);
    }

    /**
     * Add a language.
     */
    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'code' => ['required', 'string', 'max:10', 'alpha_dash', 'unique:locales,code'],
            'name' => ['required', 'string', 'max:80'],
            'native_name' => ['nullable', 'string', 'max:80'],
            'direction' => ['nullable', Rule::in(['ltr', 'rtl'])],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        $attributes['is_default'] = $request->boolean('is_default');
        $attributes['direction'] = $attributes['direction'] ?? 'ltr';

        $locale = Locale::query()->create($attributes);

        if ($locale->is_default) {
            Locale::query()->where('id', '!=', $locale->id)->update(['is_default' => false]);
        }

        $this->recordActivity('created', $locale, 'Added the language "'.$locale->name.'".');

        return redirect()
            ->route('admin.locales.index')
            ->with('status', 'Language added.');
    }

    /**
     * Make a language the site's default.
     */
    public function activate(Locale $locale): RedirectResponse
    {
        Locale::query()->where('id', '!=', $locale->id)->update(['is_default' => false]);

        $locale->update(['is_default' => true, 'is_active' => true]);

        $this->recordActivity('updated', $locale, 'Made "'.$locale->name.'" the default language.');

        return redirect()
            ->route('admin.locales.index')
            ->with('status', 'Default language updated.');
    }

    /**
     * Remove a language and its translations.
     */
    public function destroy(Locale $locale): RedirectResponse
    {
        if ($locale->is_default) {
            return back()->withErrors(['locale' => 'The default language cannot be deleted.']);
        }

        $name = $locale->name;

        $locale->delete();

        $this->recordActivity('deleted', null, 'Deleted the language "'.$name.'".');

        return redirect()
            ->route('admin.locales.index')
            ->with('status', 'Language deleted.');
    }
}
