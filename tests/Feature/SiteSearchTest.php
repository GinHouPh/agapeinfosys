<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_search_page_renders(): void
    {
        $this->get(route('search.index'))
            ->assertOk()
            ->assertSee('Search');
    }

    public function test_a_short_or_empty_query_returns_no_results(): void
    {
        Service::factory()->create(['title' => 'Cloud Migration', 'is_published' => true]);

        $this->get(route('search.index', ['q' => 'x']))
            ->assertOk()
            ->assertDontSee('Cloud Migration', false);

        $this->get(route('search.index'))
            ->assertOk()
            ->assertDontSee('Cloud Migration', false);
    }

    public function test_search_finds_published_services_projects_posts_and_products(): void
    {
        Service::factory()->create(['title' => 'Quantum Consulting', 'is_published' => true]);
        Project::factory()->create(['title' => 'Quantum Portal', 'is_published' => true]);
        Post::factory()->create(['title' => 'Quantum Notes', 'is_published' => true]);
        Product::factory()->create(['name' => 'Quantum Suite', 'is_active' => true]);

        $this->get(route('search.index', ['q' => 'Quantum']))
            ->assertOk()
            ->assertSee('Quantum Consulting', false)
            ->assertSee('Quantum Portal', false)
            ->assertSee('Quantum Notes', false)
            ->assertSee('Quantum Suite', false);
    }

    public function test_search_excludes_drafts_and_inactive_records(): void
    {
        Service::factory()->create(['title' => 'Hidden Service', 'is_published' => false]);
        Project::factory()->create(['title' => 'Hidden Project', 'is_published' => false]);
        Post::factory()->create(['title' => 'Hidden Post', 'is_published' => false]);
        Product::factory()->create(['name' => 'Hidden Product', 'is_active' => false]);

        $this->get(route('search.index', ['q' => 'Hidden']))
            ->assertOk()
            ->assertDontSee('Hidden Service', false)
            ->assertDontSee('Hidden Project', false)
            ->assertDontSee('Hidden Post', false)
            ->assertDontSee('Hidden Product', false);
    }

    public function test_an_unknown_query_reports_no_results(): void
    {
        $this->get(route('search.index', ['q' => 'nothingmatches']))
            ->assertOk()
            ->assertSee('No results found', false);
    }

    public function test_the_floating_header_exposes_search_cart_and_account(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('aria-label="Search the site"', false)
            ->assertSee(route('shop.cart.index'), false)
            ->assertSee('aria-label="Login"', false)
            ->assertSee(route('search.index'), false);
    }

    public function test_the_account_icon_points_to_the_dashboard_for_signed_in_clients(): void
    {
        $client = User::factory()->create();

        $this->actingAs($client)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('aria-label="Dashboard"', false)
            ->assertSee(route('dashboard.index'), false);
    }
}
