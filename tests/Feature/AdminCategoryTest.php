<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_guest_cannot_manage_categories(): void
    {
        $this->get('/admin/categories')->assertRedirect(route('admin.login'));
    }

    public function test_category_index_lists_categories_in_tree_order(): void
    {
        $market = Category::factory()->create(['name' => 'Web']);
        $category = Category::factory()->childOf($market)->create(['name' => 'Code']);
        Category::factory()->childOf($category)->create(['name' => 'Laravel']);

        $response = $this->actingAs($this->admin())->get('/admin/categories');

        $response->assertOk()->assertSee('Web')->assertSee('Laravel');

        $rows = collect($response->viewData('rows'));

        $this->assertSame(['Web', 'Code', 'Laravel'], $rows->map(fn (array $row) => $row['category']->name)->all());
        $this->assertSame([0, 1, 2], $rows->pluck('depth')->all());
    }

    public function test_category_index_filters_by_search(): void
    {
        Category::factory()->create(['name' => 'Alpha Market']);
        Category::factory()->create(['name' => 'Zeta Market']);

        $response = $this->actingAs($this->admin())->get('/admin/categories?q=Alpha');

        $response->assertOk()->assertSee('Alpha Market')->assertDontSee('Zeta Market');

        $this->assertCount(1, $response->viewData('rows'));
    }

    public function test_category_can_be_created(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/categories/create')
            ->assertOk()
            ->assertSee('New Category');

        $this->actingAs($this->admin())
            ->post('/admin/categories', [
                'name' => 'PHP Scripts',
                'slug' => 'php-scripts',
                'description' => 'Scripts and plugins.',
                'sort_order' => 3,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'slug' => 'php-scripts',
            'name' => 'PHP Scripts',
            'is_active' => true,
            'sort_order' => 3,
        ]);
    }

    public function test_category_can_be_nested_under_a_parent(): void
    {
        $market = Category::factory()->create(['name' => 'Web Themes']);

        $this->actingAs($this->admin())
            ->post('/admin/categories', [
                'name' => 'Landing Pages',
                'slug' => 'landing-pages',
                'parent_id' => $market->getKey(),
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'slug' => 'landing-pages',
            'parent_id' => $market->getKey(),
        ]);
    }

    public function test_blank_sort_order_defaults_to_zero(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/categories', [
                'name' => 'No Order',
                'sort_order' => '',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', ['slug' => 'no-order', 'sort_order' => 0]);
    }

    public function test_category_edit_form_renders(): void
    {
        $category = Category::factory()->create(['name' => 'Edit Me']);

        $this->actingAs($this->admin())
            ->get(route('admin.categories.edit', $category))
            ->assertOk()
            ->assertSee('Edit Category')
            ->assertSee('Edit Me');
    }

    public function test_duplicate_category_slug_is_rejected(): void
    {
        Category::factory()->create(['slug' => 'php-scripts']);

        $this->actingAs($this->admin())
            ->from('/admin/categories/create')
            ->post('/admin/categories', ['name' => 'Dupe', 'slug' => 'php-scripts', 'is_active' => 1])
            ->assertRedirect('/admin/categories/create')
            ->assertSessionHasErrors('slug');
    }

    public function test_category_can_be_updated(): void
    {
        $category = Category::factory()->create(['name' => 'Old Name', 'is_active' => true]);

        $this->actingAs($this->admin())
            ->put(route('admin.categories.update', $category), [
                'name' => 'New Name',
                'slug' => $category->slug,
                'is_active' => 0,
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->getKey(),
            'name' => 'New Name',
            'is_active' => false,
        ]);
    }

    public function test_category_cannot_be_its_own_parent(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin())
            ->from(route('admin.categories.edit', $category))
            ->put(route('admin.categories.update', $category), [
                'name' => $category->name,
                'parent_id' => $category->getKey(),
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.categories.edit', $category))
            ->assertSessionHasErrors('parent_id');

        $this->assertNull($category->refresh()->parent_id);
    }

    public function test_category_cannot_be_nested_under_its_own_descendant(): void
    {
        $market = Category::factory()->create();
        $child = Category::factory()->childOf($market)->create();

        $this->actingAs($this->admin())
            ->from(route('admin.categories.edit', $market))
            ->put(route('admin.categories.update', $market), [
                'name' => $market->name,
                'parent_id' => $child->getKey(),
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('parent_id');

        $this->assertNull($market->refresh()->parent_id);
    }

    public function test_category_with_subcategories_cannot_be_deleted(): void
    {
        $market = Category::factory()->create();
        Category::factory()->childOf($market)->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $market))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('error');

        $this->assertModelExists($market);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $category = Category::factory()->create();
        Product::factory()->inCategory($category)->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('error');

        $this->assertModelExists($category);
    }

    public function test_category_can_be_deleted(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status');

        $this->assertModelMissing($category);
    }
}
