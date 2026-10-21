<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows the request only when the user holds the named permission.
 *
 * Superusers bypass the check. Everyone else needs the permission granted
 * through one of their roles.
 */
class EnsurePermission
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->guest(route('admin.login'));
        }

        if (! $user->hasPermission($permission)) {
            abort(403, 'You do not have permission to do that.');
        }

        return $next($request);
    }
}
