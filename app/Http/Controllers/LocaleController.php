<?php

namespace App\Http\Controllers;

use App\Models\Locale;
use App\Services\LocaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Lets a visitor choose the language the site is served in.
 */
class LocaleController extends Controller
{
    /**
     * Remember the visitor's choice and send them back where they were.
     *
     * The address they land on carries the language they chose, so the
     * links on that page do too.
     */
    public function choose(Request $request, string $code, LocaleService $locales): RedirectResponse
    {
        $locale = Locale::query()->active()->where('code', $code)->first();

        if ($locale === null) {
            return back()->with('status', 'That language is not available.');
        }

        $locales->choose($locale->code);

        return redirect($this->prefixed($request, $locale->code, $locales->defaultCode()))
            ->with('status', 'Language set to '.$locale->name.'.');
    }

    /**
     * The page the visitor was on, in the language they chose.
     */
    private function prefixed(Request $request, string $code, ?string $defaultCode): string
    {
        $referer = $request->header('referer');
        $path = is_string($referer) && $referer !== ''
            ? (string) parse_url($referer, PHP_URL_PATH)
            : '/';

        $segments = explode('/', trim($path, '/'));

        if (($segments[0] ?? '') !== '' && preg_match('/^[a-z]{2}$/i', $segments[0]) === 1) {
            array_shift($segments);
        }

        $path = '/'.implode('/', $segments);

        if ($code === $defaultCode) {
            return $path === '/' ? '/' : $path;
        }

        return '/'.$code.($path === '/' ? '' : $path);
    }
}
