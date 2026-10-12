<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Testimonial;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SettingSeeder::class);
        $this->seed(ServiceSeeder::class);
    }

    public function test_homepage_renders_with_core_sections(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Engineer.', false)
            ->assertSee('Selected', false)
            ->assertSee('contact', false);
    }

    public function test_static_pages_render(): void
    {
        foreach (['/about', '/services', '/portfolio', '/blog', '/contact'] as $uri) {
            $this->get($uri)->assertOk();
        }
    }

    public function test_service_detail_page_uses_slug(): void
    {
        $service = Service::factory()->create(['is_published' => true]);

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertSee($service->title, false);
    }

    public function test_draft_services_are_hidden_from_public_listing(): void
    {
        Service::factory()->draft()->create(['title' => 'Hidden Offering']);

        $this->get(route('services.index'))
            ->assertOk()
            ->assertDontSee('Hidden Offering', false);
    }

    public function test_project_detail_page_uses_slug(): void
    {
        $project = Project::factory()->create(['is_published' => true]);

        $this->get(route('portfolio.show', $project))
            ->assertOk()
            ->assertSee($project->title, false);
    }

    public function test_only_published_posts_appear_on_the_blog(): void
    {
        $published = Post::factory()->create(['is_published' => true, 'title' => 'Published Piece']);
        Post::factory()->create(['is_published' => false, 'title' => 'Draft Piece']);

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('Published Piece', false)
            ->assertDontSee('Draft Piece', false);

        $this->get(route('blog.show', $published))->assertOk();
    }

    public function test_homepage_shows_published_testimonials(): void
    {
        Testimonial::factory()->create([
            'quote' => 'The most reliable partner we have worked with.',
            'is_published' => true,
        ]);
        Testimonial::factory()->create([
            'quote' => 'An unpublished quote nobody should see.',
            'is_published' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('The most reliable partner', false)
            ->assertDontSee('An unpublished quote', false);
    }

    public function test_unknown_page_returns_404(): void
    {
        $this->get('/definitely-not-a-page')->assertNotFound();
    }

    public function test_seeded_service_catalog_is_available(): void
    {
        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee('Custom Software Development', false);
    }
}
