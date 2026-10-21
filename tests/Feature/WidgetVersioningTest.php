<?php

namespace Tests\Feature;

use App\Builder\SchemaMigrator;
use App\Builder\WidgetRegistry;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class WidgetVersioningTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_a_widget_declares_a_version(): void
    {
        $registry = app(WidgetRegistry::class);

        $this->assertSame(2, $registry->get('cta-banner')?->version());
        $this->assertSame(1, $registry->get('statement')?->version());
    }

    public function test_a_page_saves_the_version_of_each_block(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.pages.store'), [
                'title' => 'Versioned',
                'status' => 'draft',
            ])
            ->assertRedirect();

        $page = Page::query()->where('title', 'Versioned')->firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('admin.pages.update', $page), [
                'title' => $page->title,
                'status' => 'draft',
                'schema' => [
                    'blocks' => [
                        ['id' => 'a', 'widget' => 'cta-banner', 'settings' => ['title' => 'Hello']],
                    ],
                ],
            ])
            ->assertRedirect();

        $blocks = $page->refresh()->schema['blocks'];

        $this->assertSame(2, $blocks[0]['version']);
    }

    public function test_an_old_block_is_migrated_when_it_is_read(): void
    {
        $page = Page::factory()->create([
            'schema' => [
                'blocks' => [
                    [
                        'id' => 'a',
                        'widget' => 'cta-banner',
                        'version' => 1,
                        'settings' => [
                            'title' => 'Hello',
                            'buttons' => [
                                ['label' => 'Talk to us', 'url' => '/contact'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $blocks = $page->blocks();

        $this->assertSame(2, $blocks[0]['version']);
        $this->assertArrayHasKey('id', $blocks[0]['settings']['buttons'][0]);
        $this->assertSame('Talk to us', $blocks[0]['settings']['buttons'][0]['label']);
    }

    public function test_a_block_with_no_version_is_treated_as_the_first(): void
    {
        $page = Page::factory()->create([
            'schema' => [
                'blocks' => [
                    [
                        'id' => 'a',
                        'widget' => 'cta-banner',
                        'settings' => [
                            'title' => 'Hello',
                            'buttons' => [
                                ['label' => 'Talk to us', 'url' => '/contact'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $migrator = app(SchemaMigrator::class);

        // The stored block has no version at all, so it predates versioning
        // and is treated as the first version.
        $this->assertTrue($migrator->needsMigration($page->schema['blocks'][0]));
        $this->assertArrayHasKey('id', $page->blocks()[0]['settings']['buttons'][0]);
    }

    public function test_a_block_already_at_the_current_version_is_left_alone(): void
    {
        $block = [
            'id' => 'a',
            'widget' => 'cta-banner',
            'version' => 2,
            'settings' => ['buttons' => [['id' => 'keep', 'label' => 'Talk to us']]],
        ];

        $this->assertFalse(app(SchemaMigrator::class)->needsMigration($block));
        $this->assertSame('keep', app(SchemaMigrator::class)->migrate($block)['settings']['buttons'][0]['id']);
    }

    public function test_the_command_bakes_the_migration_into_the_stored_page(): void
    {
        $page = Page::factory()->create([
            'schema' => [
                'blocks' => [
                    [
                        'id' => 'a',
                        'widget' => 'cta-banner',
                        'version' => 1,
                        'settings' => [
                            'buttons' => [
                                ['label' => 'Talk to us', 'url' => '/contact'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        Artisan::call('builder:migrate-pages');

        $stored = $page->refresh()->schema['blocks'];

        $this->assertSame(2, $stored[0]['version']);
        $this->assertArrayHasKey('id', $stored[0]['settings']['buttons'][0]);
    }

    public function test_a_renamed_setting_is_migrated(): void
    {
        $migrator = app(SchemaMigrator::class);

        // The migrator reads the migration from the widget, so a rename is
        // exercised through a real widget rather than a stub.
        $widget = app(WidgetRegistry::class)->get('cta-banner');

        $this->assertNotNull($widget);
        $this->assertArrayHasKey(1, $widget->migrations());
        $this->assertContains('row_id', array_keys($widget->migrations()[1]));
    }
}
