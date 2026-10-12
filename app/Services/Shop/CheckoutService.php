<?php

namespace App\Services\Shop;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\PaymentEvent;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ShopOrder;
use App\Models\ShopOrderItem;
use App\Services\Payments\CheckoutService as OrderCheckoutService;
use App\Services\Payments\CustomerKey;
use App\Services\Payments\DTOs\CheckoutSession;
use App\Services\Payments\Exceptions\GatewayException;
use App\Services\Payments\GatewayManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Turns a cart into one provider payment, then fans the confirmed cart out
 * into the per-product orders and subscriptions the entitlement system
 * expects — exactly once.
 */
class CheckoutService
{
    public function __construct(
        private readonly GatewayManager $gateways,
        private readonly OrderCheckoutService $orders,
    ) {}

    /**
     * Open a pending cart order and start its provider checkout.
     *
     * @param  Collection<int, array{key: string, product: Product, plan: Plan, price: int}>  $lines
     * @param  array<string, string|null>  $customer  name, email, phone, subscriber_key
     * @return array{0: ShopOrder, 1: CheckoutSession}
     *
     * @throws GatewayException
     */
    public function startCheckout(Collection $lines, array $customer, ?string $gatewayDriver = null): array
    {
        if ($lines->isEmpty()) {
            throw new GatewayException('Your cart is empty.');
        }

        $currency = $lines->first()['plan']->currency;

        if ($lines->contains(fn (array $line): bool => $line['plan']->currency !== $currency)) {
            throw new GatewayException('Every plan in the cart must share the same currency.');
        }

        $gateway = $this->gateways->resolveCheckoutGateway($gatewayDriver);

        $shopOrder = ShopOrder::create([
            'payment_gateway_id' => $gateway->getKey(),
            'subscriber_key' => CustomerKey::resolve($customer),
            'name' => $customer['name'] ?? null,
            'email' => $customer['email'] ?? null,
            'phone' => $customer['phone'] ?? null,
            'amount' => (int) $lines->sum('price'),
            'currency' => $currency,
            'status' => OrderStatus::Pending,
            'expires_at' => now()->addHour(),
        ]);

        $shopOrder->items()->createMany(
            $lines->map(fn (array $line): array => [
                'product_id' => $line['product']->getKey(),
                'plan_id' => $line['plan']->getKey(),
                'product_name' => $line['product']->name,
                'plan_name' => $line['plan']->name,
                'unit_price' => $line['price'],
                'total' => $line['price'],
                'currency' => $line['plan']->currency,
            ])->all()
        );

        $shopOrder->load('items');

        $session = $this->gateways->make($gateway)->createCheckout($shopOrder);

        return [$shopOrder->fresh(['items']), $session];
    }

    /**
     * Mark a pending cart order paid exactly once and grant every plan in it.
     *
     * Idempotent: the pending → paid claim decides the winner, so a browser
     * return racing a webhook can never fan the cart out twice.
     *
     * @param  array<string, mixed>  $payload
     */
    public function complete(ShopOrder $shopOrder, array $payload = [], string $direction = 'inbound'): bool
    {
        return DB::transaction(function () use ($shopOrder, $payload, $direction): bool {
            $event = PaymentEvent::create([
                'payment_gateway_id' => $shopOrder->payment_gateway_id,
                'shop_order_id' => $shopOrder->getKey(),
                'type' => (string) ($payload['event'] ?? 'payment.completed'),
                'direction' => $direction,
                'payload' => $payload,
                'signature' => isset($payload['signature']) ? (string) $payload['signature'] : null,
                'is_verified' => true,
                'is_processed' => false,
                'received_at' => now(),
            ]);

            $claimed = $shopOrder->claimForCompletion();

            $event->forceFill(['is_processed' => $claimed])->save();

            if (! $claimed) {
                return false;
            }

            $shopOrder->refresh();

            foreach ($shopOrder->items as $index => $item) {
                $this->fanOut($shopOrder, $item, $index, $direction);
            }

            return true;
        });
    }

    /**
     * Create the per-product order for one cart line and grant it through
     * the regular single-plan completion path.
     */
    private function fanOut(ShopOrder $shopOrder, ShopOrderItem $item, int $index, string $direction): Order
    {
        $order = Order::create([
            'product_id' => $item->product_id,
            'plan_id' => $item->plan_id,
            'payment_gateway_id' => $shopOrder->payment_gateway_id,
            'reference' => $shopOrder->reference.'-'.($index + 1),
            'subscriber_key' => $shopOrder->subscriber_key,
            'name' => $shopOrder->name,
            'email' => $shopOrder->email,
            'phone' => $shopOrder->phone,
            'amount' => $item->unit_price,
            'currency' => $item->currency,
            'status' => OrderStatus::Pending,
            'gateway_order_id' => $shopOrder->gateway_order_id,
            'gateway_payment_id' => $shopOrder->gateway_payment_id,
            'gateway_payload' => ['shop_order_reference' => $shopOrder->reference],
            'expires_at' => now(),
            'meta' => ['shop_order_id' => $shopOrder->getKey()],
        ]);

        $this->orders->complete($order, [
            'event' => 'shop.split',
            'driver' => $shopOrder->gateway?->driver,
            'reference' => $order->reference,
            'shop_reference' => $shopOrder->reference,
        ], $direction);

        return $order;
    }
}
