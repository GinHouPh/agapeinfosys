<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Services\Payments\CheckoutService;
use App\Services\Payments\Exceptions\GatewayException;
use App\Services\Payments\GatewayManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public checkout: plan selection, payment hand-off and order results.
 */
class CheckoutController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout) {}

    /**
     * Plan selection page for a digital product, e.g. /tongdon.
     */
    public function show(Request $request, Product $product): View
    {
        abort_unless($product->is_active, 404);

        $plans = $product->activePlans;

        $preselected = $request->query('plan');

        return view('checkout.show', [
            'product' => $product,
            'plans' => $plans,
            'preselected' => is_string($preselected) ? $preselected : null,
            'subscriberKey' => (string) $request->query('subscriber', ''),
            'email' => (string) $request->query('email', ''),
            'name' => (string) $request->query('name', ''),
        ]);
    }

    /**
     * Create the order and either open the inline checkout or redirect to
     * the provider's hosted page.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $validated = $request->validate([
            'plan' => ['required', 'string', 'max:120'],
            'name' => ['nullable', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subscriber_key' => ['nullable', 'string', 'max:190'],
            'gateway' => ['nullable', 'string', 'max:40'],
        ]);

        try {
            [$order, $session] = $this->checkout->startCheckout(
                $product->slug,
                $validated['plan'],
                [
                    'name' => $validated['name'] ?? null,
                    'email' => $validated['email'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                    'subscriber_key' => $validated['subscriber_key'] ?? null,
                    'return_url' => $request->input('return_url'),
                ],
                $validated['gateway'] ?? null,
            );
        } catch (GatewayException $exception) {
            return back()
                ->withInput()
                ->withErrors(['plan' => $exception->getMessage()]);
        }

        if ($session->isInline()) {
            return redirect()
                ->route('checkout.pay', [$product, $order])
                ->with('checkout_session', $session->payload);
        }

        if ($session->hasForm()) {
            return redirect()
                ->route('checkout.pay', [$product, $order])
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
    public function pay(Product $product, Order $order): View
    {
        $order->loadMissing(['product', 'plan', 'gateway']);

        abort_unless($order->product_id === $product->getKey(), 404);
        abort_unless($order->status === OrderStatus::Pending, 404);

        $driver = $order->gateway;

        $payload = session('checkout_session');
        $form = session('checkout_form');

        if ($payload === null && $driver !== null && $driver->driver === 'sandbox') {
            $sandbox = app(GatewayManager::class)->make($driver);

            $payload = $sandbox->createCheckout($order)->payload;
        }

        return view('checkout.pay', [
            'order' => $order,
            'payload' => is_array($payload) ? $payload : [],
            'form' => is_array($form) ? $form : null,
            'script' => $driver?->checkoutScript(),
        ]);
    }

    /**
     * Result page; also receives hosted-gateway return URLs (GET or POST).
     *
     * A pending order returned from a hosted provider is verified, confirmed
     * against the provider API and completed here.
     */
    public function result(Request $request, Product $product, Order $order): View|RedirectResponse
    {
        abort_unless($order->product_id === $product->getKey(), 404);

        if ($order->status === OrderStatus::Pending) {
            $this->tryCompleteFromReturn($request, $order);

            $order->refresh();

            if ($order->hasExpired()) {
                $order->forceFill(['status' => OrderStatus::Expired])->save();
            }
        }

        return view('checkout.result', [
            'order' => $order->load(['product', 'plan']),
            'product' => $product,
        ]);
    }

    /**
     * Best-effort completion when a hosted gateway sends the customer back.
     *
     * Drivers that can verify locally (Razorpay HMAC, PayU hash, PayPal
     * capture, sandbox token) do so in verifyCallback; hosted-redirect
     * drivers additionally confirm against the provider status API. Any
     * failure simply leaves the order pending — the provider webhook is the
     * fallback path.
     */
    private function tryCompleteFromReturn(Request $request, Order $order): void
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
}
