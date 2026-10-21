<?php

namespace Tests\Feature;

use App\Enums\MenuItemType;
use App\Enums\MenuLocation;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Service;
use App\Models\User;
use App\Services\MenuService;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_guests_are_redirected_from_the_navigation_editor(): void
    {
        $menu = Menu::factory()->create();

        $this->get(route('admin.navigation.index'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.navigation.store'), [])->assertRedirect(route('admin.login'));
        $this->get(route('admin.navigation.edit', $menu))->assertRedirect(route('admin.login'));
        $this->put(route('admin.navigation.update', $menu), [])->assertRedirect(route('admin.login'));
        $this->delete(route('admin.navigation.destroy', $menu))->assertRedirect(route('admin.login'));
        $this->post(route('admin.navigation.items.store', $menu), [])->assertRedirect(route('admin.login'));
    }

    public function test_navigation_index_lists_the_menus_for_every_location(): void
    {
        $menu = Menu::factory()->location(MenuLocation::Header)->create(['name' => 'Primary']);

        $this->actingAs($this->admin)
            ->get(route('admin.navigation.index'))
            ->assertOk()
            ->assertSee('Primary');
    }

    public function test_a_menu_can_be_created(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.navigation.store'), [
                'name' => 'Main Navigation',
                'location' => MenuLocation::Header->value,
                'is_active' => '1',
            ])
            ->assertRedirect();

        $menu = Menu::query()->where('name', 'Main Navigation')->firstOrFail();

        $this->assertSame(MenuLocation::Header, $menu->location);
        $this->assertSame('main-navigation', $menu->slug);
        $this->assertTrue($menu->is_active);
    }

    public function test_a_menu_can_be_updated(): void
    {
        $menu = Menu::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.navigation.update', $menu), [
                'name' => 'Renamed Menu',
                'location' => MenuLocation::Footer->value,
                'is_active' => '0',
            ])
            ->assertRedirect();

        $menu->refresh();

        $this->assertSame('Renamed Menu', $menu->name);
        $this->assertSame(MenuLocation::Footer, $menu->location);
        $this->assertFalse($menu->is_active);
    }

    public function test_a_menu_is_deleted_with_its_items(): void
    {
        $menu = Menu::factory()->create();
        MenuItem::factory()->create(['menu_id' => $menu->id]);

        $this->actingAs($this->admin)
            ->delete(route('admin.navigation.destroy', $menu))
            ->assertRedirect(route('admin.navigation.index'));

        $this->assertDatabaseCount('menus', 0);
        $this->assertDatabaseCount('menu_items', 0);
    }

    public function test_an_item_can_be_added_to_a_menu(): void
    {
        $menu = Menu::factory()->location(MenuLocation::Header)->create();

        $this->actingAs($this->admin)
            ->post(route('admin.navigation.items.store', $menu), [
                'label' => 'Pricing',
                'link_type' => MenuItemType::Route->value,
                'link_value' => 'shop.index',
                'target' => '_self',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.navigation.edit', $menu));

        $this->assertDatabaseHas('menu_items', [
            'menu_id' => $menu->id,
            'label' => 'Pricing',
            'link_value' => 'shop.index',
        ]);
    }

    public function test_an_item_can_be_updated(): void
    {
        $menu = Menu::factory()->create();
        $item = MenuItem::factory()->create(['menu_id' => $menu->id]);

        $this->actingAs($this->admin)
            ->put(route('admin.navigation.items.update', [$menu, $item]), [
                'label' => 'Renamed',
                'link_type' => MenuItemType::Url->value,
                'link_value' => 'https://example.com',
                'target' => '_blank',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.navigation.edit', $menu));

        $item->refresh();

        $this->assertSame('Renamed', $item->label);
        $this->assertSame(MenuItemType::Url, $item->link_type);
        $this->assertTrue($item->opensInNewTab());
    }

    public function test_an_item_can_be_deleted(): void
    {
        $menu = Menu::factory()->create();
        $item = MenuItem::factory()->create(['menu_id' => $menu->id]);

        $this->actingAs($this->admin)
            ->delete(route('admin.navigation.items.destroy', [$menu, $item]))
            ->assertRedirect(route('admin.navigation.edit', $menu));

        $this->assertDatabaseCount('menu_items', 0);
    }

    public function test_an_item_belonging_to_another_menu_is_not_reachable(): void
    {
        $menu = Menu::factory()->create();
        $other = Menu::factory()->create();
        $item = MenuItem::factory()->create(['menu_id' => $other->id]);

        $this->actingAs($this->admin)
            ->put(route('admin.navigation.items.update', [$menu, $item]), [
                'label' => 'Hijacked',
                'link_type' => MenuItemType::Route->value,
                'link_value' => 'home',
                'target' => '_self',
            ])
            ->assertNotFound();

        $this->actingAs($this->admin)
            ->delete(route('admin.navigation.items.destroy', [$menu, $item]))
            ->assertNotFound();
    }

    public function test_an_item_cannot_nest_under_itself(): void
    {
        $menu = Menu::factory()->create();
        $item = MenuItem::factory()->create(['menu_id' => $menu->id]);

        $this->actingAs($this->admin)
            ->put(route('admin.navigation.items.update', [$menu, $item]), [
                'label' => 'Loop',
                'link_type' => MenuItemType::Route->value,
                'link_value' => 'home',
                'parent_id' => $item->id,
                'target' => '_self',
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_the_built_in_navigation_is_used_when_no_menu_is_saved(): void
    {
        $items = app(MenuService::class)->forLocation(MenuLocation::Header);

        $this->assertCount(8, $items);
        $this->assertSame('Home', $items[0]['label']);
        $this->assertSame(route('home'), $items[0]['url']);
        $this->assertSame('home', $items[0]['icon']);
    }

    public function test_the_seeded_menus_match_the_built_in_navigation(): void
    {
        $this->seed(MenuSeeder::class);

        foreach (['header', 'footer', 'legal'] as $location) {
            $seeded = app(MenuService::class)->forLocation(MenuLocation::from($location));
            $fallback = config('navigation.fallback.'.$location);

            $this->assertSame(
                array_column($fallback, 'label'),
                array_column($seeded, 'label'),
                'The seeded '.$location.' menu drifted from the built-in navigation.',
            );
        }
    }

    public function test_the_menu_editor_lists_the_items_of_its_menu(): void
    {
        $menu = Menu::factory()->create(['name' => 'Primary']);
        MenuItem::factory()->create(['menu_id' => $menu->id, 'label' => 'Visible Item']);
        MenuItem::factory()->create([
            'menu_id' => Menu::factory()->create()->id,
            'label' => 'Other Menu Item',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.navigation.edit', $menu))
            ->assertOk()
            ->assertSee('Primary')
            ->assertSee('Visible Item')
            ->assertDontSee('Other Menu Item');
    }

    public function test_an_active_menu_replaces_the_built_in_navigation(): void
    {
        $menu = Menu::factory()->location(MenuLocation::Header)->create();
        MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'label' => 'Only Link',
            'link_value' => 'about',
            'sort_order' => 0,
        ]);

        $items = app(MenuService::class)->forLocation(MenuLocation::Header);

        $this->assertCount(1, $items);
        $this->assertSame('Only Link', $items[0]['label']);
        $this->assertSame(route('about'), $items[0]['url']);
    }

    public function test_an_inactive_menu_does_not_replace_the_built_in_navigation(): void
    {
        $menu = Menu::factory()->location(MenuLocation::Header)->inactive()->create();
        MenuItem::factory()->create(['menu_id' => $menu->id, 'label' => 'Hidden Link']);

        $items = app(MenuService::class)->forLocation(MenuLocation::Header);

        $this->assertCount(8, $items);
        $this->assertNotContains('Hidden Link', array_column($items, 'label'));
    }

    public function test_items_are_ordered_by_their_sort_order(): void
    {
        $menu = Menu::factory()->location(MenuLocation::Header)->create();
        MenuItem::factory()->create(['menu_id' => $menu->id, 'label' => 'Second', 'sort_order' => 5]);
        MenuItem::factory()->create(['menu_id' => $menu->id, 'label' => 'First', 'sort_order' => 1]);

        $items = app(MenuService::class)->forLocation(MenuLocation::Header);

        $this->assertSame(['First', 'Second'], array_column($items, 'label'));
    }

    public function test_nested_items_are_resolved_under_their_parent(): void
    {
        $menu = Menu::factory()->location(MenuLocation::Header)->create();
        $parent = MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'label' => 'Services',
            'link_value' => 'services.index',
        ]);
        MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'parent_id' => $parent->id,
            'label' => 'Blog',
            'link_value' => 'blog.index',
        ]);

        $items = app(MenuService::class)->forLocation(MenuLocation::Header);

        $this->assertCount(1, $items);
        $this->assertCount(1, $items[0]['children']);
        $this->assertSame('Blog', $items[0]['children'][0]['label']);
        $this->assertSame(route('blog.index'), $items[0]['children'][0]['url']);
    }

    public function test_a_page_link_resolves_to_the_builder_page(): void
    {
        $page = Page::factory()->published()->create(['slug' => 'custom-page']);

        $menu = Menu::factory()->location(MenuLocation::Header)->create();
        MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'link_type' => MenuItemType::Page,
            'link_value' => $page->slug,
        ]);

        $items = app(MenuService::class)->forLocation(MenuLocation::Header);

        $this->assertSame($page->publicUrl(), $items[0]['url']);
    }

    public function test_a_record_link_resolves_to_the_record_url(): void
    {
        $service = Service::factory()->create(['is_published' => true]);

        $menu = Menu::factory()->location(MenuLocation::Header)->create();
        MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'link_type' => MenuItemType::Record,
            'link_value' => 'service:'.$service->slug,
        ]);

        $items = app(MenuService::class)->forLocation(MenuLocation::Header);

        $this->assertSame(route('services.show', $service), $items[0]['url']);
    }

    public function test_a_link_to_a_missing_record_resolves_to_nothing(): void
    {
        $menu = Menu::factory()->location(MenuLocation::Header)->create();
        MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'link_type' => MenuItemType::Record,
            'link_value' => 'service:does-not-exist',
        ]);

        $items = app(MenuService::class)->forLocation(MenuLocation::Header);

        $this->assertNull($items[0]['url']);
    }

    public function test_the_public_header_renders_the_saved_menu(): void
    {
        $menu = Menu::factory()->location(MenuLocation::Header)->create();
        MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'label' => 'Saved Link',
            'link_value' => 'about',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Saved Link');
    }

    public function test_the_public_footer_renders_the_saved_legal_menu(): void
    {
        $menu = Menu::factory()->location(MenuLocation::Legal)->create();
        MenuItem::factory()->create([
            'menu_id' => $menu->id,
            'label' => 'Saved Policy',
            'link_value' => 'legal.privacy',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Saved Policy');
    }
}
