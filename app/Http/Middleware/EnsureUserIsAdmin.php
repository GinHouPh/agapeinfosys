<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the control panel to superusers and users holding a permission.
 *
 * The "is_admin" flag is the superuser switch: it opens every door. Other
 * admins reach only the routes their roles grant, and each of those routes
 * carries its own "can:" requirement.
 */
class EnsureUserIsAdmin
{
    /**
     * Redirect authenticated non-admins away from the control panel.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->guest(route('admin.login'));
        }

        if (! $user->isAdmin() && ! $user->hasAnyPermission()) {
            abort(403, 'This area is restricted to administrators.');
        }

        return $next($request);
    }
}
