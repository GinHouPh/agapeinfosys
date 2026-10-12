<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\Plan;
use App\Models\Product;
use App\Services\Payments\GatewayManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
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

    private function productWithPlan(): array
    {
        $product = Product::factory()->create(['is_active' => true]);

        $plan = Plan::factory()->create([
            'product_id' => $product->getKey(),
            'code' => 'pro',
            'is_active' => true,
            'billing_type' => 'recurring',
            'interval_days' => 30,
            'validity_days' => null,
        ]);

        return [$product, $plan];
    }

    public function test_checkout_page_lists_active_plans(): void
    {
        [$product, $plan] = $this->productWithPlan();
        Plan::factory()->inactive()->create(['product_id' => $product->getKey()]);

        $response = $this->get('/'.$product->slug);

        $response->assertOk()->assertSee($plan->name)->assertDontSee('Hidden Plan', false);
    }

    public function test_unknown_product_returns_404(): void
    {
        Product::factory()->create(['is_active' => true]);

        $this->get('/not-a-product')->assertNotFound();
    }

    public function test_inactive_product_returns_404(): void
    {
        $product = Product::factory()->inactive()->create();

        $this->get('/'.$product->slug)->assertNotFound();
    }

    public function test_checkout_creates_pending_order_and_opens_sandbox_payment(): void
    {
        $this->sandboxGateway();
        [$product] = $this->productWithPlan();

        $response = $this->post('/'.$product->slug, [
            'plan' => 'pro',
            'subscriber_key' => 'user-42',
            'name' => 'Test User',
            'email' => 'user@example.com',
        ]);

        $order = Order::sole();

        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame('user-42', $order->subscriber_key);

        $response->assertRedirect(route('checkout.pay', [$product, $order]));
    }

    public function test_sandbox_payment_completes_order_and_activates_subscription(): void
    {
        $this->sandboxGateway();
        [$product, $plan] = $this->productWithPlan();

        $order = Order::factory()->create([
            'product_id' => $product->getKey(),
            'plan_id' => $plan->getKey(),
            'payment_gateway_id' => PaymentGateway::query()->where('driver', 'sandbox')->sole()->getKey(),
            'subscriber_key' => 'user-42',
            'status' => OrderStatus::Pending,
        ]);

        $driver = app(GatewayManager::class)
            ->make(PaymentGateway::query()->where('driver', 'sandbox')->sole());

        $token = $driver->tokenFor($order);

        $this->post(route('checkout.result', [$product, $order]), [
            '_token' => csrf_token(),
            'token' => $token,
            'outcome' => 'success',
        ])->assertOk();

        $order->refresh();

        $this->assertSame(OrderStatus::Paid, $order->status);

        $subscription = $order->subscription()->sole();

        $this->assertSame(SubscriptionStatus::Active, $subscription->status);
        $this->assertTrue($subscription->isEntitled());
        $this->assertSame($plan->getKey(), $subscription->plan_id);
        $this->assertTrue($subscription->renews_at->isFuture());
    }

    public function test_sandbox_failure_does_not_complete_the_order(): void
    {
        $this->sandboxGateway();
        [$product, $plan] = $this->productWithPlan();

        $order = Order::factory()->create([
            'product_id' => $product->getKey(),
            'plan_id' => $plan->getKey(),
            'payment_gateway_id' => PaymentGateway::query()->where('driver', 'sandbox')->sole()->getKey(),
            'subscriber_key' => 'user-42',
            'status' => OrderStatus::Pending,
        ]);

        $driver = app(GatewayManager::class)
            ->make(PaymentGateway::query()->where('driver', 'sandbox')->sole());

        $this->post(route('checkout.result', [$product, $order]), [
            '_token' => csrf_token(),
            'token' => $driver->tokenFor($order),
            'outcome' => 'failure',
        ])->assertOk();

        $this->assertSame(OrderStatus::Pending, $order->refresh()->status);
    }

    public function test_tampered_sandbox_token_never_completes_an_order(): void
    {
        $this->sandboxGateway();
        [$product, $plan] = $this->productWithPlan();

        $order = Order::factory()->create([
            'product_id' => $product->getKey(),
            'plan_id' => $plan->getKey(),
            'payment_gateway_id' => PaymentGateway::query()->where('driver', 'sandbox')->sole()->getKey(),
            'subscriber_key' => 'user-42',
        ]);

        $this->post(route('checkout.result', [$product, $order]), [
            '_token' => csrf_token(),
            'token' => 'forged-token',
            'outcome' => 'success',
        ])->assertOk();

        $this->assertSame(OrderStatus::Pending, $order->refresh()->status);
    }

    public function test_pay_page_opens_the_sandbox_checkout(): void
    {
        $this->sandboxGateway();
        [$product, $plan] = $this->productWithPlan();

        $order = Order::factory()->create([
            'product_id' => $product->getKey(),
            'plan_id' => $plan->getKey(),
            'payment_gateway_id' => PaymentGateway::query()->where('driver', 'sandbox')->sole()->getKey(),
            'subscriber_key' => 'user-42',
            'status' => OrderStatus::Pending,
        ]);

        $response = $this->get(route('checkout.pay', [$product, $order]));

        $response->assertOk()
            ->assertSee('Simulate successful payment', false)
            ->assertSee($order->reference);
    }

    public function test_pay_page_hides_orders_from_another_product(): void
    {
        $this->sandboxGateway();
        [$product] = $this->productWithPlan();

        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        $this->get(route('checkout.pay', [$product, $order]))->assertNotFound();
    }

    public function test_pay_page_hides_orders_that_are_already_paid(): void
    {
        $this->sandboxGateway();
        [$product, $plan] = $this->productWithPlan();

        $order = Order::factory()->paid()->create([
            'product_id' => $product->getKey(),
            'plan_id' => $plan->getKey(),
            'payment_gateway_id' => PaymentGateway::query()->where('driver', 'sandbox')->sole()->getKey(),
        ]);

        $this->get(route('checkout.pay', [$product, $order]))->assertNotFound();
    }

    public function test_result_page_shows_paid_state(): void
    {
        $order = Order::factory()->paid()->create();
        $order->load('product');

        $this->get(route('checkout.result', [$order->product, $order]))
            ->assertOk()
            ->assertSee('Payment received');
    }

    public function test_checkout_requires_a_plan(): void
    {
        $this->sandboxGateway();
        [$product] = $this->productWithPlan();

        $this->from('/'.$product->slug)
            ->post('/'.$product->slug, ['subscriber_key' => 'user-42'])
            ->assertRedirect('/'.$product->slug)
            ->assertSessionHasErrors('plan');
    }

    public function test_expired_pending_order_is_marked_expired_on_view(): void
    {
        $this->sandboxGateway();
        [$product, $plan] = $this->productWithPlan();

        $order = Order::factory()->create([
            'product_id' => $product->getKey(),
            'plan_id' => $plan->getKey(),
            'payment_gateway_id' => PaymentGateway::query()->where('driver', 'sandbox')->sole()->getKey(),
            'status' => OrderStatus::Pending,
            'expires_at' => now()->subMinutes(5),
        ]);

        $this->get(route('checkout.result', [$product, $order]))->assertOk();

        $this->assertSame(OrderStatus::Expired, $order->refresh()->status);
    }
}
