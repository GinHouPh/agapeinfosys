<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Entitlement API consumed by tongdon.com, smt.com, motionmix.tv etc.
 *
 * All routes are authenticated with SignProductApiRequests and scoped to
 * the product the API key belongs to.
 */
class EntitlementController extends Controller
{
    /**
     * Resolve the authenticated product from middleware attributes.
     */
    private function product(Request $request): Product
    {
        return $request->attributes->get('product');
    }

    /**
     * GET /api/v1/entitlement?subscriber_key=... — current subscription state.
     */
    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subscriber_key' => ['required', 'string', 'max:190'],
        ]);

        $product = $this->product($request);

        $subscription = Subscription::query()
            ->where('product_id', $product->getKey())
            ->where('subscriber_key', $validated['subscriber_key'])
            ->with(['plan', 'lastOrder'])
            ->first();

        if ($subscription === null) {
            return response()->json([
                'subscriber_key' => $validated['subscriber_key'],
                'entitled' => false,
                'subscription' => null,
            ]);
        }

        return response()->json([
            'subscriber_key' => $validated['subscriber_key'],
            'entitled' => $subscription->isEntitled(),
            'subscription' => $subscription->toPublicArray(),
        ]);
    }

    /**
     * GET /api/v1/orders/{reference} — order status for a given subscriber.
     */
    public function order(Request $request, string $reference): JsonResponse
    {
        $product = $this->product($request);

        $order = Order::query()
            ->where('product_id', $product->getKey())
            ->where('reference', $reference)
            ->with(['plan'])
            ->firstOrFail();

        return response()->json($order->toPublicArray());
    }

    /**
     * POST /api/v1/orders/lookup — find a subscriber's latest order by email.
     */
    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:190'],
        ]);

        $product = $this->product($request);

        $order = Order::query()
            ->where('product_id', $product->getKey())
            ->where('email', $validated['email'])
            ->orderByDesc('id')
            ->with(['plan'])
            ->first();

        return response()->json([
            'order' => $order?->toPublicArray(),
            'subscription' => Subscription::query()
                ->where('product_id', $product->getKey())
                ->where('email', $validated['email'])
                ->where('status', SubscriptionStatus::Active)
                ->with('plan')
                ->first()
                ?->toPublicArray(),
        ]);
    }

    /**
     * GET /api/v1/entitlement/status?subscriber_key=...&product=smt
     *
     * Check for client/mobile apps using X-Agape-Key header or product slug.
     */
    public function status(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subscriber_key' => ['required', 'string', 'max:190'],
            'product' => ['nullable', 'string', 'max:100'],
        ]);

        $key = (string) $request->header('X-Agape-Key', '');
        $productSlug = (string) ($validated['product'] ?? 'smt');

        $product = $key !== ''
            ? Product::query()->active()->where('api_key', $key)->first()
            : Product::query()->active()->where('slug', $productSlug)->first();

        if ($product === null) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found or inactive.',
            ], 404);
        }

        $subscription = Subscription::query()
            ->where('product_id', $product->getKey())
            ->where('subscriber_key', $validated['subscriber_key'])
            ->with(['plan', 'lastOrder'])
            ->first();

        if ($subscription === null) {
            return response()->json([
                'subscriber_key' => $validated['subscriber_key'],
                'entitled' => false,
                'subscription' => null,
            ]);
        }

        return response()->json([
            'subscriber_key' => $validated['subscriber_key'],
            'entitled' => $subscription->isEntitled(),
            'subscription' => $subscription->toPublicArray(),
        ]);
    }
}
