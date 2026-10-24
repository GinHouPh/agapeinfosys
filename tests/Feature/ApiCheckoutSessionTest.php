<?php

namespace Tests\Feature;

use App\Models\PaymentGateway;
use App\Models\Plan;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ApiCheckoutSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_can_create_checkout_session_via_api(): void
    {
        PaymentGateway::factory()->sandbox()->create();

        $product = Product::factory()->create([
            'slug' => 'smt',
            'api_key' => 'pk_test_smt_123',
            'is_active' => true,
        ]);

        $plan = Plan::factory()->create([
            'product_id' => $product->getKey(),
            'code' => 'basic',
            'price' => 25000,
            'is_active' => true,
        ]);

        $response = $this->withHeaders([
            'X-Agape-Key' => 'pk_test_smt_123',
            'Accept' => 'application/json',
        ])->postJson('/api/v1/checkout/session', [
            'plan' => 'basic',
            'subscriber_key' => 'firebase_uid_test',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'gateway' => 'sandbox',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'order_id',
                'amount',
                'currency',
            ]);
    }

    public function test_can_check_entitlement_status_via_api(): void
    {
        $product = Product::factory()->create([
            'slug' => 'smt',
            'api_key' => 'pk_test_smt_123',
            'is_active' => true,
        ]);

        $response = $this->withHeaders([
            'X-Agape-Key' => 'pk_test_smt_123',
            'Accept' => 'application/json',
        ])->getJson('/api/v1/entitlement/status?subscriber_key=non_existing_uid');

        $response->assertOk()
            ->assertJson([
                'entitled' => false,
                'subscription' => null,
            ]);
    }
}
