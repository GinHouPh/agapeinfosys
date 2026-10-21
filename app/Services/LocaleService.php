<?php

namespace App\Services;

use App\Models\Locale;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;

/**
 * Works out which language the visitor is reading in.
 *
 * A visitor picks a language; the choice is kept in a cookie. A visitor
 * who has not chosen gets the browser's preference, and failing that the
 * site's default language.
 */
class LocaleService
{
    /**
     * The name of the cookie the choice is kept in.
     */
    public const COOKIE = 'agape_locale';

    /**
     * The address of the current page in another language.
     *
     * Used for the `hreflang` links a crawler reads to find the same page
     * in every language the site is in.
     */
    public function alternateUrl(string $code, bool $isDefault, string $path): string
    {
        $host = request()->getSchemeAndHttpHost();
        $path = '/'.ltrim($path, '/');

        return $host.($isDefault ? $path : '/'.$code.$path);
    }

    /**
     * Whether a language is one the site offers.
     */
    public function isActive(string $code): bool
    {
        return Locale::query()->active()->where('code', $code)->exists();
    }

    /**
     * The code of the default language, if there is one.
     */
    public function defaultCode(): ?string
    {
        return Locale::default()?->code;
    }

    /**
     * The language in play for this request.
     */
    public function current(): ?Locale
    {
        $code = $this->currentCode();

        if ($code === null) {
            return Locale::default();
        }

        return Locale::query()->active()->where('code', $code)->first() ?? Locale::default();
    }

    /**
     * The code of the locale in play for this request.
     */
    public function currentCode(): ?string
    {
        $chosen = $this->chosenCode();

        if ($chosen !== null && Locale::query()->active()->where('code', $chosen)->exists()) {
            return $chosen;
        }

        return $this->browserCode() ?? Locale::default()?->code;
    }

    /**
     * Remember the visitor's choice.
     */
    public function choose(string $code): void
    {
        Cookie::queue(Cookie::forever(self::COOKIE, $code));
    }

    /**
     * The languages the site offers.
     *
     * @return Collection<int, Locale>
     */
    public function available(): Collection
    {
        return Locale::active();
    }

    /**
     * The code the visitor chose, if they chose one.
     */
    private function chosenCode(): ?string
    {
        $code = request()->cookie(self::COOKIE);

        return is_string($code) && $code !== '' ? $code : null;
    }

    /**
     * The language the browser asked for.
     */
    private function browserCode(): ?string
    {
        $header = (string) request()->header('Accept-Language');

        if ($header === '') {
            return null;
        }

        foreach (explode(',', $header) as $part) {
            $code = strtolower(trim(explode(';', $part)[0]));

            if ($code === '') {
                continue;
            }

            $short = explode('-', $code)[0];

            if (Locale::query()->active()->where('code', $code)->exists()) {
                return $code;
            }

            if (Locale::query()->active()->where('code', $short)->exists()) {
                return $short;
            }
        }

        return null;
    }
}
