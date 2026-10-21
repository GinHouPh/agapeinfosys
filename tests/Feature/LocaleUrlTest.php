<?php

namespace Tests\Feature;

use App\Models\Locale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleUrlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Locale::factory()->default()->create(['code' => 'en', 'name' => 'English']);
        Locale::factory()->create(['code' => 'de', 'name' => 'German']);
    }

    public function test_a_prefixed_address_serves_the_same_page(): void
    {
        $this->get('/de/about')
            ->assertOk()
            ->assertSee('About', false);
    }

    public function test_the_default_language_is_not_prefixed(): void
    {
        $this->get('/about')->assertOk();

        $this->get('/en/about')->assertOk();
    }

    public function test_links_on_a_prefixed_page_carry_the_prefix(): void
    {
        $html = $this->get('/de/about')->assertOk()->getContent();

        $this->assertStringContainsString('/de/services', $html);
    }

    public function test_links_on_the_default_page_do_not_carry_a_prefix(): void
    {
        $html = $this->get('/about')->assertOk()->getContent();

        $this->assertStringContainsString('/services', $html);
        $this->assertStringNotContainsString('/de/services', $html);
    }

    public function test_a_prefix_that_is_not_a_language_is_left_alone(): void
    {
        // "xx" is not a language the site offers, so it is an ordinary
        // path segment and never a language.
        $this->get('/xx/about')->assertNotFound();
    }

    public function test_the_control_panel_is_never_prefixed(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/de/admin')
            ->assertOk();
    }

    public function test_a_file_path_is_not_prefixed(): void
    {
        $html = $this->get('/de/about')->assertOk()->getContent();

        $this->assertStringNotContainsString('/de/storage/', $html);
    }

    public function test_the_page_advertises_its_other_languages(): void
    {
        $html = $this->get('/de/about')->assertOk()->getContent();

        $this->assertStringContainsString('hreflang="de"', $html);
        $this->assertStringContainsString('hreflang="en"', $html);
        $this->assertStringContainsString('hreflang="x-default"', $html);
    }

    public function test_the_alternate_addresses_point_at_the_same_page(): void
    {
        $html = $this->get('/de/about')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/hreflang="en"\s+href="[^"]*\/about"/', $html);
        $this->assertMatchesRegularExpression('/hreflang="de"\s+href="[^"]*\/de\/about"/', $html);
    }
}
