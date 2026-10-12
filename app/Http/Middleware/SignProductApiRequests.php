<?php

namespace App\Http\Middleware;

use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticate product API calls.
 *
 * Products send their api_key plus an HMAC-SHA256 signature over
 * "timestamp.method.path" made with their api_secret. Signatures expire
 * after five minutes to blunt replay attacks.
 */
class SignProductApiRequests
{
    private const TOLERANCE_SECONDS = 300;

    public function handle(Request $request, Closure $next): Response
    {
        $key = (string) $request->header('X-Agape-Key', '');
        $timestamp = (string) $request->header('X-Agape-Timestamp', '');
        $signature = (string) $request->header('X-Agape-Signature', '');

        $product = $key !== ''
            ? Product::query()->active()->where('api_key', $key)->first()
            : null;

        if ($product === null) {
            abort(response()->json(['message' => 'Invalid API key.'], 401));
        }

        if ($timestamp === '' || abs(now()->timestamp - (int) $timestamp) > self::TOLERANCE_SECONDS) {
            abort(response()->json(['message' => 'The request timestamp is outside the allowed window.'], 401));
        }

        if ($signature === '') {
            abort(response()->json(['message' => 'Missing request signature.'], 401));
        }

        $expected = hash_hmac(
            'sha256',
            $timestamp.'.'.$request->method().'.'.$request->path(),
            (string) $product->api_secret
        );

        if (! hash_equals($expected, $signature)) {
            abort(response()->json(['message' => 'The request signature is not valid.'], 401));
        }

        $request->attributes->set('product', $product);

        return $next($request);
    }
}
