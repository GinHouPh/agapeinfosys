<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\SeoMeta;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SettingSeeder::class);

        $this->admin = User::factory()->admin()->create();
    }

    public function test_the_sitemap_is_served_as_xml(): void
    {
        $page = Page::factory()->published()->create(['slug' => 'home']);

        $response = $this->get(route('sitemap.xml'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset', false)
            ->assertSee($page->targetUrl(), false);
    }

    public function test_the_sitemap_leaves_out_draft_pages(): void
    {
        $draft = Page::factory()->create(['slug' => 'secret-draft']);

        $this->get(route('sitemap.xml'))
            ->assertOk()
            ->assertDontSee('/pages/'.$draft->slug, false);
    }

    public function test_a_page_marked_noindex_is_left_out_of_the_sitemap(): void
    {
        $page = Page::factory()->published()->create(['slug' => 'hidden']);

        SeoMeta::query()->create([
            'metaable_type' => Page::class,
            'metaable_id' => $page->id,
            'noindex' => true,
        ]);

        $this->get(route('sitemap.xml'))
            ->assertOk()
            ->assertDontSee('/pages/hidden', false);
    }

    public function test_robots_points_at_the_sitemap(): void
    {
        $this->get(route('robots.txt'))
            ->assertOk()
            ->assertSee('Sitemap: '.route('sitemap.xml'), false)
            ->assertSee('Disallow: /admin', false);
    }

    public function test_the_seo_panel_renders_for_a_record(): void
    {
        $page = Page::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.seo.edit', ['type' => 'page', 'id' => $page->id]))
            ->assertOk()
            ->assertSee('Search results', false);
    }

    public function test_metadata_can_be_saved_for_a_record(): void
    {
        $page = Page::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.seo.update', ['type' => 'page', 'id' => $page->id]), [
                'meta_title' => 'A better title',
                'meta_description' => 'A better description.',
                'noindex' => '1',
            ])
            ->assertRedirect(route('admin.seo.edit', ['type' => 'page', 'id' => $page->id]));

        $meta = $page->fresh()->seoMeta()->firstOrFail();

        $this->assertSame('A better title', $meta->meta_title);
        $this->assertTrue($meta->noindex);
    }

    public function test_an_unknown_record_type_is_a_404(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.seo.edit', ['type' => 'unicorn', 'id' => 1]))
            ->assertNotFound();
    }

    public function test_the_saved_title_reaches_the_page_head(): void
    {
        $service = Service::factory()->create(['is_published' => true]);

        SeoMeta::query()->create([
            'metaable_type' => Service::class,
            'metaable_id' => $service->id,
            'meta_title' => 'Cloud migration, done properly',
            'og_image' => '/uploads/pages/cloud.jpg',
        ]);

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertSee('Cloud migration, done properly', false)
            ->assertSee('/uploads/pages/cloud.jpg', false)
            ->assertSee('twitter:card', false);
    }

    public function test_a_record_falls_back_to_its_own_title(): void
    {
        $post = Post::factory()->create(['is_published' => true]);

        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertSee($post->title, false);
    }

    public function test_structured_data_is_emitted_for_a_record(): void
    {
        $post = Post::factory()->create(['is_published' => true]);

        $response = $this->get(route('blog.show', $post));

        $response->assertOk()
            ->assertSee('application/ld+json', false)
            ->assertSee('Article', false)
            ->assertSee('WebSite', false)
            ->assertSee('Organization', false);
    }

    public function test_a_redirect_sends_the_visitor_to_its_replacement(): void
    {
        Redirect::query()->create([
            'from_path' => '/old-page',
            'to_path' => '/new-page',
            'status_code' => 301,
        ]);

        $this->get('/old-page')
            ->assertStatus(301)
            ->assertRedirect('/new-page');
    }

    public function test_an_inactive_redirect_does_not_fire(): void
    {
        Redirect::query()->create([
            'from_path' => '/old-page',
            'to_path' => '/new-page',
            'is_active' => false,
        ]);

        $this->get('/old-page')->assertNotFound();
    }

    public function test_a_redirect_counts_its_hits(): void
    {
        $redirect = Redirect::query()->create([
            'from_path' => '/old-page',
            'to_path' => '/new-page',
        ]);

        $this->get('/old-page');

        $this->assertSame(1, $redirect->refresh()->hits);
    }

    public function test_a_redirect_can_be_created_and_deleted_from_the_admin(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.seo.redirects.store'), [
                'from_path' => '/legacy',
                'to_path' => '/current',
                'status_code' => 301,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.seo.redirects'));

        $redirect = Redirect::query()->where('from_path', '/legacy')->firstOrFail();

        $this->actingAs($this->admin)
            ->delete(route('admin.seo.redirects.destroy', $redirect))
            ->assertRedirect(route('admin.seo.redirects'));

        $this->assertDatabaseMissing('redirects', ['id' => $redirect->id]);
    }

    public function test_a_redirect_needs_both_paths(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.seo.redirects.store'), [
                'from_path' => 'no-leading-slash',
                'to_path' => '/current',
                'status_code' => 301,
            ])
            ->assertSessionHasErrors('from_path');
    }
}
