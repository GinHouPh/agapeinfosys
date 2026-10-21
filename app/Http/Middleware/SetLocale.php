<?php

namespace App\Http\Middleware;

use App\Services\LocaleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets the language the request is served in.
 */
class SetLocale
{
    public function __construct(private readonly LocaleService $locales) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // A language in the address beats the one the visitor chose
        // earlier: they asked for this address.
        $prefix = $request->attributes->get('locale_prefix');

        if (! is_string($prefix) || $prefix === '') {
            $locale = $this->locales->current();

            if ($locale !== null) {
                app()->setLocale($locale->code);
            }
        }

        return $next($request);
    }
}
