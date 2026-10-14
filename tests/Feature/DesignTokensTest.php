<?php

namespace Tests\Feature;

use App\Builder\DesignTokens;
use App\Builder\WidgetRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A colour control stores a token NAME, so two vocabularies have to agree:
 * the PHP list the sanitizer will accept, and the custom properties the
 * stylesheet actually declares.
 *
 * Nothing in the runtime can catch them drifting — a token that stops
 * existing in app.css still sanitises cleanly, and then renders as whatever
 * colour the element happens to inherit, which looks like a theme bug rather
 * than the missing definition it is.
 */
class DesignTokensTest extends TestCase
{
    // Walking every widget's controls pulls in FormEmbedWidget, whose options
    // list is the live set of forms — so the schema has to exist.
    use RefreshDatabase;

    private WidgetRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = app(WidgetRegistry::class);
    }

    public function test_every_token_is_declared_as_a_custom_property_in_the_stylesheet(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        foreach (array_keys(DesignTokens::COLORS) as $name) {
            $this->assertStringContainsString(
                "--color-{$name}:",
                $css,
                "Design token [{$name}] has no matching --color-{$name} declared in app.css.",
            );
        }
    }

    public function test_every_widget_color_control_defaults_to_a_token_that_exists(): void
    {
        foreach ($this->registry->all() as $name => $widget) {
            foreach ($widget->controls() as $control) {
                if (($control['type'] ?? '') !== 'color') {
                    continue;
                }

                $this->assertTrue(
                    DesignTokens::isColor($control['default'] ?? null),
                    "Widget [{$name}] control [{$control['key']}] defaults to a token the palette does not have.",
                );
            }
        }
    }

    public function test_a_color_control_does_not_spell_out_its_own_options(): void
    {
        // The palette belongs to the sanitizer. A widget carrying a second
        // list would be one more thing to keep in step, and the whole reason
        // this control type exists is to have exactly one.
        foreach ($this->registry->all() as $name => $widget) {
            foreach ($widget->controls() as $control) {
                if (($control['type'] ?? '') !== 'color') {
                    continue;
                }

                $this->assertArrayNotHasKey(
                    'options',
                    $control,
                    "Widget [{$name}] control [{$control['key']}] declares its own options; a color control takes the global palette.",
                );
            }
        }
    }

    public function test_the_palette_is_the_source_of_truth_for_a_color_controls_values(): void
    {
        $statement = $this->registry->get('statement');

        $this->assertSame('accent', $statement->defaults()['accent_color']);
        $this->assertContains('muted', array_keys(DesignTokens::COLORS));
    }
}
