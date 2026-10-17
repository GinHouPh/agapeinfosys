<?php

namespace App\Services\Payments;

use App\Enums\OrderStatus;
use App\Enums\SubscriptionStatus;
use App\Jobs\SendEntitlementWebhook;
use App\Mail\OrderReceipt;
use App\Models\Order;
use App\Models\PaymentEvent;
use App\Models\Product;
use App\Models\Subscription;
use App\Notifications\NewOrderNotification;
use App\Services\Payments\Exceptions\GatewayException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Creates pending checkout orders and completes them exactly once.
 */
class CheckoutService
{
    public function __construct(private readonly GatewayManager $gateways) {}

    /**
     * Open a pending order for the plan and start its provider checkout.
     *
     * @param  array<string, string|null>  $customer  name, email, phone, subscriber_key, return_url
     *
     * @throws GatewayException
     */
    public function startCheckout(
        string $productSlug,
        string $planCode,
        array $customer,
        ?string $gatewayDriver = null,
    ): array {
        $order = $this->createPendingOrder($productSlug, $planCode, $customer, $gatewayDriver);

        $driver = $this->gateways->make($order->gateway);

        $session = $driver->createCheckout($order);

        return [$order->fresh(), $session];
    }

    /**
     * Create the pending order row without talking to a provider.
     *
     * @param  array<string, string|null>  $customer
     */
    public function createPendingOrder(
        string $productSlug,
        string $planCode,
        array $customer,
        ?string $gatewayDriver = null,
    ): Order {
        $product = Product::query()
            ->active()
            ->where('slug', $productSlug)
            ->firstOrFail();

        $plan = $product->plans()
            ->active()
            ->where('code', $planCode)
            ->firstOrFail();

        $gateway = $this->gateways->resolveCheckoutGateway($gatewayDriver);

        $subscriberKey = CustomerKey::resolve($customer);

        return Order::create([
            'product_id' => $product->getKey(),
            'plan_id' => $plan->getKey(),
            'payment_gateway_id' => $gateway->getKey(),
            'subscriber_key' => $subscriberKey,
            'name' => $customer['name'] ?? null,
            'email' => $customer['email'] ?? null,
            'phone' => $customer['phone'] ?? null,
            'amount' => $plan->price,
            'currency' => $plan->currency,
            'status' => OrderStatus::Pending,
            'expires_at' => now()->addHour(),
            'meta' => array_filter([
                'return_url' => $customer['return_url'] ?? null,
            ]),
        ]);
    }

    /**
     * Mark a pending order paid exactly once and grant the entitlement.
     *
     * Idempotent: concurrent webhooks and browser callbacks race on the
     * pending → paid transition and only one wins.
     *
     * @param  array<string, mixed>  $payload
     */
    public function complete(Order $order, array $payload = [], string $direction = 'inbound', bool $notifyCustomer = true): bool
    {
        return DB::transaction(function () use ($order, $payload, $direction, $notifyCustomer): bool {
            $event = PaymentEvent::create([
                'payment_gateway_id' => $order->payment_gateway_id,
                'order_id' => $order->getKey(),
                'type' => (string) ($payload['event'] ?? 'payment.completed'),
                'direction' => $direction,
                'payload' => $payload,
                'signature' => isset($payload['signature']) ? (string) $payload['signature'] : null,
                'is_verified' => true,
                'is_processed' => false,
                'received_at' => now(),
            ]);

            $claimed = $order->claimForCompletion();

            $event->forceFill(['is_processed' => $claimed])->save();

            if (! $claimed) {
                return false;
            }

            $order->refresh();

            $this->grantEntitlement($order);

            SendEntitlementWebhook::dispatch($order->getKey())->afterCommit();

            // A cart fan-out passes $notifyCustomer = false so the buyer gets
            // one receipt for the whole cart, not one per line.
            if ($notifyCustomer) {
                $this->sendReceipts($order);
            }

            return true;
        });
    }

    /**
     * Email the buyer their receipt and alert the admins, after commit.
     */
    private function sendReceipts(Order $order): void
    {
        if (is_string($order->email) && $order->email !== '') {
            Mail::to($order->email)->send((new OrderReceipt($order))->afterCommit());
        }

        NewOrderNotification::sendToAdmins($order);
    }

    /**
     * Extend or create the subscriber's entitlement for this order's plan.
     */
    private function grantEntitlement(Order $order): Subscription
    {
        $plan = $order->plan()->lockForUpdate()->firstOrFail();
        $entitlementDays = $plan->entitlementDays();

        return Subscription::updateOrCreate(
            [
                'product_id' => $order->product_id,
                'subscriber_key' => $order->subscriber_key,
            ],
            [
                'plan_id' => $plan->getKey(),
                'last_order_id' => $order->getKey(),
                'name' => $order->name,
                'email' => $order->email,
                'status' => SubscriptionStatus::Active,
                'starts_at' => $order->paid_at,
                'ends_at' => $entitlementDays === null
                    ? null
                    : $order->paid_at->copy()->addDays($entitlementDays),
                'renews_at' => $plan->isRecurring() && $entitlementDays !== null
                    ? $order->paid_at->copy()->addDays($entitlementDays)
                    : null,
                'cancelled_at' => null,
            ]
        );
    }
}
