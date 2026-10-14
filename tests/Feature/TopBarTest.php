<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopBarTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_top_bar_shows_contact_details_and_a_quote_link(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('mailto:', false)
            ->assertSee('tel:', false)
            ->assertSee('Get a Quote', false)
            ->assertSee(route('requirements.create'), false);
    }

    public function test_the_top_bar_offers_client_login_to_guests(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Client Login', false)
            ->assertSee(route('login'), false);
    }

    public function test_the_top_bar_offers_the_dashboard_to_signed_in_clients(): void
    {
        $client = User::factory()->create();

        $this->actingAs($client)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Dashboard', false)
            ->assertSee(route('dashboard.index'), false)
            ->assertDontSee('Client Login', false);
    }

    public function test_the_top_bar_carries_the_scroll_progress_element(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-scroll-progress', false);
    }

    public function test_the_top_bar_appears_on_public_pages_but_not_in_the_admin(): void
    {
        foreach (['/', '/about', '/services', '/shop', '/search'] as $uri) {
            $this->get($uri)
                ->assertOk()
                ->assertSee('Get a Quote', false);
        }

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Get a Quote', false);
    }
}
