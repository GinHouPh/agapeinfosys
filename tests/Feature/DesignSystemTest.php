<?php

namespace Tests\Feature;

use App\Builder\DesignTokens;
use App\Models\DesignTheme;
use App\Models\User;
use App\Services\ThemeCompiler;
use Database\Seeders\DesignThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesignSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_guests_are_redirected_from_the_design_editor(): void
    {
        $theme = DesignTheme::factory()->create();

        $this->get(route('admin.design.index'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.design.store'), [])->assertRedirect(route('admin.login'));
        $this->get(route('admin.design.edit', $theme))->assertRedirect(route('admin.login'));
    }

    public function test_a_theme_can_be_created(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.design.store'), ['name' => 'Midnight'])
            ->assertRedirect();

        $theme = DesignTheme::query()->where('name', 'Midnight')->firstOrFail();

        $this->assertSame('midnight', $theme->slug);
        $this->assertArrayHasKey('dark', $theme->tokens);
        $this->assertArrayHasKey('accent', $theme->tokens['dark']);
    }

    public function test_a_theme_rejects_a_value_that_is_not_a_token(): void
    {
        $theme = DesignTheme::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.design.update', $theme), [
                'name' => $theme->name,
                'tokens' => ['dark' => ['not-a-token' => '#ffffff']],
            ])
            ->assertSessionHasErrors('tokens.dark.not-a-token');
    }

    public function test_a_theme_rejects_a_value_that_is_not_a_colour(): void
    {
        $theme = DesignTheme::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.design.update', $theme), [
                'name' => $theme->name,
                'tokens' => ['dark' => ['accent' => 'not-a-colour']],
            ])
            ->assertSessionHasErrors('tokens.dark.accent');
    }

    public function test_a_theme_accepts_hex_and_rgba_values(): void
    {
        $theme = DesignTheme::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.design.update', $theme), [
                'name' => $theme->name,
                'tokens' => [
                    'dark' => ['accent' => '#ff0055', 'accent-soft' => 'rgba(255, 0, 85, 0.2)'],
                ],
            ])
            ->assertSessionHasNoErrors();

        $theme->refresh();

        $this->assertSame('#ff0055', $theme->token('accent'));
        $this->assertSame('rgba(255, 0, 85, 0.2)', $theme->token('accent-soft'));
    }

    public function test_a_theme_can_be_made_live_and_only_one_is_live_at_a_time(): void
    {
        $first = DesignTheme::factory()->active()->create();
        $second = DesignTheme::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.design.activate', $second))
            ->assertRedirect(route('admin.design.index'));

        $this->assertTrue($second->refresh()->is_active);
        $this->assertFalse($first->refresh()->is_active);
        $this->assertSame($second->id, DesignTheme::active()?->id);
    }

    public function test_a_live_theme_cannot_be_deleted(): void
    {
        $theme = DesignTheme::factory()->active()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.design.destroy', $theme))
            ->assertSessionHasErrors('theme');

        $this->assertDatabaseHas('design_themes', ['id' => $theme->id]);
    }

    public function test_the_compiler_emits_a_custom_property_for_every_token(): void
    {
        $theme = DesignTheme::factory()->create([
            'tokens' => array_merge(config('design.tokens'), [
                'font' => ['display' => 'Outfit', 'body' => 'Inter'],
            ]),
        ]);

        $css = app(ThemeCompiler::class)->compile($theme);

        foreach (array_keys(DesignTokens::COLORS) as $name) {
            $this->assertStringContainsString('--color-'.$name.':', $css);
        }

        $this->assertStringContainsString("--font-display:'Outfit'", $css);
        $this->assertStringContainsString("--font-body:'Inter'", $css);
    }

    public function test_the_compiler_emits_the_light_scheme_under_its_own_selector(): void
    {
        $theme = DesignTheme::factory()->create();

        $css = app(ThemeCompiler::class)->compile($theme);

        $this->assertStringContainsString(':root{', $css);
        $this->assertStringContainsString('html.light{', $css);
    }

    public function test_no_style_tag_is_emitted_without_an_active_theme(): void
    {
        $this->assertNull(app(ThemeCompiler::class)->styleTag());
    }

    public function test_the_active_theme_is_emitted_into_the_page_head(): void
    {
        $this->seed(DesignThemeSeeder::class);

        $response = $this->get(route('home'));

        $response->assertOk()->assertSee('id="design-theme"', false);
    }

    public function test_the_seeded_theme_matches_the_stylesheet_defaults(): void
    {
        $this->seed(DesignThemeSeeder::class);

        $theme = DesignTheme::active();

        $this->assertNotNull($theme);

        foreach (array_keys(DesignTokens::COLORS) as $name) {
            $this->assertSame(
                config('design.tokens.dark.'.$name),
                $theme->token($name, 'dark'),
                'The seeded dark token ['.$name.'] drifted from the stylesheet.',
            );
        }
    }
}
