<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\SubscriptionStatus;
use App\Jobs\SendEntitlementWebhook;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ShopOrder;
use App\Models\Subscription;
use App\Services\Payments\GatewayManager;
use App\Services\Shop\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ShopCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Outbound product webhooks must never hit the network in tests.
        Queue::fake();
    }

    private function sandboxGateway(): PaymentGateway
    {
        return PaymentGateway::factory()->sandbox()->create();
    }

    /**
     * @return array{0: Product, 1: Plan}
     */
    private function productWithPlan(string $code = 'pro', int $price = 49900, array $overrides = []): array
    {
        $product = Product::factory()->create(['is_active' => true]);

        $plan = Plan::factory()->create(array_merge([
            'product_id' => $product->getKey(),
            'code' => $code,
            'name' => ucfirst($code).' plan',
            'is_active' => true,
            'billing_type' => 'recurring',
            'interval_days' => 30,
            'validity_days' => null,
            'price' => $price,
            'currency' => 'INR',
        ], $overrides));

        return [$product, $plan];
    }

    /**
     * @param  array<int, array{0: Product, 1: Plan}>  $pairs
     * @return array<string, array{product_id: int, plan_id: int}>
     */
    private function cartItems(array $pairs): array
    {
        $items = [];

        foreach ($pairs as [$product, $plan]) {
            $items[$product->getKey().':'.$plan->getKey()] = [
                'product_id' => $product->getKey(),
                'plan_id' => $plan->getKey(),
            ];
        }

        return ['shop.cart' => $items];
    }

    /**
     * @param  array<int, array{0: Product, 1: Plan}>  $pairs
     */
    private function pendingShopOrder(array $pairs, array $overrides = []): ShopOrder
    {
        $order = ShopOrder::factory()->create(array_merge([
            'payment_gateway_id' => PaymentGateway::query()->where('driver', 'sandbox')->sole()->getKey(),
            'subscriber_key' => 'user-42',
            'amount' => array_sum(array_map(fn (array $pair): int => $pair[1]->price, $pairs)),
            'currency' => 'INR',
            'status' => OrderStatus::Pending,
        ], $overrides));

        foreach ($pairs as [$product, $plan]) {
            $order->items()->create([
                'product_id' => $product->getKey(),
                'plan_id' => $plan->getKey(),
                'product_name' => $product->name,
                'plan_name' => $plan->name,
                'unit_price' => $plan->price,
                'total' => $plan->price,
                'currency' => $plan->currency,
            ]);
        }

        return $order->fresh('items');
    }

    public function test_checkout_page_redirects_to_cart_when_cart_is_empty(): void
    {
        $this->get('/shop/checkout')->assertRedirect(route('shop.cart.index'));
    }

    public function test_checkout_creates_cart_order_and_opens_sandbox_payment(): void
    {
        $this->sandboxGateway();
        [$productA, $planA] = $this->productWithPlan('pro', 49900);
        [$productB, $planB] = $this->productWithPlan('basic', 19900);

        $response = $this->withSession($this->cartItems([[$productA, $planA], [$productB, $planB]]))
            ->post('/shop/checkout', [
                'subscriber_key' => 'user-42',
                'name' => 'Test User',
                'email' => 'user@example.com',
            ]);

        $order = ShopOrder::sole();

        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(69800, $order->amount);
        $this->assertSame('user-42', $order->subscriber_key);
        $this->assertCount(2, $order->items);

        $response->assertRedirect(route('shop.pay', $order));
        $this->assertNull(session('shop.cart'), 'The cart must be cleared once the order is opened.');
    }

    public function test_checkout_requires_a_subscriber_key_or_email(): void
    {
        $this->sandboxGateway();
        [$product, $plan] = $this->productWithPlan();

        $this->withSession($this->cartItems([[$product, $plan]]))
            ->post('/shop/checkout', ['name' => 'No Account'])
            ->assertSessionHasErrors('subscriber_key');

        $this->assertSame(0, ShopOrder::count());
    }

    public function test_mixed_currency_carts_are_rejected(): void
    {
        $this->sandboxGateway();
        [$productA, $planA] = $this->productWithPlan('pro', 49900);
        [$productB, $planB] = $this->productWithPlan('basic', 1990, ['currency' => 'USD']);

        $this->withSession($this->cartItems([[$productA, $planA], [$productB, $planB]]))
            ->post('/shop/checkout', ['subscriber_key' => 'user-42'])
            ->assertSessionHasErrors([
                'subscriber_key' => 'Every plan in the cart must share the same currency.',
            ]);

        $this->assertSame(0, ShopOrder::count());
    }

    public function test_sandbox_payment_fans_the_cart_out_into_orders_and_subscriptions(): void
    {
        $this->sandboxGateway();
        [$productA, $planA] = $this->productWithPlan('pro', 49900);
        [$productB, $planB] = $this->productWithPlan('basic', 19900);

        $order = $this->pendingShopOrder([[$productA, $planA], [$productB, $planB]]);

        $driver = app(GatewayManager::class)
            ->make(PaymentGateway::query()->where('driver', 'sandbox')->sole());

        $this->post(route('shop.result', $order), [
            '_token' => csrf_token(),
            'token' => $driver->tokenFor($order),
            'outcome' => 'success',
        ])->assertOk();

        $order->refresh();

        $this->assertSame(OrderStatus::Paid, $order->status);

        $fanned = Order::query()->orderBy('id')->get();

        $this->assertCount(2, $fanned);
        $this->assertSame([$order->reference.'-1', $order->reference.'-2'], $fanned->pluck('reference')->all());
        $this->assertSame([49900, 19900], $fanned->pluck('amount')->all());
        $this->assertTrue($fanned->every(fn (Order $child): bool => $child->status === OrderStatus::Paid));
        $this->assertTrue($fanned->every(fn (Order $child): bool => $child->subscriber_key === 'user-42'));

        $subscriptions = Subscription::query()->get();

        $this->assertCount(2, $subscriptions);
        $this->assertTrue($subscriptions->every(fn (Subscription $subscription): bool => $subscription->status === SubscriptionStatus::Active));
        $this->assertTrue($subscriptions->every(fn (Subscription $subscription): bool => $subscription->subscriber_key === 'user-42'));

        Queue::assertPushed(SendEntitlementWebhook::class, 2);
    }

    public function test_completing_a_cart_order_is_idempotent(): void
    {
        $this->sandboxGateway();
        [$productA, $planA] = $this->productWithPlan('pro', 49900);
        [$productB, $planB] = $this->productWithPlan('basic', 19900);

        $order = $this->pendingShopOrder([[$productA, $planA], [$productB, $planB]]);

        $service = app(CheckoutService::class);

        $this->assertTrue($service->complete($order, ['event' => 'test.first']));
        $this->assertFalse($service->complete($order, ['event' => 'test.second']));

        $this->assertSame(2, Order::count());
        $this->assertSame(OrderStatus::Paid, $order->refresh()->status);
    }

    public function test_webhook_completes_a_cart_order_after_confirmation(): void
    {
        $this->sandboxGateway();
        [$product, $plan] = $this->productWithPlan();
        $order = $this->pendingShopOrder([[$product, $plan]]);

        $this->postJson(route('checkout.webhook', 'sandbox'), [
            'reference' => $order->reference,
            'event' => 'sandbox.success',
        ])->assertOk()->assertSee('ok');

        $this->assertSame(OrderStatus::Paid, $order->refresh()->status);
        $this->assertSame(1, Order::count());
        Queue::assertPushed(SendEntitlementWebhook::class, 1);
    }

    public function test_pay_page_renders_the_sandbox_simulator(): void
    {
        $this->sandboxGateway();
        [$product, $plan] = $this->productWithPlan();
        $order = $this->pendingShopOrder([[$product, $plan]]);

        $this->get(route('shop.pay', $order))
            ->assertOk()
            ->assertSee('Simulate successful payment', false)
            ->assertSee($order->reference);
    }

    public function test_cashfree_checkout_renders_the_js_sdk_session(): void
    {
        $gateway = PaymentGateway::factory()
            ->configured(['client_id' => 'cf_test_app', 'client_secret' => 'cf_test_secret'])
            ->create(['driver' => 'cashfree', 'mode' => 'test', 'is_default' => true]);

        [$product, $plan] = $this->productWithPlan();

        Http::fake([
            'sandbox.cashfree.com/pg/orders' => Http::response([
                'cf_order_id' => 'CF999',
                'payment_session_id' => 'session_abc123',
            ], 200),
        ]);

        $this->withSession($this->cartItems([[$product, $plan]]))
            ->post('/shop/checkout', ['subscriber_key' => 'user-42'])
            ->assertRedirect(route('shop.pay', ShopOrder::sole()));

        $order = ShopOrder::sole();

        $this->assertSame($gateway->getKey(), $order->payment_gateway_id);

        $this->get(route('shop.pay', $order))
            ->assertOk()
            ->assertSee('session_abc123', false)
            ->assertSee('sdk.cashfree.com/js/v3/cashfree.js', false);

        // A reload must not strand the customer on an "expired" page.
        $this->get(route('shop.pay', $order))
            ->assertOk()
            ->assertSee('session_abc123', false)
            ->assertDontSee('This checkout session has expired');
    }

    public function test_expired_cart_order_shows_the_expired_state(): void
    {
        $this->sandboxGateway();
        [$product, $plan] = $this->productWithPlan();
        $order = $this->pendingShopOrder([[$product, $plan]], ['expires_at' => now()->subMinute()]);

        $this->get(route('shop.result', $order))
            ->assertOk()
            ->assertSee('Checkout expired');

        $this->assertSame(OrderStatus::Expired, $order->refresh()->status);
    }

    public function test_failed_sandbox_payment_keeps_the_cart_order_pending(): void
    {
        $this->sandboxGateway();
        [$product, $plan] = $this->productWithPlan();
        $order = $this->pendingShopOrder([[$product, $plan]]);

        $driver = app(GatewayManager::class)
            ->make(PaymentGateway::query()->where('driver', 'sandbox')->sole());

        $this->post(route('shop.result', $order), [
            '_token' => csrf_token(),
            'token' => $driver->tokenFor($order),
            'outcome' => 'failure',
        ])->assertOk();

        $this->assertSame(OrderStatus::Pending, $order->refresh()->status);
        $this->assertSame(0, Order::count());
    }
}
