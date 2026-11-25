<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopBrowseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A live product filed under a category, with one purchasable plan.
     */
    private function productInCategory(Category $category): Product
    {
        $product = Product::factory()->inCategory($category)->create(['is_active' => true]);

        Plan::factory()->recurring()->create([
            'product_id' => $product->getKey(),
            'code' => 'monthly',
            'name' => 'Monthly',
            'is_active' => true,
        ]);

        return $product;
    }

    public function test_market_home_lists_markets_and_products(): void
    {
        $market = Category::factory()->create(['name' => 'Code']);
        $child = Category::factory()->childOf($market)->create(['name' => 'PHP Scripts']);
        $product = $this->productInCategory($child);

        $this->get('/shop')
            ->assertOk()
            ->assertSee('Code')
            ->assertSee('PHP Scripts')
            ->assertSee($product->name);
    }

    public function test_category_page_lists_products_from_its_whole_subtree(): void
    {
        $market = Category::factory()->create(['name' => 'Code']);
        $child = Category::factory()->childOf($market)->create(['name' => 'PHP Scripts']);
        $grandchild = Category::factory()->childOf($child)->create(['name' => 'Forms']);

        $inTree = $this->productInCategory($grandchild);
        $outside = $this->productInCategory(Category::factory()->create());

        $this->get('/shop/category/'.$market->slug)
            ->assertOk()
            ->assertSee($inTree->name)
            ->assertDontSee($outside->name)
            ->assertSee('PHP Scripts');
    }

    public function test_category_page_404s_for_inactive_category(): void
    {
        $category = Category::factory()->inactive()->create();

        $this->get('/shop/category/'.$category->slug)->assertNotFound();
    }

    public function test_product_detail_shows_breadcrumb_plans_and_related(): void
    {
        $market = Category::factory()->create(['name' => 'Code']);
        $child = Category::factory()->childOf($market)->create(['name' => 'PHP Scripts']);

        $product = $this->productInCategory($child);
        $related = $this->productInCategory($child);

        $this->get('/shop/'.$product->slug)
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('Monthly')
            ->assertSee('Pricing &amp; License', false)
            ->assertSee('Support &amp; Delivery', false)
            ->assertSee('Add to cart', false)
            ->assertSee('same corner of the marketplace', false)
            ->assertSee($related->name)
            ->assertDontSee('Choose your plan', false)
            ->assertDontSee('Need a custom build?', false);
    }

    public function test_product_detail_renders_markdown_body_preview_url_and_gallery(): void
    {
        $product = Product::factory()->create([
            'is_active' => true,
            'preview_url' => 'https://demo.example.com',
            'body' => "## Feature Highlights\n\n- [Admin Demo](https://admin.example.com)\n\n![Dashboard](https://example.com/dash.png)",
            'gallery' => ['https://example.com/shot-1.png'],
        ]);

        $this->get('/shop/'.$product->slug)
            ->assertOk()
            ->assertSee('https://demo.example.com', false)
            ->assertSee('<h2>Feature Highlights</h2>', false)
            ->assertSee('https://admin.example.com', false)
            ->assertSee('https://example.com/dash.png', false)
            ->assertSee('Screenshots', false)
            ->assertSee('https://example.com/shot-1.png', false);
    }

    public function test_descendant_ids_cover_the_whole_subtree(): void
    {
        $market = Category::factory()->create();
        $child = Category::factory()->childOf($market)->create();
        $grandchild = Category::factory()->childOf($child)->create();
        $sibling = Category::factory()->create();

        $ids = $market->fresh()->descendantIds();

        $this->assertEqualsCanonicalizing(
            [$market->getKey(), $child->getKey(), $grandchild->getKey()],
            $ids,
        );
        $this->assertNotContains($sibling->getKey(), $ids);
    }

    public function test_select_options_are_depth_indented(): void
    {
        $market = Category::factory()->create(['name' => 'Code', 'sort_order' => 0]);
        $child = Category::factory()->childOf($market)->create(['name' => 'PHP Scripts', 'sort_order' => 0]);

        $options = Category::selectOptions();

        $this->assertSame('Code', $options[$market->getKey()]);
        $this->assertSame('— PHP Scripts', $options[$child->getKey()]);
    }

    public function test_ancestors_return_the_chain_from_the_root(): void
    {
        $market = Category::factory()->create(['name' => 'Code']);
        $child = Category::factory()->childOf($market)->create(['name' => 'PHP Scripts']);
        $grandchild = Category::factory()->childOf($child)->create(['name' => 'Forms']);

        $this->assertSame(
            ['Code', 'PHP Scripts'],
            $grandchild->ancestors()->pluck('name')->all(),
        );
    }
}
