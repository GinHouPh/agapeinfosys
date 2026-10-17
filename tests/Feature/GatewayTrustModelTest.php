<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\Plan;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * The payment trust model, proved per driver: a webhook payload that claims
 * success is ignored unless the provider's own status API confirms it.
 *
 * Cashfree is covered in depth by CashfreeDriverTest; this file exercises
 * the confirm() gate for PhonePe, PayU and PayPal.
 */
class GatewayTrustModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // No outbound product webhooks or receipt mail over the network.
        Queue::fake();
    }

    /**
     * @param  array<string, string>  $config
     */
    private function gateway(string $driver, array $config): PaymentGateway
    {
        return PaymentGateway::factory()->configured($config)->create([
            'driver' => $driver,
            'is_enabled' => true,
            'is_default' => true,
            'mode' => 'test',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function pendingOrder(PaymentGateway $gateway, array $overrides = []): Order
    {
        $product = Product::factory()->create(['is_active' => true]);
        $plan = Plan::factory()->create(['product_id' => $product->getKey(), 'code' => 'pro']);

        return Order::factory()->create(array_merge([
            'product_id' => $product->getKey(),
            'plan_id' => $plan->getKey(),
            'payment_gateway_id' => $gateway->getKey(),
            'subscriber_key' => 'user-42',
            'name' => 'Buyer',
            'email' => 'buyer@example.com',
            'status' => OrderStatus::Pending,
        ], $overrides));
    }

    public function test_phonepe_webhook_is_ignored_when_the_status_api_reports_pending(): void
    {
        $gateway = $this->gateway('phonepe', ['merchant_id' => 'M1', 'salt' => 'S1', 'salt_index' => '1']);
        $order = $this->pendingOrder($gateway);

        Http::fake([
            'api-preprod.phonepe.com/*' => Http::response(['code' => 'SUCCESS', 'data' => ['state' => 'PENDING']]),
        ]);

        $this->postJson(route('checkout.webhook', 'phonepe'), [
            'data' => ['merchantOrderId' => $order->reference],
        ])->assertOk()->assertSee('unconfirmed');

        $this->assertSame(OrderStatus::Pending, $order->refresh()->status);
    }

    public function test_phonepe_webhook_completes_only_after_the_status_api_confirms(): void
    {
        $gateway = $this->gateway('phonepe', ['merchant_id' => 'M1', 'salt' => 'S1', 'salt_index' => '1']);
        $order = $this->pendingOrder($gateway);

        Http::fake([
            'api-preprod.phonepe.com/*' => Http::response(['code' => 'SUCCESS', 'data' => ['state' => 'COMPLETED']]),
        ]);

        $this->postJson(route('checkout.webhook', 'phonepe'), [
            'data' => ['merchantOrderId' => $order->reference],
        ])->assertOk()->assertSee('ok');

        $this->assertSame(OrderStatus::Paid, $order->refresh()->status);
    }

    public function test_payu_webhook_is_ignored_when_the_status_api_reports_failure(): void
    {
        $gateway = $this->gateway('payu', ['merchant_key' => 'K1', 'salt' => 'S1']);
        $order = $this->pendingOrder($gateway);

        Http::fake([
            'info.payu.in/*' => Http::response(['status' => 'failure']),
        ]);

        $this->post(route('checkout.webhook', 'payu'), ['txnid' => $order->reference])
            ->assertOk()
            ->assertSee('unconfirmed');

        $this->assertSame(OrderStatus::Pending, $order->refresh()->status);
    }

    public function test_payu_webhook_completes_only_after_the_status_api_confirms(): void
    {
        $gateway = $this->gateway('payu', ['merchant_key' => 'K1', 'salt' => 'S1']);
        $order = $this->pendingOrder($gateway);

        Http::fake([
            'info.payu.in/*' => Http::response(['status' => 'success']),
        ]);

        $this->post(route('checkout.webhook', 'payu'), ['txnid' => $order->reference])
            ->assertOk()
            ->assertSee('ok');

        $this->assertSame(OrderStatus::Paid, $order->refresh()->status);
    }

    public function test_paypal_webhook_is_ignored_when_the_status_api_reports_unpaid(): void
    {
        $gateway = $this->gateway('paypal', [
            'client_id' => 'id',
            'client_secret' => 'secret',
            'webhook_id' => 'wh',
        ]);
        $order = $this->pendingOrder($gateway, ['gateway_order_id' => 'PAYPAL-1']);

        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(['access_token' => 'tok']),
            'api-m.sandbox.paypal.com/v2/checkout/orders/*' => Http::response(['status' => 'APPROVED']),
        ]);

        $this->postJson(route('checkout.webhook', 'paypal'), [
            'resource' => ['custom_id' => $order->reference],
        ])->assertOk()->assertSee('unconfirmed');

        $this->assertSame(OrderStatus::Pending, $order->refresh()->status);
    }

    public function test_paypal_webhook_completes_only_after_the_status_api_confirms(): void
    {
        $gateway = $this->gateway('paypal', [
            'client_id' => 'id',
            'client_secret' => 'secret',
            'webhook_id' => 'wh',
        ]);
        $order = $this->pendingOrder($gateway, ['gateway_order_id' => 'PAYPAL-1']);

        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(['access_token' => 'tok']),
            'api-m.sandbox.paypal.com/v2/checkout/orders/*' => Http::response(['status' => 'COMPLETED']),
        ]);

        $this->postJson(route('checkout.webhook', 'paypal'), [
            'resource' => ['custom_id' => $order->reference],
        ])->assertOk()->assertSee('ok');

        $this->assertSame(OrderStatus::Paid, $order->refresh()->status);
    }
}
