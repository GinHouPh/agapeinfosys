<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\UrlGenerator;

/**
 * Generates addresses that carry the language they are in.
 *
 * A site in more than one language gives each language its own addresses:
 * the German About page lives at `/de/about`, not at `/about`. When the
 * visitor is reading a language other than the default, every address the
 * site generates carries that language's prefix.
 *
 * The prefix is read from the request, where the middleware put it, rather
 * than looked up again — this runs for every link on every page.
 */
class LocalisedUrlGenerator extends UrlGenerator
{
    /**
     * Paths that are files, not pages, and never carry a prefix.
     *
     * @var list<string>
     */
    private const FILES = ['/storage/', '/build/', '/vendor/', '/uploads/'];

    /**
     * Generate the address of a named route.
     *
     * @param  Route|string  $route
     * @param  mixed  $parameters
     */
    public function toRoute($route, $parameters = [], $absolute = false): string
    {
        return $this->withPrefix(parent::toRoute($route, $parameters, $absolute));
    }

    /**
     * Generate the address of a path.
     *
     * @param  string  $path
     * @param  mixed  $extra
     * @param  mixed  $secure
     */
    public function to($path, $extra = [], $secure = null): string
    {
        return $this->withPrefix(parent::to($path, $extra, $secure));
    }

    /**
     * Put the language prefix in front of an address.
     */
    private function withPrefix(string $url): string
    {
        $prefix = $this->prefix();

        if ($prefix === '' || $url === '') {
            return $url;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return preg_replace(
                '#^(https?://[^/]+)(/.*)?$#',
                '$1/'.$prefix.'$2',
                $url,
            ) ?? $url;
        }

        if (! str_starts_with($url, '/')) {
            return $url;
        }

        foreach (self::FILES as $file) {
            if (str_starts_with($url, $file)) {
                return $url;
            }
        }

        return '/'.$prefix.$url;
    }

    /**
     * The language prefix for this request, if it has one.
     */
    private function prefix(): string
    {
        $prefix = request()->attributes->get('locale_prefix');

        return is_string($prefix) ? $prefix : '';
    }
}
