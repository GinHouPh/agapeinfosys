<?php

namespace Tests\Feature;

use App\Builder\WidgetRegistry;
use App\Models\CollectionRecord;
use App\Models\CollectionType;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_the_widget_is_in_the_palette(): void
    {
        $manifest = app(WidgetRegistry::class)->manifest();

        $names = array_column($manifest, 'name');

        $this->assertContains('collection-grid', $names);
    }

    public function test_the_schema_endpoint_offers_the_live_collections(): void
    {
        CollectionType::factory()->create(['slug' => 'work', 'name' => 'Work']);

        $this->actingAs($this->admin)
            ->post(route('admin.builder.widget-schema'), ['widget' => 'collection-grid'])
            ->assertOk()
            ->assertJsonPath('controls.0.options.work', 'Work');
    }

    public function test_the_widget_lists_the_records_of_its_collection(): void
    {
        $type = CollectionType::factory()->create(['slug' => 'work']);
        CollectionRecord::factory()->create([
            'collection_type_id' => $type->id,
            'title' => 'Acme Corporation',
            'is_published' => true,
        ]);
        CollectionRecord::factory()->create([
            'collection_type_id' => $type->id,
            'title' => 'Hidden Co',
            'is_published' => false,
        ]);

        $html = $this->actingAs($this->admin)
            ->post(route('admin.builder.preview'), [
                'widget' => 'collection-grid',
                'settings' => ['collection' => 'work', 'limit' => 6],
            ])
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Acme Corporation', $html);
        $this->assertStringNotContainsString('Hidden Co', $html);
    }

    public function test_a_collection_value_is_kept_when_the_page_is_saved(): void
    {
        CollectionType::factory()->create(['slug' => 'work']);

        $this->actingAs($this->admin)
            ->post(route('admin.pages.store'), [
                'title' => 'Collection page',
                'status' => 'draft',
            ])
            ->assertRedirect();

        $page = Page::query()->where('title', 'Collection page')->firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('admin.pages.update', $page), [
                'title' => $page->title,
                'status' => 'draft',
                'schema' => [
                    'blocks' => [
                        [
                            'id' => 'a',
                            'widget' => 'collection-grid',
                            'settings' => ['collection' => 'work', 'limit' => 3],
                        ],
                    ],
                ],
            ])
            ->assertRedirect();

        $blocks = $page->refresh()->schema['blocks'];

        $this->assertSame('work', $blocks[0]['settings']['collection']);
    }

    public function test_a_value_that_is_not_a_collection_renders_nothing(): void
    {
        $html = $this->actingAs($this->admin)
            ->post(route('admin.builder.preview'), [
                'widget' => 'collection-grid',
                'settings' => ['collection' => 'nothing-here'],
            ])
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Nothing here yet', $html);
    }
}
