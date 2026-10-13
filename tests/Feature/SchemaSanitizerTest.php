<?php

namespace Tests\Feature;

use App\Builder\SchemaSanitizer;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchemaSanitizerTest extends TestCase
{
    use RefreshDatabase;

    private SchemaSanitizer $sanitizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sanitizer = app(SchemaSanitizer::class);
    }

    /**
     * Sanitize one block and return only its settings, so each test reads as
     * the single value it is about.
     *
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function settingsFor(string $widget, array $settings): array
    {
        $result = $this->sanitizer->sanitize([
            'blocks' => [['id' => 'b1', 'widget' => $widget, 'settings' => $settings]],
        ]);

        return $result['blocks'][0]['settings'] ?? [];
    }

    public function test_a_link_whose_scheme_a_browser_cannot_trust_is_dropped(): void
    {
        $dangerous = [
            'javascript:alert(1)',
            'JavaScript:alert(document.cookie)',
            'data:text/html,<script>alert(1)</script>',
            'vbscript:msgbox(1)',
            // The URL parser discards tabs and newlines before it reads the
            // scheme, so this is the same link wearing a disguise.
            "java\t\nscript:alert(1)",
            ' javascript:alert(1)',
        ];

        foreach ($dangerous as $link) {
            $settings = $this->settingsFor('cta-card', ['button_url' => $link]);

            $this->assertSame('', $settings['button_url'], "Kept a link that should have been dropped: {$link}");
        }
    }

    public function test_a_link_under_a_scheme_the_browser_can_trust_is_kept_verbatim(): void
    {
        $trusted = [
            'http://agapeinfosys.com/about',
            'https://agapeinfosys.com',
            'mailto:hello@agapeinfosys.com',
            'tel:+911234567890',
        ];

        foreach ($trusted as $link) {
            $settings = $this->settingsFor('cta-card', ['button_url' => $link]);

            $this->assertSame($link, $settings['button_url'], "Dropped a link that should have been kept: {$link}");
        }
    }

    public function test_a_link_without_a_scheme_carries_nothing_to_misuse_and_is_kept(): void
    {
        $schemeless = [
            '/services',
            '#pricing',
            '?ref=footer',
            'www.agapeinfosys.com',
        ];

        foreach ($schemeless as $link) {
            $settings = $this->settingsFor('cta-card', ['button_url' => $link]);

            $this->assertSame($link, $settings['button_url'], "Rewrote a site-local link: {$link}");
        }
    }

    public function test_a_link_is_checked_the_same_way_inside_a_repeater_row(): void
    {
        $settings = $this->settingsFor('cta-banner', [
            'buttons' => [
                ['label' => 'Good', 'url' => 'https://agapeinfosys.com/pricing', 'variant' => 'accent'],
                ['label' => 'Bad', 'url' => 'javascript:alert(1)', 'variant' => 'ghost'],
            ],
        ]);

        $this->assertSame('https://agapeinfosys.com/pricing', $settings['buttons'][0]['url']);
        $this->assertSame('', $settings['buttons'][1]['url']);
    }

    public function test_a_link_value_that_is_not_scalar_is_stored_as_an_empty_string(): void
    {
        $this->assertSame('', $this->settingsFor('cta-card', ['button_url' => ['https://x']])['button_url']);
        $this->assertSame('', $this->settingsFor('cta-card', ['button_url' => null])['button_url']);
        $this->assertSame('', $this->settingsFor('cta-card', ['button_url' => new \stdClass])['button_url']);
    }

    public function test_a_dangerous_link_never_reaches_the_database_through_the_save_endpoint(): void
    {
        $admin = User::factory()->admin()->create();
        $page = Page::factory()->create();

        $this->actingAs($admin)
            ->putJson(route('admin.pages.update', $page), [
                'title' => $page->title,
                'status' => 'draft',
                'schema' => [
                    'blocks' => [
                        ['id' => 'one', 'widget' => 'cta-card', 'settings' => ['button_url' => 'javascript:alert(1)']],
                    ],
                ],
            ])
            ->assertOk();

        $blocks = $page->refresh()->blocks();

        $this->assertSame('', $blocks[0]['settings']['button_url']);
    }
}
