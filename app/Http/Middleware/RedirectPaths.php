<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends visitors of an old address to its replacement.
 *
 * The lookup is one indexed read on a table that holds a handful of rows.
 */
class RedirectPaths
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        $path = '/'.ltrim($request->path(), '/');

        $redirect = Redirect::query()
            ->active()
            ->where('from_path', $path)
            ->first();

        if ($redirect === null) {
            return $next($request);
        }

        $redirect->increment('hits');

        return redirect($redirect->to_path, $redirect->status_code);
    }
}
