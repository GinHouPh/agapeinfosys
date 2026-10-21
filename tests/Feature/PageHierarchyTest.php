<?php

namespace Tests\Feature;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use App\Services\PageDiffService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PageHierarchyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_a_top_level_page_keeps_its_slug_as_its_path(): void
    {
        $page = Page::factory()->create(['slug' => 'about']);

        $this->assertSame('about', $page->path);
    }

    public function test_a_nested_page_carries_its_parent_in_its_path(): void
    {
        $parent = Page::factory()->create(['slug' => 'about']);
        $child = Page::factory()->create(['slug' => 'team', 'parent_id' => $parent->id]);

        $this->assertSame('about/team', $child->path);
    }

    public function test_a_nested_page_is_served_at_its_path(): void
    {
        $parent = Page::factory()->published()->create(['slug' => 'about']);
        $child = Page::factory()->published()->create(['slug' => 'team', 'parent_id' => $parent->id]);

        $this->get('/pages/about/team')
            ->assertOk()
            ->assertSee($child->title, false);
    }

    public function test_moving_a_parent_re_paths_its_children(): void
    {
        $parent = Page::factory()->create(['slug' => 'about']);
        $child = Page::factory()->create(['slug' => 'team', 'parent_id' => $parent->id]);

        $parent->update(['slug' => 'company']);

        $this->assertSame('company/team', $child->refresh()->path);
    }

    public function test_the_breadcrumb_lists_the_ancestors_first(): void
    {
        $root = Page::factory()->create(['slug' => 'about', 'title' => 'About']);
        $mid = Page::factory()->create(['slug' => 'team', 'title' => 'Team', 'parent_id' => $root->id]);
        $leaf = Page::factory()->create(['slug' => 'devs', 'title' => 'Developers', 'parent_id' => $mid->id]);

        $trail = $leaf->breadcrumb();

        $this->assertSame(['About', 'Team', 'Developers'], array_column($trail, 'label'));
    }

    public function test_the_admin_addresses_a_page_by_its_id(): void
    {
        $page = Page::factory()->create(['slug' => 'about']);

        $this->assertStringContainsString('/admin/pages/'.$page->id, $page->adminRoute('admin.pages.edit'));
    }

    public function test_a_scheduled_page_is_not_live_before_its_date(): void
    {
        $page = Page::factory()->create([
            'status' => PageStatus::Scheduled,
            'scheduled_for' => now()->addDay(),
        ]);

        $this->assertFalse($page->isLive());
        $this->get('/pages/'.$page->path)->assertNotFound();
    }

    public function test_a_scheduled_page_goes_live_when_its_date_arrives(): void
    {
        $page = Page::factory()->create([
            'status' => PageStatus::Scheduled,
            'scheduled_for' => now()->subMinute(),
        ]);

        $this->assertTrue($page->isLive());
        $this->get('/pages/'.$page->path)->assertOk();
    }

    public function test_the_command_publishes_the_pages_whose_date_has_arrived(): void
    {
        $due = Page::factory()->create([
            'status' => PageStatus::Scheduled,
            'scheduled_for' => now()->subMinute(),
        ]);
        $later = Page::factory()->create([
            'status' => PageStatus::Scheduled,
            'scheduled_for' => now()->addDay(),
        ]);

        Artisan::call('pages:publish-scheduled');

        $this->assertSame(PageStatus::Published, $due->refresh()->status);
        $this->assertSame(PageStatus::Scheduled, $later->refresh()->status);
    }

    public function test_saving_a_layout_keeps_the_version_it_replaced(): void
    {
        $page = Page::factory()->create([
            'schema' => ['blocks' => [['id' => 'a', 'widget' => 'statement', 'settings' => ['text' => 'First']]]],
        ]);

        $page->update([
            'schema' => ['blocks' => [['id' => 'a', 'widget' => 'statement', 'settings' => ['text' => 'Second']]]],
        ]);

        $revision = $page->revisions()->firstOrFail();

        $this->assertSame('First', $revision->schema['blocks'][0]['settings']['text']);
    }

    public function test_only_the_last_twenty_versions_are_kept(): void
    {
        $page = Page::factory()->create();

        for ($i = 0; $i < 25; $i++) {
            $page->update([
                'schema' => ['blocks' => [['id' => 'a', 'widget' => 'statement', 'settings' => ['text' => 'v'.$i]]]],
            ]);
        }

        $this->assertSame(20, $page->revisions()->count());
    }

    public function test_a_saved_version_can_be_put_back(): void
    {
        $page = Page::factory()->create([
            'schema' => ['blocks' => [['id' => 'a', 'widget' => 'statement', 'settings' => ['text' => 'First']]]],
        ]);

        $page->update([
            'schema' => ['blocks' => [['id' => 'a', 'widget' => 'statement', 'settings' => ['text' => 'Second']]]],
        ]);

        $revision = $page->revisions()->firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('admin.pages.restore', [$page, $revision]))
            ->assertRedirect(route('admin.pages.edit', $page));

        $this->assertSame('First', $page->refresh()->schema['blocks'][0]['settings']['text']);
    }

    public function test_the_diff_reports_what_changed(): void
    {
        $before = ['blocks' => [['id' => 'a', 'widget' => 'hero', 'settings' => ['title_top' => 'Old', 'title_accent' => 'Keep']]]];
        $after = ['blocks' => [['id' => 'a', 'widget' => 'hero', 'settings' => ['title_top' => 'New', 'title_accent' => 'Keep']]]];

        $changes = app(PageDiffService::class)->compare($before, $after);

        $this->assertCount(1, $changes);
        $this->assertSame('changed', $changes[0]['type']);
        $this->assertSame('title_top', $changes[0]['setting']);
        $this->assertSame('Old', $changes[0]['before']);
        $this->assertSame('New', $changes[0]['after']);
    }

    public function test_the_diff_reports_a_section_that_was_added(): void
    {
        $after = ['blocks' => [['id' => 'b', 'widget' => 'cta-banner', 'settings' => []]]];

        $changes = app(PageDiffService::class)->compare(['blocks' => []], $after);

        $this->assertCount(1, $changes);
        $this->assertSame('added', $changes[0]['type']);
        $this->assertSame('cta-banner', $changes[0]['widget']);
    }

    public function test_the_history_and_compare_screens_render(): void
    {
        $page = Page::factory()->create();
        $page->update(['schema' => ['blocks' => [['id' => 'a', 'widget' => 'statement', 'settings' => []]]]]);
        $revision = $page->revisions()->firstOrFail();

        $this->actingAs($this->admin)
            ->get(route('admin.pages.revisions', $page))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('admin.pages.diff', [$page, $revision]))
            ->assertOk();
    }

    public function test_a_page_can_be_duplicated(): void
    {
        $page = Page::factory()->create([
            'title' => 'Landing',
            'slug' => 'landing',
            'schema' => ['blocks' => [['id' => 'a', 'widget' => 'statement', 'settings' => ['text' => 'Hello']]]],
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.pages.duplicate', $page))
            ->assertRedirect();

        $copy = Page::query()->where('slug', 'landing-copy')->firstOrFail();

        $this->assertSame('Landing (copy)', $copy->title);
        $this->assertSame(PageStatus::Draft, $copy->status);
        $this->assertSame('Hello', $copy->schema['blocks'][0]['settings']['text']);
    }

    public function test_a_scheduled_page_needs_a_date(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.pages.store'), [
                'title' => 'Later',
                'status' => PageStatus::Scheduled->value,
            ])
            ->assertSessionHasErrors('scheduled_for');
    }
}
