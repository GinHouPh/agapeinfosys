<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Jobs\SendEntitlementWebhook;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\Plan;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Outbound product webhooks must never hit the network in tests.
        Queue::fake();
    }

    private function productAndOrder(string $driver = 'sandbox'): array
    {
        $product = Product::factory()->create(['is_active' => true]);
        $plan = Plan::factory()->create(['product_id' => $product->getKey()]);

        $gateway = PaymentGateway::factory()->create([
            'driver' => $driver,
            'name' => 'Test Gateway',
            'is_enabled' => true,
            'is_default' => true,
        ]);

        $order = Order::factory()->create([
            'product_id' => $product->getKey(),
            'plan_id' => $plan->getKey(),
            'payment_gateway_id' => $gateway->getKey(),
            'subscriber_key' => 'user-42',
            'status' => OrderStatus::Pending,
        ]);

        return [$product, $order, $gateway];
    }

    public function test_webhook_for_unknown_driver_returns_404(): void
    {
        $this->postJson('/api/webhooks/unknown-gateway', [])->assertNotFound();
    }

    public function test_signed_sandbox_webhook_completes_the_order(): void
    {
        Queue::fake();

        [, $order] = $this->productAndOrder();

        $body = json_encode(['event' => 'sandbox.paid', 'reference' => $order->reference]);

        $this->call(
            'POST',
            '/api/webhooks/sandbox',
            [],
            [],
            [],
            ['HTTP_X-Sandbox-Signature' => hash_hmac('sha256', $body, (string) config('app.key'))],
            $body
        )->assertOk();

        $this->assertSame(OrderStatus::Paid, $order->refresh()->status);
        $this->assertTrue($order->subscription()->exists());

        Queue::assertPushed(SendEntitlementWebhook::class);
    }

    public function test_webhook_is_idempotent_across_repeated_deliveries(): void
    {
        [, $order, $gateway] = $this->productAndOrder();

        $body = json_encode(['event' => 'sandbox.paid', 'reference' => $order->reference]);
        $signature = hash_hmac('sha256', $body, (string) config('app.key'));

        $headers = ['HTTP_X-Sandbox-Signature' => $signature];

        $this->call('POST', '/api/webhooks/sandbox', [], [], [], $headers, $body)->assertOk();
        $this->call('POST', '/api/webhooks/sandbox', [], [], [], $headers, $body)->assertOk();

        $order->refresh();

        $this->assertSame(OrderStatus::Paid, $order->status);

        // Exactly one processed completion event, not two.
        $this->assertSame(1, $order->events()->where('is_processed', true)->count());
    }

    public function test_untagged_order_reference_is_ignored(): void
    {
        $this->productAndOrder();

        $body = json_encode(['event' => 'sandbox.paid', 'reference' => 'AGP-NOPE99']);

        $this->call(
            'POST',
            '/api/webhooks/sandbox',
            [],
            [],
            [],
            ['HTTP_X-Sandbox-Signature' => hash_hmac('sha256', $body, (string) config('app.key'))],
            $body
        )->assertOk()->assertSee('ignored');
    }

    public function test_entitlement_webhook_payload_is_signed_for_the_product(): void
    {
        $product = Product::factory()->create(['is_active' => true, 'callback_url' => 'https://tongdon.com/webhooks/agape']);
        $plan = Plan::factory()->create(['product_id' => $product->getKey()]);

        $order = Order::factory()->paid()->create([
            'product_id' => $product->getKey(),
            'plan_id' => $plan->getKey(),
            'status' => OrderStatus::Paid,
        ]);

        Http::fake([
            'tongdon.com/*' => Http::response('', 200),
        ]);

        (new SendEntitlementWebhook($order->getKey()))->handle();

        Http::assertSent(function ($request) use ($product): bool {
            if (! str_contains($request->url(), 'tongdon.com/webhooks/agape')) {
                return false;
            }

            $timestamp = $request->header('X-Agape-Timestamp')[0];
            $signature = $request->header('X-Agape-Signature')[0];

            return $request->header('X-Agape-Key')[0] === $product->api_key
                && hash_equals(
                    hash_hmac('sha256', $timestamp.'.POST.webhooks/agape', $product->api_secret),
                    $signature
                );
        });
    }
}
