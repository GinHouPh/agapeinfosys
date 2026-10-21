<?php

namespace App\Http\Controllers\Shop;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\ShopOrder;
use App\Models\Subscription;
use App\Services\Payments\CheckoutPayload;
use App\Services\Payments\Exceptions\GatewayException;
use App\Services\Payments\GatewayManager;
use App\Services\Shop\Cart;
use App\Services\Shop\CheckoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Public cart checkout: customer details, payment hand-off and results.
 */
class CheckoutController extends Controller
{
    public function __construct(
        private readonly Cart $cart,
        private readonly CheckoutService $checkout,
    ) {}

    /**
     * Customer details form with the cart summary.
     */
    public function show(): View|RedirectResponse
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            return redirect()->route('shop.cart.index');
        }

        return view('shop.checkout', [
            'lines' => $lines,
            'subtotal' => (int) $lines->sum('price'),
        ]);
    }

    /**
     * Create the cart order and either open the inline checkout or redirect
     * to the provider's hosted page.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subscriber_key' => ['nullable', 'string', 'max:190'],
            'gateway' => ['nullable', 'string', 'max:40'],
        ]);

        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            return redirect()->route('shop.cart.index');
        }

        try {
            [$shopOrder, $session] = $this->checkout->startCheckout(
                $lines,
                [
                    'name' => $validated['name'] ?? null,
                    'email' => $validated['email'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                    'subscriber_key' => $validated['subscriber_key'] ?? null,
                ],
                $validated['gateway'] ?? null,
            );
        } catch (GatewayException $exception) {
            return back()
                ->withInput()
                ->withErrors(['subscriber_key' => $exception->getMessage()]);
        }

        $this->cart->clear();

        if ($session->isInline()) {
            return redirect()
                ->route('shop.pay', $shopOrder)
                ->with('checkout_session', $session->payload);
        }

        if ($session->hasForm()) {
            return redirect()
                ->route('shop.pay', $shopOrder)
                ->with('checkout_form', [
                    'url' => $session->redirectUrl,
                    'fields' => $session->formFields,
                ]);
        }

        return redirect()->away((string) $session->redirectUrl);
    }

    /**
     * Payment page: opens the inline SDK or auto-submits the hosted form.
     */
    public function pay(ShopOrder $order): View
    {
        abort_unless($order->status === OrderStatus::Pending, 404);

        $order->loadMissing(['items', 'gateway']);

        $driver = $order->gateway;

        $payload = CheckoutPayload::resolve($order, $driver, session('checkout_session'));
        $form = session('checkout_form');

        return view('shop.pay', [
            'order' => $order,
            'payload' => $payload,
            'form' => is_array($form) ? $form : null,
            'script' => $driver?->checkoutScript(),
        ]);
    }

    /**
     * Result page; also receives hosted-gateway return URLs (GET or POST).
     *
     * A pending cart returned from a hosted provider is verified, confirmed
     * against the provider API and completed here.
     */
    public function result(Request $request, ShopOrder $order): View
    {
        if ($order->status === OrderStatus::Pending) {
            $this->tryCompleteFromReturn($request, $order);

            $order->refresh();

            if ($order->hasExpired()) {
                $order->forceFill(['status' => OrderStatus::Expired])->save();
            }
        }

        return view('shop.result', [
            'order' => $order->load(['items', 'gateway']),
            'subscriptions' => $this->subscriptionsFor($order),
        ]);
    }

    /**
     * Best-effort completion when a hosted gateway sends the customer back.
     *
     * Any failure simply leaves the order pending — the provider webhook is
     * the fallback path.
     */
    private function tryCompleteFromReturn(Request $request, ShopOrder $order): void
    {
        $gateway = $order->gateway;

        if ($gateway === null) {
            return;
        }

        $driver = app(GatewayManager::class)->make($gateway);

        try {
            $driver->verifyCallback($order, $request);
        } catch (GatewayException) {
            return;
        }

        $this->checkout->complete($order, [
            'event' => 'payment.return',
            'driver' => $gateway->driver,
            'reference' => $order->reference,
        ]);
    }

    /**
     * Subscriptions the paid cart granted, keyed by product for the receipt.
     *
     * @return Collection<int, Subscription>
     */
    private function subscriptionsFor(ShopOrder $order): Collection
    {
        if ($order->status !== OrderStatus::Paid) {
            return collect();
        }

        $productIds = $order->items->pluck('product_id')->unique();

        return Subscription::query()
            ->where('subscriber_key', $order->subscriber_key)
            ->whereIn('product_id', $productIds)
            ->with('plan')
            ->get()
            ->keyBy('product_id');
    }
}
