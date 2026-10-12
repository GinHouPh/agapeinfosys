<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopCartTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{0: Product, 1: Plan, 2: Plan}
     */
    private function productWithPlans(): array
    {
        $product = Product::factory()->create(['is_active' => true]);

        $monthly = Plan::factory()->create([
            'product_id' => $product->getKey(),
            'code' => 'monthly',
            'name' => 'Monthly',
            'is_active' => true,
            'billing_type' => 'recurring',
            'interval_days' => 30,
            'validity_days' => null,
        ]);

        $yearly = Plan::factory()->create([
            'product_id' => $product->getKey(),
            'code' => 'yearly',
            'name' => 'Yearly',
            'is_active' => true,
        ]);

        return [$product, $monthly, $yearly];
    }

    /**
     * @return array{string: array<string, array{product_id: int, plan_id: int}>}
     */
    private function cartSession(Product $product, Plan $plan): array
    {
        return [
            'shop.cart' => [
                $product->getKey().':'.$plan->getKey() => [
                    'product_id' => $product->getKey(),
                    'plan_id' => $plan->getKey(),
                ],
            ],
        ];
    }

    public function test_shop_lists_active_products_with_their_plans(): void
    {
        [$product, $monthly] = $this->productWithPlans();
        Plan::factory()->inactive()->create(['product_id' => $product->getKey(), 'name' => 'Hidden Plan']);
        $hidden = Product::factory()->inactive()->create();

        $response = $this->get('/shop');

        $visiblePrice = $product->plans
            ->where('is_active', true)
            ->sortBy('price')
            ->first()
            ->formattedPrice();

        $response->assertOk()
            ->assertSee($product->name)
            ->assertSee($visiblePrice)
            ->assertSee('View plans', false)
            ->assertDontSee('Hidden Plan', false)
            ->assertDontSee($hidden->name);
    }

    public function test_product_page_lists_active_plans_with_add_to_cart(): void
    {
        [$product, $monthly] = $this->productWithPlans();
        Plan::factory()->inactive()->create(['product_id' => $product->getKey(), 'name' => 'Hidden Plan']);

        $response = $this->get('/shop/'.$product->slug);

        $response->assertOk()
            ->assertSee($monthly->name)
            ->assertSee('Add to cart', false)
            ->assertDontSee('Hidden Plan', false);
    }

    public function test_inactive_or_unknown_product_returns_404(): void
    {
        $product = Product::factory()->inactive()->create();

        $this->get('/shop/'.$product->slug)->assertNotFound();
        $this->get('/shop/not-a-product')->assertNotFound();
    }

    public function test_plan_can_be_added_to_the_cart(): void
    {
        [$product, $monthly] = $this->productWithPlans();

        $this->from('/shop/'.$product->slug)
            ->post('/shop/cart', ['product' => $product->slug, 'plan' => 'monthly'])
            ->assertRedirect('/shop/'.$product->slug)
            ->assertSessionHas('shop.cart');

        $items = session('shop.cart');

        $this->assertCount(1, $items);
        $this->assertSame($product->getKey(), reset($items)['product_id']);
        $this->assertSame($monthly->getKey(), reset($items)['plan_id']);
    }

    public function test_unknown_plan_is_rejected(): void
    {
        [$product] = $this->productWithPlans();

        $this->post('/shop/cart', ['product' => $product->slug, 'plan' => 'nope'])
            ->assertSessionHasErrors('plan');

        $this->assertCount(0, session('shop.cart', []));
    }

    public function test_only_one_plan_per_product_may_be_in_the_cart(): void
    {
        [$product, $monthly, $yearly] = $this->productWithPlans();

        $this->post('/shop/cart', ['product' => $product->slug, 'plan' => 'monthly']);
        $this->post('/shop/cart', ['product' => $product->slug, 'plan' => 'yearly'])
            ->assertSessionHasErrors('plan');

        $this->assertCount(1, session('shop.cart'));
    }

    public function test_cart_page_shows_lines_and_subtotal(): void
    {
        [$product, $monthly] = $this->productWithPlans();

        $this->withSession($this->cartSession($product, $monthly))
            ->get('/shop/cart')
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee($monthly->name)
            ->assertSee('Subtotal', false);
    }

    public function test_empty_cart_page_shows_empty_state(): void
    {
        $this->get('/shop/cart')
            ->assertOk()
            ->assertSee('Your cart is empty.');
    }

    public function test_cart_line_can_be_removed(): void
    {
        [$product, $monthly] = $this->productWithPlans();

        $this->withSession($this->cartSession($product, $monthly))
            ->delete('/shop/cart', [
                'product_id' => $product->getKey(),
                'plan_id' => $monthly->getKey(),
            ])
            ->assertRedirect(route('shop.cart.index'));

        $this->assertSame([], session('shop.cart'));
    }
}
