<?php

namespace Tests\Feature;

use App\Enums\BillingType;
use App\Enums\OrderStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Services\Payments\GatewayManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Cashfree Subscriptions: recurring plans run through the mandate API while
 * one-time plans keep using the Orders API.
 */
class CashfreeSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'cf_test_secret';

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
    }

    private function gateway(): PaymentGateway
    {
        return PaymentGateway::factory()
            ->configured([
                'client_id' => 'cf_test_app',
                'client_secret' => self::SECRET,
            ])
            ->create(['driver' => 'cashfree', 'mode' => 'test']);
    }

    private function recurringOrder(PaymentGateway $gateway, array $attributes = []): Order
    {
        $product = Product::factory()->create(['is_active' => true, 'slug' => 'tongdon']);

        $plan = Plan::factory()->create([
            'product_id' => $product->getKey(),
            'code' => 'monthly',
            'name' => 'Monthly',
            'billing_type' => BillingType::Recurring,
            'interval_days' => 30,
            'price' => 29900,
            'currency' => 'INR',
        ]);

        return Order::factory()->create(array_merge([
            'product_id' => $product->getKey(),
            'plan_id' => $plan->getKey(),
            'payment_gateway_id' => $gateway->getKey(),
            'subscriber_key' => 'user-42',
            'name' => 'Test User',
            'email' => 'user@example.com',
            'phone' => '+91 98765 43210',
            'amount' => 29900,
            'currency' => 'INR',
            'status' => OrderStatus::Pending,
        ], $attributes));
    }

    private function sign(string $body): array
    {
        $timestamp = (string) now()->getTimestamp();

        return [
            'HTTP_X-Webhook-Signature' => base64_encode(hash_hmac('sha256', $timestamp.$body, self::SECRET, true)),
            'HTTP_X-Webhook-Timestamp' => $timestamp,
        ];
    }

    public function test_a_recurring_plan_creates_a_provider_plan_and_mandate(): void
    {
        $gateway = $this->gateway();
        $order = $this->recurringOrder($gateway);

        Http::fake([
            'sandbox.cashfree.com/pg/plans' => Http::response(['plan_id' => 'agape-tongdon-monthly'], 200),
            'sandbox.cashfree.com/pg/subscriptions' => Http::response([
                'cf_subscription_id' => '23639356',
                'subscription_id' => $order->reference,
                'subscription_session_id' => 'subs_session_1',
            ], 200),
        ]);

        $session = app(GatewayManager::class)->make($gateway)->createCheckout($order);

        $this->assertTrue($session->isInline());
        $this->assertSame('subs_session_1', $session->payload['subscription_session_id']);
        $this->assertSame($order->reference, $session->payload['subscription_id']);
        $this->assertSame('sandbox', $session->payload['mode']);

        $order->refresh();

        $this->assertSame('23639356', $order->gateway_subscription_id);
        $this->assertTrue($order->gateway_payload['recurring']);

        Http::assertSent(function (Request $request): bool {
            $body = $request->data();

            return $request->url() === 'https://sandbox.cashfree.com/pg/plans'
                && $request->method() === 'POST'
                && data_get($body, 'plan_id') === 'agape-tongdon-monthly'
                && data_get($body, 'plan_type') === 'PERIODIC'
                && data_get($body, 'plan_intervals') === 1
                && data_get($body, 'plan_interval_type') === 'MONTH'
                && data_get($body, 'plan_recurring_amount') === 299.0
                && data_get($body, 'plan_max_amount') === 299.0;
        });

        Http::assertSent(function (Request $request) use ($order): bool {
            $body = $request->data();

            return $request->url() === 'https://sandbox.cashfree.com/pg/subscriptions'
                && $request->method() === 'POST'
                && data_get($body, 'subscription_id') === $order->reference
                && data_get($body, 'plan_details.plan_id') === 'agape-tongdon-monthly'
                && data_get($body, 'customer_details.customer_phone') === '9876543210'
                && str_contains((string) data_get($body, 'subscription_meta.return_url'), $order->reference);
        });
    }

    public function test_an_already_existing_provider_plan_is_tolerated(): void
    {
        $gateway = $this->gateway();
        $order = $this->recurringOrder($gateway);

        Http::fake([
            'sandbox.cashfree.com/pg/plans' => Http::response(['code' => 'plan_already_exists'], 409),
            'sandbox.cashfree.com/pg/subscriptions' => Http::response([
                'subscription_session_id' => 'subs_session_2',
            ], 200),
        ]);

        $session = app(GatewayManager::class)->make($gateway)->createCheckout($order);

        $this->assertSame('subs_session_2', $session->payload['subscription_session_id']);
    }

    public function test_a_one_time_plan_still_uses_the_orders_api(): void
    {
        $gateway = $this->gateway();

        $product = Product::factory()->create(['is_active' => true]);
        $plan = Plan::factory()->create([
            'product_id' => $product->getKey(),
            'code' => 'yearly',
            'billing_type' => BillingType::OneTime,
            'price' => 299900,
        ]);

        $order = Order::factory()->create([
            'product_id' => $product->getKey(),
            'plan_id' => $plan->getKey(),
            'payment_gateway_id' => $gateway->getKey(),
            'amount' => 299900,
            'currency' => 'INR',
            'status' => OrderStatus::Pending,
        ]);

        Http::fake([
            'sandbox.cashfree.com/pg/orders' => Http::response([
                'cf_order_id' => 'CF1',
                'order_status' => 'ACTIVE',
                'payment_session_id' => 'session_ot_1',
            ], 200),
        ]);

        $session = app(GatewayManager::class)->make($gateway)->createCheckout($order);

        $this->assertSame('session_ot_1', $session->payload['payment_session_id']);
        $this->assertArrayNotHasKey('subscription_session_id', $session->payload);

        Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), '/subscriptions'));
    }

    public function test_subscription_activation_webhook_marks_the_order_paid(): void
    {
        $gateway = $this->gateway();
        $order = $this->recurringOrder($gateway);
        $order->recordGatewayResult([], ['recurring' => true, 'subscription_id' => $order->reference]);

        Http::fake([
            'sandbox.cashfree.com/pg/subscriptions/'.$order->reference => Http::response([
                'subscription_id' => $order->reference,
                'subscription_status' => 'ACTIVE',
            ], 200),
        ]);

        $body = json_encode([
            'type' => 'SUBSCRIPTION_STATUS_CHANGED',
            'data' => [
                'subscription_details' => [
                    'subscription_id' => $order->reference,
                    'subscription_status' => 'ACTIVE',
                ],
            ],
        ]);

        $this->call('POST', '/api/webhooks/cashfree', [], [], [], $this->sign($body), $body)->assertOk();

        $order->refresh();

        $this->assertSame(OrderStatus::Paid, $order->status);

        $subscription = $order->subscription()->sole();

        $this->assertSame(SubscriptionStatus::Active, $subscription->status);
        $this->assertTrue($subscription->isEntitled());
    }

    public function test_an_unauthorised_mandate_leaves_the_order_pending(): void
    {
        $gateway = $this->gateway();
        $order = $this->recurringOrder($gateway);
        $order->recordGatewayResult([], ['recurring' => true, 'subscription_id' => $order->reference]);

        Http::fake([
            'sandbox.cashfree.com/pg/subscriptions/'.$order->reference => Http::response([
                'subscription_status' => 'BANK_APPROVAL_PENDING',
            ], 200),
        ]);

        $body = json_encode([
            'type' => 'SUBSCRIPTION_STATUS_CHANGED',
            'data' => ['subscription_details' => ['subscription_id' => $order->reference, 'subscription_status' => 'BANK_APPROVAL_PENDING']],
        ]);

        $this->call('POST', '/api/webhooks/cashfree', [], [], [], $this->sign($body), $body)->assertOk();

        $this->assertSame(OrderStatus::Pending, $order->refresh()->status);
    }

    public function test_a_renewal_charge_extends_the_entitlement_only_once(): void
    {
        $gateway = $this->gateway();
        $order = $this->recurringOrder($gateway, [
            'status' => OrderStatus::Paid,
            'paid_at' => now(),
        ]);
        $order->recordGatewayResult([], ['recurring' => true, 'subscription_id' => $order->reference]);

        $subscription = Subscription::factory()->create([
            'product_id' => $order->product_id,
            'plan_id' => $order->plan_id,
            'last_order_id' => $order->getKey(),
            'subscriber_key' => $order->subscriber_key,
            'status' => SubscriptionStatus::Active,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'renews_at' => now()->addDays(30),
        ]);

        $originalEndsAt = $subscription->ends_at->copy();

        Http::fake([
            'sandbox.cashfree.com/pg/subscriptions/'.$order->reference => Http::response([
                'subscription_status' => 'ACTIVE',
            ], 200),
        ]);

        $body = json_encode([
            'type' => 'SUBSCRIPTION_PAYMENT_SUCCESS',
            'data' => [
                'subscription_details' => ['subscription_id' => $order->reference],
                'payment' => ['cf_payment_id' => 'P-RENEW-1'],
            ],
        ]);

        $this->call('POST', '/api/webhooks/cashfree', [], [], [], $this->sign($body), $body)->assertOk();

        $subscription->refresh();

        $this->assertTrue($subscription->ends_at->equalTo($originalEndsAt->copy()->addDays(30)));
        $this->assertSame('P-RENEW-1', $subscription->meta['last_renewed_payment_id']);

        // A retried delivery of the same charge must not grant another cycle.
        $this->call('POST', '/api/webhooks/cashfree', [], [], [], $this->sign($body), $body)->assertOk();

        $this->assertTrue(
            $subscription->refresh()->ends_at->equalTo($originalEndsAt->copy()->addDays(30))
        );
    }
}
