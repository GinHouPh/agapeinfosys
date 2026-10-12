<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\PaymentGateway;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ShopOrder;
use App\Models\ShopOrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminShopTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    /**
     * @return array{0: ShopOrder, 1: ShopOrderItem}
     */
    private function paidShopOrder(array $overrides = []): array
    {
        PaymentGateway::factory()->sandbox()->create();

        $product = Product::factory()->create(['name' => 'Tongdon', 'is_active' => true]);

        $plan = Plan::factory()->create([
            'product_id' => $product->getKey(),
            'name' => 'Yearly',
            'code' => 'yearly',
            'price' => 49900,
        ]);

        $order = ShopOrder::factory()->paid()->create(array_merge([
            'amount' => 49900,
            'subscriber_key' => 'user-42',
            'email' => 'buyer@example.com',
        ], $overrides));

        $item = $order->items()->create([
            'product_id' => $product->getKey(),
            'plan_id' => $plan->getKey(),
            'product_name' => $product->name,
            'plan_name' => $plan->name,
            'unit_price' => $plan->price,
            'total' => $plan->price,
            'currency' => $plan->currency,
        ]);

        return [$order, $item];
    }

    public function test_guest_cannot_view_shop_orders(): void
    {
        $this->get('/admin/shop-orders')->assertRedirect(route('admin.login'));
    }

    public function test_shop_order_index_lists_orders(): void
    {
        [$order] = $this->paidShopOrder();

        $this->actingAs($this->admin())
            ->get('/admin/shop-orders')
            ->assertOk()
            ->assertSee($order->reference)
            ->assertSee('buyer@example.com');
    }

    public function test_shop_order_index_filters_by_search(): void
    {
        [$order] = $this->paidShopOrder();
        $other = ShopOrder::factory()->create(['email' => 'someone-else@example.com']);

        $this->actingAs($this->admin())
            ->get('/admin/shop-orders?q='.$order->reference)
            ->assertOk()
            ->assertSee($order->reference)
            ->assertDontSee($other->reference);
    }

    public function test_shop_order_show_renders_items_and_customer(): void
    {
        [$order] = $this->paidShopOrder();

        $this->actingAs($this->admin())
            ->get(route('admin.shop-orders.show', $order))
            ->assertOk()
            ->assertSee($order->reference)
            ->assertSee('Tongdon')
            ->assertSee('Yearly')
            ->assertSee('user-42');
    }

    public function test_product_image_can_be_uploaded(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post('/admin/products', [
                'name' => 'Imaged Product',
                'slug' => 'imaged-product',
                'logo' => UploadedFile::fake()->image('product.png', 400, 400),
                'sort_order' => 0,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('slug', 'imaged-product')->sole();

        $this->assertNotNull($product->logo);
        Storage::disk('public')->assertExists($product->logo);
        $this->assertNotNull($product->imageUrl());
    }

    public function test_non_image_upload_is_rejected(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post('/admin/products', [
                'name' => 'Bad File',
                'slug' => 'bad-file',
                'logo' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
                'sort_order' => 0,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('logo');

        $this->assertSame(0, Product::query()->where('slug', 'bad-file')->count());
    }

    public function test_updating_a_product_without_a_new_image_keeps_the_old_one(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post('/admin/products', [
                'name' => 'Kept Image',
                'slug' => 'kept-image',
                'logo' => UploadedFile::fake()->image('keep.png', 200, 200),
                'sort_order' => 0,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('slug', 'kept-image')->sole();
        $logo = $product->logo;

        $this->actingAs($this->admin())
            ->put(route('admin.products.update', $product), [
                'name' => 'Kept Image Renamed',
                'slug' => 'kept-image',
                'sort_order' => 0,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertSame($logo, $product->fresh()->logo);
        Storage::disk('public')->assertExists($logo);
    }

    public function test_paid_cart_order_shows_status_in_the_index(): void
    {
        [$order] = $this->paidShopOrder(['status' => OrderStatus::Paid]);

        $this->actingAs($this->admin())
            ->get('/admin/shop-orders?status=paid')
            ->assertOk()
            ->assertSee($order->reference)
            ->assertSee('Paid');
    }
}
