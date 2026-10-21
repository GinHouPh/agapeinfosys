<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Holds the public site behind a maintenance notice.
 *
 * Admins still get through — a platform you cannot edit while it is down is
 * no use at all.
 */
class MaintenanceMode
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $maintenance = Setting::structured('maintenance', []);

        if (! ($maintenance['enabled'] ?? false)) {
            return $next($request);
        }

        $user = $request->user();

        if ($user instanceof User && $user->hasAnyPermission()) {
            return $next($request);
        }

        return response()->view('errors.maintenance', [
            'message' => $maintenance['message'] ?? 'We are carrying out scheduled work. We will be back shortly.',
        ], 503);
    }
}
