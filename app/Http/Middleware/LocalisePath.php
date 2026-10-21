<?php

namespace App\Http\Middleware;

use App\Services\LocaleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reads the language out of the address and takes it off the path.
 *
 * A site in more than one language gives each language its own addresses:
 * `/de/about` is the German About page. The prefix is removed before the
 * router sees the request, so the routes behind it stay the same ones the
 * unprefixed site uses — the language is a prefix on the address, not a
 * second copy of every route.
 */
class LocalisePath
{
    public function __construct(private readonly LocaleService $locales) {}

    /**
     * Paths that never carry a language.
     *
     * @var list<string>
     */
    private const EXCLUDED = ['api', 'admin', 'up', 'telescope', 'horizon'];

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is(...self::EXCLUDED)) {
            return $next($request);
        }

        $prefix = $this->prefixFor($request);

        if ($prefix !== null) {
            $this->rewrite($request, $this->withoutPrefix($request));
            $request->attributes->set('locale_prefix', $prefix);
            app()->setLocale($prefix);
        }

        return $next($request);
    }

    /**
     * Point the request at a different path.
     *
     * The path is read from REQUEST_URI, so that is what has to change;
     * the query string is carried across untouched.
     */
    private function rewrite(Request $request, string $path): void
    {
        $query = $request->server->get('QUERY_STRING', '');
        $query = is_string($query) && $query !== '' ? '?'.$query : '';

        $request->server->set('REQUEST_URI', $path.$query);
        $request->server->set('PATH_INFO', $path);

        // Symfony works the path out once and keeps it, so the two caches
        // it holds are cleared by hand: the address has changed under it,
        // and the router must see the new one.
        foreach (['pathInfo', 'requestUri'] as $property) {
            $reflection = new \ReflectionProperty(SymfonyRequest::class, $property);
            $reflection->setValue($request, null);
        }
    }

    /**
     * The language the address is in, if it carries one.
     */
    private function prefixFor(Request $request): ?string
    {
        $segments = explode('/', trim($request->path(), '/'));

        $code = $segments[0] ?? '';

        if ($code === '' || ! preg_match('/^[a-z]{2}(-[a-z]{2})?$/i', $code)) {
            return null;
        }

        return $this->locales->isActive($code) ? strtolower($code) : null;
    }

    /**
     * The path with the language prefix taken off.
     */
    private function withoutPrefix(Request $request): string
    {
        $segments = explode('/', trim($request->path(), '/'));

        array_shift($segments);

        return '/'.implode('/', $segments);
    }
}
