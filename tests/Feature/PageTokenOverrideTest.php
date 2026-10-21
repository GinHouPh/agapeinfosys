<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use App\Services\ThemeCompiler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTokenOverrideTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_a_page_can_override_its_own_colours(): void
    {
        $page = Page::factory()->published()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.pages.design.update', $page), [
                'overrides' => [
                    'dark' => ['accent' => '#ff0055'],
                    'light' => ['accent' => '#00aa55'],
                ],
            ])
            ->assertRedirect(route('admin.pages.design', $page));

        $overrides = $page->refresh()->tokenOverrides();

        $this->assertSame('#ff0055', $overrides['dark']['accent']);
        $this->assertSame('#00aa55', $overrides['light']['accent']);
    }

    public function test_a_token_the_platform_does_not_know_is_dropped(): void
    {
        $page = Page::factory()->published()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.pages.design.update', $page), [
                'overrides' => [
                    'dark' => ['accent' => '#ff0055', 'not-a-token' => '#000000'],
                ],
            ]);

        $overrides = $page->refresh()->tokenOverrides();

        $this->assertArrayHasKey('accent', $overrides['dark']);
        $this->assertArrayNotHasKey('not-a-token', $overrides['dark']);
    }

    public function test_a_value_that_is_not_a_colour_is_dropped(): void
    {
        $page = Page::factory()->published()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.pages.design.update', $page), [
                'overrides' => ['dark' => ['accent' => 'javascript:alert(1)']],
            ]);

        $this->assertSame([], $page->refresh()->tokenOverrides());
    }

    public function test_the_overrides_reach_the_rendered_page(): void
    {
        $page = Page::factory()->published()->create([
            'token_overrides' => ['dark' => ['accent' => '#ff0055']],
        ]);

        $this->get('/pages/'.$page->path)
            ->assertOk()
            ->assertSee('id="page-tokens"', false)
            ->assertSee('--color-accent:#ff0055', false);
    }

    public function test_a_page_without_overrides_emits_nothing(): void
    {
        $page = Page::factory()->published()->create();

        $this->get('/pages/'.$page->path)
            ->assertOk()
            ->assertDontSee('id="page-tokens"', false);
    }

    public function test_the_compiler_emits_the_light_scheme_under_its_own_selector(): void
    {
        $tag = app(ThemeCompiler::class)->pageTag([
            'light' => ['accent' => '#00aa55'],
        ]);

        $this->assertStringContainsString('html.light{--color-accent:#00aa55}', (string) $tag);
    }

    public function test_the_design_screen_renders(): void
    {
        $page = Page::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.pages.design', $page))
            ->assertOk()
            ->assertSee('Page Colours', false);
    }
}
