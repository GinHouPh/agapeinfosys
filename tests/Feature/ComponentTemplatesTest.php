<?php

namespace Tests\Feature;

use App\Models\ComponentTemplate;
use App\Models\Page;
use App\Models\PageTemplate;
use App\Models\User;
use App\Services\TemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComponentTemplatesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_guests_are_redirected_from_the_template_editor(): void
    {
        $this->get(route('admin.templates.index'))->assertRedirect(route('admin.login'));
    }

    public function test_the_template_index_lists_components_and_page_templates(): void
    {
        ComponentTemplate::factory()->create(['name' => 'Call to action']);
        PageTemplate::factory()->create(['name' => 'Landing layout']);

        $this->actingAs($this->admin)
            ->get(route('admin.templates.index'))
            ->assertOk()
            ->assertSee('Call to action')
            ->assertSee('Landing layout');
    }

    public function test_a_component_can_be_saved_from_the_canvas(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.templates.components.store'), [
                'name' => 'Saved section',
                'category' => 'Marketing',
                'schema' => [
                    'blocks' => [
                        ['id' => 'abc', 'widget' => 'statement', 'settings' => ['text' => 'Hello']],
                    ],
                ],
                'slots' => [],
            ])
            ->assertRedirect(route('admin.templates.index'));

        $this->assertDatabaseHas('component_templates', [
            'name' => 'Saved section',
            'category' => 'Marketing',
        ]);
    }

    public function test_a_component_can_be_deleted(): void
    {
        $component = ComponentTemplate::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.templates.components.destroy', $component))
            ->assertRedirect(route('admin.templates.index'));

        $this->assertDatabaseMissing('component_templates', ['id' => $component->id]);
    }

    public function test_inserting_a_component_fills_its_slots(): void
    {
        $component = ComponentTemplate::factory()->create([
            'schema' => [
                'blocks' => [
                    [
                        'id' => 'original',
                        'widget' => 'statement',
                        'settings' => ['accent' => '{{heading}}', 'text' => '{{body}}'],
                    ],
                ],
            ],
            'slots' => [
                ['name' => 'heading', 'label' => 'Heading', 'type' => 'text', 'default' => 'Default heading'],
                ['name' => 'body', 'label' => 'Body', 'type' => 'textarea', 'default' => ''],
            ],
        ]);

        $blocks = app(TemplateService::class)->insertComponent($component, [
            'heading' => 'Growth',
            'body' => 'Ship faster.',
        ]);

        $this->assertSame('Growth', $blocks[0]['settings']['accent']);
        $this->assertSame('Ship faster.', $blocks[0]['settings']['text']);
    }

    public function test_an_unfilled_slot_falls_back_to_its_default(): void
    {
        $component = ComponentTemplate::factory()->create([
            'schema' => [
                'blocks' => [
                    ['id' => 'original', 'widget' => 'statement', 'settings' => ['text' => '{{body}}']],
                ],
            ],
            'slots' => [
                ['name' => 'body', 'label' => 'Body', 'type' => 'textarea', 'default' => 'Default body'],
            ],
        ]);

        $blocks = app(TemplateService::class)->insertComponent($component);

        $this->assertSame('Default body', $blocks[0]['settings']['text']);
    }

    public function test_an_inserted_component_gets_fresh_block_ids(): void
    {
        $component = ComponentTemplate::factory()->create();

        $first = app(TemplateService::class)->insertComponent($component);
        $second = app(TemplateService::class)->insertComponent($component);

        $this->assertNotSame('original', $first[0]['id']);
        $this->assertNotSame($first[0]['id'], $second[0]['id']);
    }

    public function test_inserting_counts_the_usage(): void
    {
        $component = ComponentTemplate::factory()->create();

        app(TemplateService::class)->insertComponent($component);

        $this->assertSame(1, $component->refresh()->usage_count);
    }

    public function test_the_builder_can_list_the_saved_components(): void
    {
        ComponentTemplate::factory()->create(['name' => 'Pricing table', 'category' => 'Marketing']);

        $this->actingAs($this->admin)
            ->get(route('admin.templates.components'))
            ->assertOk()
            ->assertJsonPath('components.0.name', 'Pricing table')
            ->assertJsonPath('components.0.category', 'Marketing');
    }

    public function test_the_insert_endpoint_returns_the_blocks(): void
    {
        $component = ComponentTemplate::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.templates.components.insert', $component), [
                'slots' => ['heading' => 'From the endpoint'],
            ])
            ->assertOk()
            ->assertJsonPath('blocks.0.settings.accent', 'From the endpoint');
    }

    public function test_a_page_can_be_created_from_a_page_template(): void
    {
        $template = PageTemplate::factory()->create([
            'schema' => [
                'blocks' => [
                    ['id' => 'seed', 'widget' => 'hero', 'settings' => ['title_top' => 'Hello']],
                ],
            ],
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.pages.store'), [
                'title' => 'New page',
                'status' => 'draft',
                'template' => $template->id,
            ])
            ->assertRedirect();

        $page = Page::query()->where('title', 'New page')->firstOrFail();

        $this->assertCount(1, $page->blocks());
        $this->assertSame('hero', $page->blocks()[0]['widget']);
        $this->assertNotSame('seed', $page->blocks()[0]['id']);
    }

    public function test_a_page_created_without_a_template_starts_blank(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.pages.store'), [
                'title' => 'Blank page',
                'status' => 'draft',
            ])
            ->assertRedirect();

        $page = Page::query()->where('title', 'Blank page')->firstOrFail();

        $this->assertSame([], $page->blocks());
    }
}
