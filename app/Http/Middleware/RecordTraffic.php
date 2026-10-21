<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records a line for every public page request.
 *
 * The control panel, the API and the shop's own requests are left out: this
 * is a record of what visitors read, not of what the platform does to
 * itself.
 */
class RecordTraffic
{
    /**
     * Paths that are never recorded.
     *
     * @var list<string>
     */
    private const IGNORED = ['admin', 'api', 'up', 'telescope', 'horizon'];

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $this->record($request, $response);
        } catch (\Throwable $exception) {
            // A view that cannot be recorded is not a reason for the page
            // itself to fail.
            Log::warning('Traffic could not be recorded: '.$exception->getMessage());
        }

        return $response;
    }

    /**
     * Write one line for the request.
     */
    private function record(Request $request, Response $response): void
    {
        if (! $request->isMethod('GET') || $request->is(...self::IGNORED)) {
            return;
        }

        $path = '/'.ltrim($request->path(), '/');

        if ($path === '/' && ! config('traffic.count_home', true)) {
            return;
        }

        $user = $request->user();

        PageView::query()->create([
            'path' => mb_substr($path, 0, 500),
            'method' => $request->method(),
            'status' => $response->getStatusCode(),
            'referrer' => $this->referrer($request),
            'visitor' => $this->visitor($request, $user),
            'ip_hash' => $this->ipHash($request),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 400),
            'user_id' => $user instanceof User ? $user->getKey() : null,
        ]);
    }

    /**
     * The page the visitor came from.
     */
    private function referrer(Request $request): ?string
    {
        $referrer = $request->header('referer');

        if (! is_string($referrer) || $referrer === '') {
            return null;
        }

        // Only the site itself is worth keeping; another site's address
        // says nothing about this one.
        if (! str_contains($referrer, (string) config('app.url'))) {
            return null;
        }

        return mb_substr($referrer, 0, 500);
    }

    /**
     * A key that recognises the same visitor across their requests.
     */
    private function visitor(Request $request, mixed $user): string
    {
        if ($user instanceof User) {
            return 'user:'.$user->getKey();
        }

        $session = $request->hasSession() ? $request->session()->getId() : null;

        return 'anon:'.substr(hash('sha256', ($session ?? '').'|'.($request->userAgent() ?? '')), 0, 32);
    }

    /**
     * A hash of the visitor's address. The address itself is never stored.
     */
    private function ipHash(Request $request): ?string
    {
        $ip = $request->ip();

        if (! is_string($ip) || $ip === '') {
            return null;
        }

        return substr(hash('sha256', $ip.(string) config('app.key')), 0, 64);
    }
}
