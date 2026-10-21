<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\Plan;
use App\Models\Product;
use App\Services\Payments\Exceptions\GatewayException;
use App\Services\Payments\Exceptions\GatewayNotConfiguredException;
use App\Services\Payments\GatewayManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Cashfree PG integration, verified against the documented API:
 * create order -> payment_session_id for the JS SDK, status API as the
 * trust root, and base64(HMAC-SHA256(secret, timestamp.rawBody)) webhooks.
 */
class CashfreeDriverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Outbound product webhooks must never hit the network in tests.
        Queue::fake();
    }

    private function cashfreeGateway(array $overrides = []): PaymentGateway
    {
        return PaymentGateway::factory()
            ->configured([
                'client_id' => 'cf_test_app',
                'client_secret' => 'cf_test_secret',
            ])
            ->create(array_merge(['driver' => 'cashfree', 'mode' => 'test'], $overrides));
    }

    /**
     * @return array{0: Product, 1: Plan, 2: Order}
     */
    private function pendingOrder(PaymentGateway $gateway): array
    {
        $product = Product::factory()->create(['is_active' => true]);
        $plan = Plan::factory()->create(['product_id' => $product->getKey(), 'code' => 'pro']);

        $order = Order::factory()->create([
            'product_id' => $product->getKey(),
            'plan_id' => $plan->getKey(),
            'payment_gateway_id' => $gateway->getKey(),
            'subscriber_key' => 'user-42',
            'name' => 'Test User',
            'email' => 'user@example.com',
            'phone' => '+91 98765 43210',
            'amount' => 49900,
            'currency' => 'INR',
            'status' => OrderStatus::Pending,
        ]);

        return [$product, $plan, $order];
    }

    public function test_checkout_opens_cashfree_hosted_checkout_via_js_sdk(): void
    {
        $gateway = $this->cashfreeGateway();
        [, , $order] = $this->pendingOrder($gateway);

        Http::fake([
            'sandbox.cashfree.com/pg/orders' => Http::response([
                'cf_order_id' => 'CF123456',
                'order_id' => $order->reference,
                'order_status' => 'ACTIVE',
                'payment_session_id' => 'session_abc123',
            ], 200),
        ]);

        $session = app(GatewayManager::class)->make($gateway)->createCheckout($order);

        $this->assertTrue($session->isInline());
        $this->assertSame('cashfree', $session->driver);
        $this->assertSame('session_abc123', $session->payload['payment_session_id']);
        $this->assertSame('sandbox', $session->payload['mode']);

        $this->assertSame('CF123456', $order->refresh()->gateway_order_id);
        $this->assertSame('session_abc123', $order->gateway_payload['payment_session_id'] ?? null);

        Http::assertSent(function (Request $request) use ($order): bool {
            $body = $request->data();

            return $request->url() === 'https://sandbox.cashfree.com/pg/orders'
                && $request->method() === 'POST'
                && $request->header('x-client-id')[0] === 'cf_test_app'
                && $request->header('x-client-secret')[0] === 'cf_test_secret'
                && $request->header('x-api-version')[0] === '2026-01-01'
                && $request->header('x-idempotency-key')[0] !== null
                && data_get($body, 'order_id') === $order->reference
                && data_get($body, 'order_amount') === 499.0
                && data_get($body, 'order_currency') === 'INR'
                // Cashfree expects one customer object, not a list.
                && data_get($body, 'customer_details.customer_id') === 'USER42'
                && data_get($body, 'customer_details.customer_name') === 'Test User'
                && data_get($body, 'customer_details.customer_email') === 'user@example.com'
                // Phone numbers are normalised to exactly ten digits.
                && data_get($body, 'customer_details.customer_phone') === '9876543210'
                && str_contains((string) data_get($body, 'order_meta.return_url'), $order->reference)
                && str_ends_with((string) data_get($body, 'order_meta.notify_url'), '/api/webhooks/cashfree');
        });
    }

    public function test_pay_page_rebuilds_the_cashfree_session_after_the_flash_is_gone(): void
    {
        $gateway = $this->cashfreeGateway();
        [$product, , $order] = $this->pendingOrder($gateway);

        Http::fake([
            'sandbox.cashfree.com/pg/orders' => Http::response([
                'cf_order_id' => 'CF123456',
                'order_id' => $order->reference,
                'order_status' => 'ACTIVE',
                'payment_session_id' => 'session_abc123',
            ], 200),
        ]);

        app(GatewayManager::class)->make($gateway)->createCheckout($order);

        // Do not flash anything: the payload must come from the order itself.
        $this->get(route('checkout.pay', [$product, $order]))
            ->assertOk()
            ->assertSee('session_abc123', false)
            ->assertSee('"mode":"sandbox"', false)
            ->assertDontSee('This checkout session has expired');
    }

    public function test_live_mode_uses_the_production_api_and_sdk_mode(): void
    {
        $gateway = $this->cashfreeGateway([
            'mode' => 'live',
            'test_config' => null,
            'live_config' => ['client_id' => 'cf_live_app', 'client_secret' => 'cf_live_secret'],
        ]);
        [, , $order] = $this->pendingOrder($gateway);

        Http::fake([
            'api.cashfree.com/pg/orders' => Http::response([
                'cf_order_id' => 'CF789',
                'order_status' => 'ACTIVE',
                'payment_session_id' => 'session_live_1',
            ], 200),
        ]);

        $session = app(GatewayManager::class)->make($gateway)->createCheckout($order);

        $this->assertSame('production', $session->payload['mode']);

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.cashfree.com/pg/orders');
    }

    public function test_create_checkout_requires_credentials(): void
    {
        $gateway = PaymentGateway::factory()->create(['driver' => 'cashfree', 'test_config' => null]);
        [, , $order] = $this->pendingOrder($gateway);

        $this->expectException(GatewayNotConfiguredException::class);

        app(GatewayManager::class)->make($gateway)->createCheckout($order);
    }

    public function test_create_checkout_rejects_a_response_without_session_id(): void
    {
        $gateway = $this->cashfreeGateway();
        [, , $order] = $this->pendingOrder($gateway);

        Http::fake(['sandbox.cashfree.com/pg/orders' => Http::response(['order_status' => 'ACTIVE'], 200)]);

        $this->expectException(GatewayException::class);

        app(GatewayManager::class)->make($gateway)->createCheckout($order);
    }

    public function test_webhook_signature_is_base64_hmac_over_timestamp_and_raw_body(): void
    {
        $gateway = $this->cashfreeGateway();
        $driver = app(GatewayManager::class)->make($gateway);

        $body = json_encode(['type' => 'PAYMENT_SUCCESS_WEBHOOK']);
        $timestamp = '1617695238078';
        $signature = base64_encode(hash_hmac('sha256', $timestamp.$body, 'cf_test_secret', true));

        $valid = HttpRequest::create('/api/webhooks/cashfree', 'POST', [], [], [], [
            'HTTP_X-Webhook-Signature' => $signature,
            'HTTP_X-Webhook-Timestamp' => $timestamp,
        ], $body);

        $this->assertTrue($driver->verifyWebhookSignature($valid));

        $tampered = HttpRequest::create('/api/webhooks/cashfree', 'POST', [], [], [], [
            'HTTP_X-Webhook-Signature' => base64_encode(random_bytes(32)),
            'HTTP_X-Webhook-Timestamp' => $timestamp,
        ], $body);

        $this->assertFalse($driver->verifyWebhookSignature($tampered));

        $missing = HttpRequest::create('/api/webhooks/cashfree', 'POST', [], [], [], [], $body);

        $this->assertFalse($driver->verifyWebhookSignature($missing));
    }

    public function test_confirm_reports_paid_status_from_cashfree(): void
    {
        $gateway = $this->cashfreeGateway();
        [, , $order] = $this->pendingOrder($gateway);

        Http::fake([
            'sandbox.cashfree.com/pg/orders/'.$order->reference => Http::response(['order_status' => 'PAID'], 200),
        ]);

        $this->assertTrue(app(GatewayManager::class)->make($gateway)->confirm($order));
    }

    public function test_confirm_reports_unpaid_status_from_cashfree(): void
    {
        $gateway = $this->cashfreeGateway();
        [, , $order] = $this->pendingOrder($gateway);

        Http::fake([
            'sandbox.cashfree.com/pg/orders/'.$order->reference => Http::response(['order_status' => 'ACTIVE'], 200),
        ]);

        $this->assertFalse(app(GatewayManager::class)->make($gateway)->confirm($order));
    }

    public function test_confirm_returns_null_when_cashfree_cannot_be_reached(): void
    {
        $gateway = $this->cashfreeGateway();
        [, , $order] = $this->pendingOrder($gateway);

        Http::fake([
            'sandbox.cashfree.com/pg/orders/'.$order->reference => Http::response('boom', 500),
        ]);

        $this->assertNull(app(GatewayManager::class)->make($gateway)->confirm($order));
    }

    public function test_returning_customer_is_completed_only_when_cashfree_confirms(): void
    {
        $gateway = $this->cashfreeGateway();
        [$product, $plan, $order] = $this->pendingOrder($gateway);

        Http::fake([
            'sandbox.cashfree.com/pg/orders/'.$order->reference => Http::response(['order_status' => 'PAID'], 200),
        ]);

        $this->get(route('checkout.result', [$product, $order]).'?order_id='.$order->reference)
            ->assertOk()
            ->assertSee('Payment received');

        $order->refresh();

        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertSame('status_api', $order->gateway_payload['verified_via'] ?? null);

        $subscription = $order->subscription()->sole();

        $this->assertSame(SubscriptionStatus::Active, $subscription->status);
        $this->assertTrue($subscription->isEntitled());
        $this->assertSame($plan->getKey(), $subscription->plan_id);
    }

    public function test_returning_customer_with_unpaid_status_stays_pending(): void
    {
        $gateway = $this->cashfreeGateway();
        [$product, , $order] = $this->pendingOrder($gateway);

        Http::fake([
            'sandbox.cashfree.com/pg/orders/'.$order->reference => Http::response(['order_status' => 'ACTIVE'], 200),
        ]);

        $this->get(route('checkout.result', [$product, $order]).'?order_id='.$order->reference)->assertOk();

        $this->assertSame(OrderStatus::Pending, $order->refresh()->status);
    }

    public function test_returning_customer_with_mismatched_order_id_stays_pending(): void
    {
        $gateway = $this->cashfreeGateway();
        [$product, , $order] = $this->pendingOrder($gateway);

        Http::fake([
            'sandbox.cashfree.com/pg/orders/'.$order->reference => Http::response(['order_status' => 'PAID'], 200),
        ]);

        $this->get(route('checkout.result', [$product, $order]).'?order_id=AGP-NOPE99')->assertOk();

        $this->assertSame(OrderStatus::Pending, $order->refresh()->status);
    }

    public function test_cashfree_webhook_completes_the_order_via_the_status_api(): void
    {
        $gateway = $this->cashfreeGateway();
        [, , $order] = $this->pendingOrder($gateway);

        Http::fake([
            'sandbox.cashfree.com/pg/orders/'.$order->reference => Http::response(['order_status' => 'PAID'], 200),
        ]);

        $body = json_encode([
            'type' => 'PAYMENT_SUCCESS_WEBHOOK',
            'data' => ['order' => ['order_id' => $order->reference, 'order_status' => 'PAID']],
        ]);

        $timestamp = (string) now()->getTimestamp();

        $this->call('POST', '/api/webhooks/cashfree', [], [], [], [
            'HTTP_X-Webhook-Signature' => base64_encode(hash_hmac('sha256', $timestamp.$body, 'cf_test_secret', true)),
            'HTTP_X-Webhook-Timestamp' => $timestamp,
        ], $body)->assertOk();

        $this->assertSame(OrderStatus::Paid, $order->refresh()->status);
        $this->assertTrue($order->subscription()->exists());
    }

    public function test_webhook_with_valid_signature_does_not_complete_an_unpaid_order(): void
    {
        $gateway = $this->cashfreeGateway();
        [, , $order] = $this->pendingOrder($gateway);

        Http::fake([
            'sandbox.cashfree.com/pg/orders/'.$order->reference => Http::response(['order_status' => 'ACTIVE'], 200),
        ]);

        $body = json_encode([
            'type' => 'PAYMENT_SUCCESS_WEBHOOK',
            'data' => ['order' => ['order_id' => $order->reference, 'order_status' => 'PAID']],
        ]);

        $timestamp = (string) now()->getTimestamp();

        // Even a correctly signed payload is not enough on its own: the
        // status API is the trust root and still reports the order ACTIVE.
        $this->call('POST', '/api/webhooks/cashfree', [], [], [], [
            'HTTP_X-Webhook-Signature' => base64_encode(hash_hmac('sha256', $timestamp.$body, 'cf_test_secret', true)),
            'HTTP_X-Webhook-Timestamp' => $timestamp,
        ], $body)->assertOk();

        $this->assertSame(OrderStatus::Pending, $order->refresh()->status);
    }

    public function test_pay_page_loads_the_cashfree_js_sdk(): void
    {
        $this->cashfreeGateway(['is_enabled' => true, 'is_default' => true]);

        $product = Product::factory()->create(['is_active' => true]);

        Plan::factory()->create([
            'product_id' => $product->getKey(),
            'code' => 'pro',
            'is_active' => true,
        ]);

        Http::fake([
            'sandbox.cashfree.com/pg/orders' => Http::response([
                'cf_order_id' => 'CF555',
                'order_status' => 'ACTIVE',
                'payment_session_id' => 'session_page_1',
            ], 200),
        ]);

        $this->post('/'.$product->slug, [
            'plan' => 'pro',
            'subscriber_key' => 'user-42',
        ])->assertRedirect();

        $order = Order::sole();

        $this->get(route('checkout.pay', [$product, $order]))
            ->assertOk()
            ->assertSee('https://sdk.cashfree.com/js/v3/cashfree.js', false)
            ->assertSee('session_page_1', false);
    }
}
