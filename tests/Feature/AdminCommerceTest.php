<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Payments\GatewayManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCommerceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_guest_cannot_manage_products(): void
    {
        $this->get('/admin/products')->assertRedirect(route('admin.login'));
    }

    public function test_product_index_lists_seeded_products(): void
    {
        Product::factory()->create(['name' => 'Tongdon']);

        $this->actingAs($this->admin())
            ->get('/admin/products')
            ->assertOk()
            ->assertSee('Tongdon');
    }

    public function test_product_can_be_created(): void
    {
        // The create and edit forms must actually render.
        $this->actingAs($this->admin())
            ->get('/admin/products/create')
            ->assertOk()
            ->assertSee('New Product');

        $product = Product::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('Edit Product')
            ->assertSee('API credentials');

        $this->actingAs($this->admin())
            ->post('/admin/products', [
                'name' => 'New Product',
                'slug' => 'new-product',
                'domain' => 'newproduct.com',
                'callback_url' => 'https://newproduct.com/webhooks/agape',
                'description' => 'A test product.',
                'sort_order' => 0,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', ['slug' => 'new-product', 'is_active' => true]);
    }

    public function test_duplicate_product_slug_is_rejected(): void
    {
        Product::factory()->create(['slug' => 'tongdon']);

        $this->actingAs($this->admin())
            ->from('/admin/products/create')
            ->post('/admin/products', ['name' => 'Dupe', 'slug' => 'tongdon', 'is_active' => 1])
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('slug');
    }

    public function test_product_credentials_can_be_rotated(): void
    {
        $product = Product::factory()->create();
        $oldKey = $product->api_key;

        $response = $this->actingAs($this->admin())
            ->post(route('admin.products.rotate', $product));

        $response->assertSessionHas('rotated_secret');
        $this->assertNotSame($oldKey, $product->refresh()->api_key);
    }

    public function test_plan_can_be_created_for_a_product(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin())
            ->post('/admin/plans', [
                'product_id' => $product->getKey(),
                'name' => 'Pro',
                'code' => 'pro',
                'billing_type' => 'recurring',
                'price' => 49900,
                'currency' => 'INR',
                'interval_days' => 30,
                'validity_days' => '',
                'sort_order' => 0,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.plans.index'));

        $this->assertDatabaseHas('plans', ['code' => 'pro', 'product_id' => $product->getKey()]);
    }

    public function test_plan_code_must_be_unique_per_product(): void
    {
        $product = Product::factory()->create();

        Plan::factory()->create(['product_id' => $product->getKey(), 'code' => 'pro']);

        $this->actingAs($this->admin())
            ->from('/admin/plans/create')
            ->post('/admin/plans', [
                'product_id' => $product->getKey(),
                'name' => 'Pro again',
                'code' => 'pro',
                'billing_type' => 'one_time',
                'price' => 100,
                'currency' => 'INR',
            ])
            ->assertSessionHasErrors('code');
    }

    public function test_gateway_can_be_configured_and_enabled(): void
    {
        $gateway = PaymentGateway::factory()->create(['driver' => 'razorpay', 'is_enabled' => false]);

        $this->actingAs($this->admin())
            ->put(route('admin.gateways.update', $gateway), [
                'mode' => 'test',
                'is_enabled' => 1,
                'is_default' => 1,
                'key_id' => 'rzp_test_123',
                'key_secret' => 'secret123',
            ])
            ->assertRedirect(route('admin.gateways.index'));

        $gateway->refresh();

        $this->assertTrue($gateway->is_enabled);
        $this->assertTrue($gateway->is_default);
        $this->assertSame('rzp_test_123', $gateway->credentials()['key_id']);
    }

    public function test_blank_gateway_fields_keep_stored_credentials(): void
    {
        $gateway = PaymentGateway::factory()->configured(['key_id' => 'rzp_test_old', 'key_secret' => 'oldsecret'])->create();

        $this->actingAs($this->admin())
            ->put(route('admin.gateways.update', $gateway), [
                'mode' => 'test',
                'is_enabled' => 1,
                'is_default' => 0,
                'key_id' => '',
                'key_secret' => '',
            ]);

        $credentials = $gateway->refresh()->credentials();

        $this->assertSame('rzp_test_old', $credentials['key_id']);
        $this->assertSame('oldsecret', $credentials['key_secret']);
    }

    public function test_setting_a_default_gateway_clears_the_others(): void
    {
        $first = PaymentGateway::factory()->create(['driver' => 'razorpay', 'is_default' => true]);
        $second = PaymentGateway::factory()->create(['driver' => 'payu']);

        $this->actingAs($this->admin())
            ->put(route('admin.gateways.update', $second), [
                'mode' => 'test',
                'is_enabled' => 1,
                'is_default' => 1,
            ]);

        $this->assertFalse($first->refresh()->is_default);
        $this->assertTrue($second->refresh()->is_default);
    }

    public function test_orders_index_and_detail_pages_render(): void
    {
        $order = Order::factory()->paid()->create();

        $this->actingAs($this->admin())
            ->get('/admin/orders')
            ->assertOk()
            ->assertSee($order->reference);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee($order->subscriber_key);
    }

    public function test_orders_can_be_filtered_by_status(): void
    {
        $order = Order::factory()->paid()->create();
        Order::factory()->create();

        $this->actingAs($this->admin())
            ->get('/admin/orders?status=paid')
            ->assertOk()
            ->assertSee($order->reference);
    }

    public function test_subscriptions_index_renders(): void
    {
        Subscription::factory()->create();

        $this->actingAs($this->admin())
            ->get('/admin/subscriptions')
            ->assertOk()
            ->assertSee('Subscriptions');
    }

    public function test_gateways_index_lists_every_driver(): void
    {
        collect(GatewayManager::DRIVERS)->keys()
            ->each(fn (string $driver) => PaymentGateway::factory()->create(['driver' => $driver, 'name' => ucfirst($driver)]));

        $this->actingAs($this->admin())
            ->get('/admin/gateways')
            ->assertOk()
            ->assertSee('Razorpay')
            ->assertSee('Phonepe')
            ->assertSee('Cashfree')
            ->assertSee('Payu')
            ->assertSee('Paypal');
    }
}
