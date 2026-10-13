<?php

namespace Tests\Feature;

use App\Builder\PageRenderer;
use App\Builder\WidgetRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WidgetLibraryTest extends TestCase
{
    use RefreshDatabase;

    private WidgetRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = app(WidgetRegistry::class);
    }

    public function test_the_registry_exposes_the_expected_widget_names(): void
    {
        $names = $this->registry->names();

        foreach ([
            'hero', 'statement', 'marquee-band', 'media-text', 'rich-text',
            'feature-cards', 'stats', 'process-steps', 'values',
            'services-grid', 'services-list', 'projects-grid', 'testimonials',
            'clients-marquee', 'posts-grid', 'contact-section', 'cta-banner',
        ] as $expected) {
            $this->assertContains($expected, $names, "Missing widget [{$expected}].");
        }
    }

    public function test_every_widget_declares_valid_metadata_and_controls(): void
    {
        foreach ($this->registry->all() as $name => $widget) {
            $this->assertSame($name, $widget->name(), "Widget name mismatch for [{$name}].");
            $this->assertNotSame('', trim($widget->title()), "Widget [{$name}] needs a title.");
            $this->assertNotSame('', trim($widget->category()), "Widget [{$name}] needs a category.");
            $this->assertNotSame('', trim($widget->description()), "Widget [{$name}] needs a description.");
            $this->assertFileExists(resource_path('views/'.str_replace('.', '/', $widget->view()).'.blade.php'), "Widget [{$name}] points at a missing view.");

            $controls = $widget->controls();
            $this->assertNotEmpty($controls, "Widget [{$name}] declares no controls.");

            $keys = array_column($controls, 'key');
            $this->assertSame($keys, array_unique($keys), "Widget [{$name}] has duplicate control keys.");

            foreach ($controls as $control) {
                $this->assertNotEmpty($control['key'], "Widget [{$name}] has a control without a key.");
                $this->assertNotEmpty($control['label'], "Widget [{$name}] control [{$control['key']}] needs a label.");
                $this->assertContains($control['type'], ['text', 'textarea', 'number', 'select', 'toggle', 'image', 'url', 'repeater'],
                    "Widget [{$name}] control [{$control['key']}] has an unknown type.");

                if ($control['type'] === 'select') {
                    $this->assertNotEmpty($control['options'] ?? [], "Widget [{$name}] control [{$control['key']}] needs options.");
                }

                if ($control['type'] === 'repeater') {
                    $this->assertNotEmpty($control['fields'] ?? [], "Widget [{$name}] control [{$control['key']}] needs repeater fields.");
                }
            }
        }
    }

    public function test_every_widget_renders_with_its_default_settings(): void
    {
        $renderer = app(PageRenderer::class);

        foreach ($this->registry->all() as $name => $widget) {
            $html = $renderer->renderBlock($name, $widget->defaults());

            $this->assertNotSame('', trim($html), "Widget [{$name}] rendered empty HTML with default settings.");
            $this->assertStringNotContainsString('Exception', $html, "Widget [{$name}] threw while rendering.");
        }
    }

    public function test_defaults_are_derived_from_the_control_schema(): void
    {
        $hero = $this->registry->get('hero');

        $this->assertSame('Software solutions & IT consultancy', $hero->defaults()['eyebrow']);
        $this->assertTrue($hero->defaults()['show_card']);
        $this->assertIsArray($hero->defaults()['buttons']);
        $this->assertNotEmpty($hero->defaults()['buttons']);
    }

    public function test_unknown_widgets_render_empty_and_the_registry_returns_null(): void
    {
        $this->assertNull($this->registry->get('does-not-exist'));

        $renderer = app(PageRenderer::class);
        $this->assertSame('', $renderer->renderBlock('does-not-exist', []));
    }

    public function test_the_renderer_skips_malformed_blocks(): void
    {
        $renderer = app(PageRenderer::class);

        $html = $renderer->renderBlocks([
            ['widget' => 'statement', 'settings' => ['accent' => 'Keep', 'text' => 'me.']],
            [],
            ['widget' => 'missing-widget', 'settings' => []],
            'junk',
        ]);

        $this->assertStringContainsString('Keep', $html);
        $this->assertStringNotContainsString('missing-widget', $html);
    }

    public function test_the_renderer_ignores_unknown_settings_keys(): void
    {
        $renderer = app(PageRenderer::class);

        $html = $renderer->renderBlock('statement', [
            'accent' => 'Safe',
            'text' => 'output.',
            'evil' => '<script>alert(1)</script>',
        ]);

        $this->assertStringContainsString('Safe', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_the_manifest_drives_the_palette(): void
    {
        $manifest = $this->registry->manifest();

        $this->assertNotEmpty($manifest);

        foreach ($manifest as $entry) {
            $this->assertContainsOnly('string', [
                $entry['name'], $entry['title'], $entry['category'], $entry['description'], $entry['icon'],
            ]);
        }
    }

    public function test_widgets_are_grouped_by_category_for_the_palette(): void
    {
        $grouped = $this->registry->grouped();

        $this->assertArrayHasKey('Layout', $grouped);
        $this->assertArrayHasKey('Content', $grouped);
        $this->assertArrayHasKey('Dynamic', $grouped);
    }
}
