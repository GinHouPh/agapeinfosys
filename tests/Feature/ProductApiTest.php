<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Sign a request the way a product's backend would.
     *
     * @return array<string, string>
     */
    private function signedHeaders(Product $product, string $method = 'GET', string $path = 'api/v1/entitlement'): array
    {
        $timestamp = (string) now()->timestamp;

        return [
            'X-Agape-Key' => $product->api_key,
            'X-Agape-Timestamp' => $timestamp,
            'X-Agape-Signature' => hash_hmac('sha256', $timestamp.'.'.$method.'.'.$path, $product->api_secret),
        ];
    }

    public function test_requests_without_credentials_are_rejected(): void
    {
        $this->getJson('/api/v1/entitlement')->assertUnauthorized();
    }

    public function test_requests_with_a_wrong_signature_are_rejected(): void
    {
        $product = Product::factory()->create(['is_active' => true]);

        $headers = $this->signedHeaders($product);
        $headers['X-Agape-Signature'] = 'invalid';

        $this->getJson('/api/v1/entitlement', $headers)->assertUnauthorized();
    }

    public function test_requests_with_a_stale_timestamp_are_rejected(): void
    {
        $product = Product::factory()->create(['is_active' => true]);

        $headers = $this->signedHeaders($product);
        $headers['X-Agape-Timestamp'] = (string) now()->subHour()->timestamp;

        $this->getJson('/api/v1/entitlement', $headers)->assertUnauthorized();
    }

    public function test_signature_is_bound_to_the_request_path(): void
    {
        $product = Product::factory()->create(['is_active' => true]);

        // Signature made for a different path must not work here.
        $headers = $this->signedHeaders($product, 'GET', 'api/v1/somewhere-else');

        $this->getJson('/api/v1/entitlement', $headers)->assertUnauthorized();
    }

    public function test_entitlement_returns_active_subscription(): void
    {
        $product = Product::factory()->create(['is_active' => true]);
        $plan = Plan::factory()->create(['product_id' => $product->getKey()]);

        Subscription::factory()->create([
            'product_id' => $product->getKey(),
            'plan_id' => $plan->getKey(),
            'subscriber_key' => 'user-42',
            'ends_at' => now()->addMonths(3),
        ]);

        $response = $this->getJson('/api/v1/entitlement?subscriber_key=user-42', $this->signedHeaders($product));

        $response->assertOk()
            ->assertJsonPath('entitled', true)
            ->assertJsonPath('subscription.plan_code', $plan->code)
            ->assertJsonPath('subscription.status', 'active');
    }

    public function test_entitlement_reports_no_access_for_unknown_subscriber(): void
    {
        $product = Product::factory()->create(['is_active' => true]);

        $this->getJson('/api/v1/entitlement?subscriber_key=nobody', $this->signedHeaders($product))
            ->assertOk()
            ->assertJsonPath('entitled', false)
            ->assertJsonPath('subscription', null);
    }

    public function test_entitlement_denies_expired_subscriptions(): void
    {
        $product = Product::factory()->create(['is_active' => true]);
        $plan = Plan::factory()->create(['product_id' => $product->getKey()]);

        Subscription::factory()->expired()->create([
            'product_id' => $product->getKey(),
            'plan_id' => $plan->getKey(),
            'subscriber_key' => 'user-42',
        ]);

        $this->getJson('/api/v1/entitlement?subscriber_key=user-42', $this->signedHeaders($product))
            ->assertOk()
            ->assertJsonPath('entitled', false)
            ->assertJsonPath('subscription.status', 'expired');
    }

    public function test_entitlement_is_scoped_to_the_authenticated_product(): void
    {
        $productA = Product::factory()->create(['is_active' => true]);
        $productB = Product::factory()->create(['is_active' => true]);

        $planB = Plan::factory()->create(['product_id' => $productB->getKey()]);

        Subscription::factory()->create([
            'product_id' => $productB->getKey(),
            'plan_id' => $planB->getKey(),
            'subscriber_key' => 'user-42',
        ]);

        // Product A must never see product B's entitlement.
        $this->getJson('/api/v1/entitlement?subscriber_key=user-42', $this->signedHeaders($productA))
            ->assertOk()
            ->assertJsonPath('entitled', false);
    }

    public function test_order_lookup_by_reference(): void
    {
        $product = Product::factory()->create(['is_active' => true]);
        $plan = Plan::factory()->create(['product_id' => $product->getKey()]);

        $order = Order::factory()->paid()->create([
            'product_id' => $product->getKey(),
            'plan_id' => $plan->getKey(),
            'status' => OrderStatus::Paid,
        ]);

        $path = 'api/v1/orders/'.$order->reference;

        $this->getJson($path, $this->signedHeaders($product, 'GET', $path))
            ->assertOk()
            ->assertJsonPath('reference', $order->reference)
            ->assertJsonPath('status', 'paid');
    }

    public function test_order_lookup_is_scoped_to_the_authenticated_product(): void
    {
        $productA = Product::factory()->create(['is_active' => true]);
        $productB = Product::factory()->create(['is_active' => true]);

        $planB = Plan::factory()->create(['product_id' => $productB->getKey()]);

        $orderB = Order::factory()->create([
            'product_id' => $productB->getKey(),
            'plan_id' => $planB->getKey(),
        ]);

        $path = 'api/v1/orders/'.$orderB->reference;

        $this->getJson($path, $this->signedHeaders($productA, 'GET', $path))->assertNotFound();
    }
}
