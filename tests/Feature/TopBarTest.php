<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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

    public function test_the_top_bar_routes_administrators_to_the_admin_panel(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Dashboard', false)
            ->assertSee(route('admin.dashboard'), false)
            ->assertDontSee(route('dashboard.index'), false)
            ->assertDontSee('Client Login', false);
    }

    public function test_the_header_and_footer_links_point_admins_to_the_admin_panel(): void
    {
        $admin = User::factory()->admin()->create();

        $html = $this->actingAs($admin)
            ->get(route('home'))
            ->assertOk()
            ->getContent();

        $header = Str::between($html, '<header data-site-header', '</header>');
        $footer = Str::between($html, '<footer', '</footer>');

        $this->assertStringContainsString(route('admin.dashboard'), $header);
        $this->assertStringContainsString('Admin Panel', $footer);
        $this->assertStringNotContainsString('Client Dashboard', $footer);
    }

    public function test_the_top_bar_carries_the_scroll_progress_element(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-scroll-progress', false);
    }

    public function test_the_bottom_menu_drops_requirements_and_the_logo_moves_to_the_top_bar(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        $topBar = Str::before($html, '<header data-site-header');
        $bottomBar = Str::between($html, '<header data-site-header', '</header>');

        // The bottom menu stays lean: no redundant quote entry (the top
        // bar owns that CTA) and no logo (also the top bar's job now).
        $this->assertStringNotContainsString('Requirements', $bottomBar);
        $this->assertStringNotContainsString('aria-label="'.config('app.name').' home"', $bottomBar);

        // The logo is served from the centre of the top utility bar.
        $this->assertStringContainsString('aria-label="'.config('app.name').' home"', $topBar);
    }

    public function test_the_top_bar_hosts_a_three_way_theme_toggle(): void
    {
        $response = $this->get(route('home'))->assertOk();

        $topBar = Str::before($response->getContent(), '<header data-site-header');

        $this->assertStringContainsString('aria-label="Colour theme"', $topBar);

        foreach (['dark', 'light', 'system'] as $mode) {
            $this->assertStringContainsString("data-theme-choice=\"$mode\"", $topBar);
        }

        // The layout bootstraps the stored preference before first paint.
        $response->assertSee("localStorage.getItem('theme')", false);
    }

    public function test_the_admin_shell_keeps_its_own_dark_theme(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('data-theme-choice', false)
            ->assertDontSee("localStorage.getItem('theme')", false);
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
