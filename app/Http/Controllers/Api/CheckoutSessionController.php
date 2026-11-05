<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Payments\CheckoutService;
use App\Services\Payments\Exceptions\GatewayException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Generates payment sessions for mobile and client SDK checkouts (e.g. SMT mobile app).
 */
class CheckoutSessionController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout) {}

    /**
     * POST /api/v1/checkout/session
     *
     * The client must send its public X-Agape-Key; the product that key
     * belongs to decides which catalogue and plans are charged.
     */
    public function store(Request $request): JsonResponse
    {
        $key = (string) $request->header('X-Agape-Key', '');

        if ($key === '') {
            return response()->json([
                'success' => false,
                'message' => 'Missing X-Agape-Key header.',
            ], 401);
        }

        $product = Product::query()->active()->where('api_key', $key)->first();

        if ($product === null) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found or inactive.',
            ], 404);
        }

        $validated = $request->validate([
            'plan' => ['required', 'string', 'max:120'],
            'subscriber_key' => ['required', 'string', 'max:190'],
            'name' => ['nullable', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:30'],
            'gateway' => ['nullable', 'string', 'max:40'],
            'return_url' => ['nullable', 'url', 'max:500'],
        ]);

        try {
            [$order, $session] = $this->checkout->startCheckout(
                $product->slug,
                $validated['plan'],
                [
                    'name' => $validated['name'] ?? null,
                    'email' => $validated['email'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                    'subscriber_key' => $validated['subscriber_key'],
                    'return_url' => $validated['return_url'] ?? null,
                ],
                $validated['gateway'] ?? 'cashfree',
            );
        } catch (GatewayException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        $payload = $session->payload ?? [];

        return response()->json([
            'success' => true,
            'order_id' => $order->reference,
            'amount' => $order->amount,
            'currency' => $order->currency,
            'payment_session_id' => $payload['payment_session_id'] ?? null,
            // Recurring plans open a mandate checkout instead; the client
            // must pick this up rather than expecting a payment session.
            'subscription_session_id' => $payload['subscription_session_id'] ?? null,
            'environment' => ($payload['mode'] ?? 'sandbox') === 'production' ? 'PRODUCTION' : 'SANDBOX',
            'session' => $payload,
        ]);
    }
}
