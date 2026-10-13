<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_guests_are_redirected_from_the_page_builder(): void
    {
        $page = Page::factory()->create();

        $this->get(route('admin.pages.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.pages.create'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.pages.edit', $page))->assertRedirect(route('admin.login'));
        $this->post(route('admin.pages.store'), [])->assertRedirect(route('admin.login'));
        $this->put(route('admin.pages.update', $page), [])->assertRedirect(route('admin.login'));
        $this->delete(route('admin.pages.destroy', $page))->assertRedirect(route('admin.login'));
        $this->post(route('admin.builder.preview'), [])->assertRedirect(route('admin.login'));
        $this->post(route('admin.builder.widget-schema'), [])->assertRedirect(route('admin.login'));
        $this->post(route('admin.builder.upload'), [])->assertRedirect(route('admin.login'));
    }

    public function test_non_admins_cannot_manage_pages(): void
    {
        $page = Page::factory()->create();
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get(route('admin.pages.index'))
            ->assertForbidden();

        $this->actingAs($member)
            ->put(route('admin.pages.update', $page), ['title' => 'Nope'])
            ->assertForbidden();
    }

    public function test_pages_index_lists_existing_pages(): void
    {
        Page::factory()->create(['title' => 'Summer Campaign']);

        $this->actingAs($this->admin)
            ->get(route('admin.pages.index'))
            ->assertOk()
            ->assertSee('Summer Campaign');
    }

    public function test_a_page_can_be_created_with_a_slug_derived_from_the_title(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.pages.store'), [
            'title' => 'Partner Landing',
            'status' => 'draft',
        ]);

        $page = Page::sole();

        $response->assertRedirect(route('admin.pages.edit', $page));
        $this->assertSame('partner-landing', $page->slug);
        $this->assertTrue($page->status->isDraft());
    }

    public function test_page_title_and_status_are_required(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.pages.create'))
            ->post(route('admin.pages.store'), [])
            ->assertRedirect(route('admin.pages.create'))
            ->assertSessionHasErrors(['title', 'status']);

        $this->assertSame(0, Page::query()->count());
    }

    public function test_duplicate_page_slugs_are_rejected(): void
    {
        Page::factory()->create(['slug' => 'partner-landing']);

        $this->actingAs($this->admin)
            ->post(route('admin.pages.store'), [
                'title' => 'Another',
                'slug' => 'partner-landing',
                'status' => 'draft',
            ])
            ->assertSessionHasErrors('slug');
    }

    public function test_the_canvas_renders_for_a_page(): void
    {
        $page = Page::factory()->create(['title' => 'Landing One']);

        $this->actingAs($this->admin)
            ->get(route('admin.pages.edit', $page))
            ->assertOk()
            ->assertSee('Landing One')
            ->assertSee('pb-canvas', false);
    }

    public function test_page_meta_can_be_updated_without_touching_the_schema(): void
    {
        $page = Page::factory()->create([
            'schema' => ['blocks' => [['id' => 'abc', 'widget' => 'statement', 'settings' => []]]],
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.pages.update', $page), [
                'title' => 'Renamed Page',
                'slug' => 'renamed-page',
                'status' => 'published',
            ])
            ->assertRedirect(route('admin.pages.edit', $page));

        $page->refresh();
        $this->assertSame('Renamed Page', $page->title);
        $this->assertSame('renamed-page', $page->slug);
        $this->assertTrue($page->status->isPublished());
        $this->assertCount(1, $page->blocks());
    }

    public function test_saving_the_schema_normalises_blocks_and_drops_unknown_widgets(): void
    {
        $page = Page::factory()->create();

        $this->actingAs($this->admin)
            ->putJson(route('admin.pages.update', $page), [
                'title' => $page->title,
                'status' => 'draft',
                'schema' => [
                    'blocks' => [
                        ['id' => 'one', 'widget' => 'hero', 'settings' => ['eyebrow' => 'Hi']],
                        ['id' => 'two', 'widget' => 'not-a-widget', 'settings' => []],
                        ['widget' => 'statement', 'settings' => []],
                        'not-an-array',
                    ],
                ],
            ])
            ->assertOk();

        $blocks = $page->refresh()->blocks();

        // Known widgets survive; the unknown widget and junk entries are dropped.
        $this->assertCount(2, $blocks);
        $this->assertSame('hero', $blocks[0]['widget']);
        $this->assertSame('statement', $blocks[1]['widget']);
        // The block without an id got one generated.
        $this->assertNotEmpty($blocks[1]['id']);
    }

    public function test_a_page_can_be_deleted(): void
    {
        $page = Page::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.pages.destroy', $page))
            ->assertRedirect(route('admin.pages.index'));

        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }

    public function test_the_preview_endpoint_renders_a_widget(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.builder.preview'), [
                'widget' => 'statement',
                'settings' => ['accent' => 'Ship faster', 'text' => 'with the builder.'],
            ]);

        $response->assertOk()->assertJsonStructure(['html']);
        $this->assertStringContainsString('Ship faster', $response->json('html'));
        $this->assertStringContainsString('with the builder.', $response->json('html'));
    }

    public function test_the_preview_endpoint_rejects_unknown_widgets(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.builder.preview'), ['widget' => 'nope'])
            ->assertStatus(422);
    }

    public function test_the_widget_schema_endpoint_returns_controls_and_defaults(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.builder.widget-schema'), ['widget' => 'hero']);

        $response->assertOk()->assertJsonStructure(['controls', 'defaults']);

        $keys = array_column($response->json('controls'), 'key');
        $this->assertContains('eyebrow', $keys);

        $this->assertArrayHasKey('eyebrow', $response->json('defaults'));
    }

    public function test_an_image_can_be_uploaded_through_the_builder(): void
    {
        $file = UploadedFile::fake()->image('hero.jpg', 600, 400);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.builder.upload'), ['image' => $file]);

        $response->assertOk()->assertJsonStructure(['path', 'url']);
        $this->assertNotEmpty($response->json('path'));
        Storage::disk('public')->assertExists($response->json('path'));
    }
}
