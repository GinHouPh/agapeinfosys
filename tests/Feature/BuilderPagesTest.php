<?php

namespace Tests\Feature;

use App\Builder\PageRenderer;
use App\Builder\WidgetRegistry;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuilderPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_published_page_is_rendered_at_its_slug(): void
    {
        $page = Page::factory()->published()->create([
            'title' => 'Landing Page',
            'meta_description' => 'A campaign landing page.',
            'schema' => [
                'blocks' => [
                    ['id' => 'a', 'widget' => 'statement', 'settings' => ['accent' => 'Hello', 'text' => 'from the builder.']],
                ],
            ],
        ]);

        $this->get(route('pages.show', ['page' => $page->path]))
            ->assertOk()
            ->assertSee('Hello')
            ->assertSee('from the builder.')
            ->assertSee('A campaign landing page.', false);
    }

    public function test_a_draft_page_returns_404(): void
    {
        $page = Page::factory()->create();

        $this->get(route('pages.show', ['page' => $page->path]))->assertNotFound();
    }

    public function test_the_home_route_falls_back_to_the_hand_built_view_when_no_builder_page_exists(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Software solutions & IT consultancy');
    }

    public function test_a_published_home_page_takes_over_the_home_route(): void
    {
        Page::factory()->published()->create([
            'slug' => 'home',
            'schema' => [
                'blocks' => [
                    ['id' => 'a', 'widget' => 'statement', 'settings' => ['accent' => 'BuilderHome', 'text' => 'is live.']],
                ],
            ],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('BuilderHome')
            ->assertSee('is live.');
    }

    public function test_a_draft_home_page_does_not_take_over_the_home_route(): void
    {
        Page::factory()->create([
            'slug' => 'home',
            'schema' => [
                'blocks' => [
                    ['id' => 'a', 'widget' => 'statement', 'settings' => ['accent' => 'DraftHome', 'text' => 'should not show.']],
                ],
            ],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('DraftHome')
            ->assertSee('Software solutions & IT consultancy');
    }

    public function test_a_published_about_page_takes_over_the_about_route(): void
    {
        Page::factory()->published()->create([
            'slug' => 'about',
            'schema' => [
                'blocks' => [
                    ['id' => 'a', 'widget' => 'statement', 'settings' => ['accent' => 'BuilderAbout', 'text' => 'is live.']],
                ],
            ],
        ]);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('BuilderAbout');
    }

    public function test_a_published_services_page_takes_over_the_services_route(): void
    {
        Page::factory()->published()->create([
            'slug' => 'services',
            'schema' => [
                'blocks' => [
                    ['id' => 'a', 'widget' => 'statement', 'settings' => ['accent' => 'BuilderServices', 'text' => 'is live.']],
                ],
            ],
        ]);

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee('BuilderServices');
    }

    public function test_seeded_flagship_pages_are_drafts_so_the_live_site_is_untouched(): void
    {
        $this->seed();

        $this->assertTrue(Page::where('slug', 'home')->first()?->status->isDraft());
        $this->assertTrue(Page::where('slug', 'about')->first()?->status->isDraft());
        $this->assertTrue(Page::where('slug', 'services')->first()?->status->isDraft());

        // The hand-built views still serve the routes.
        $this->get(route('home'))->assertOk()->assertSee('Software solutions & IT consultancy');
        $this->get(route('about'))->assertOk()->assertSee('About us');
        $this->get(route('services.index'))->assertOk()->assertSee('Capabilities');
    }

    public function test_seeded_flagship_pages_render_when_published(): void
    {
        $this->seed();

        Page::where('slug', 'home')->update(['status' => 'published']);

        $this->get(route('home'))->assertOk()->assertSee('Design.');
    }

    public function test_the_seeded_page_catalogue_covers_every_reserved_slug(): void
    {
        $this->seed();

        $reserved = array_merge(
            array_keys(Page::flagshipRoutes()),
            array_keys(Page::recordTemplates()),
        );

        foreach ($reserved as $slug) {
            $page = Page::query()->where('slug', $slug)->first();

            $this->assertNotNull($page, "No seeded page found for reserved slug [{$slug}].");
            $this->assertTrue($page->status->isDraft(), "Seeded page [{$slug}] must stay a draft.");
        }
    }

    public function test_every_seeded_page_renders_with_the_seeded_content(): void
    {
        $this->seed();

        $renderer = app(PageRenderer::class);

        foreach (Page::query()->get() as $page) {
            $html = $renderer->render($page);

            $this->assertNotSame('', trim($html), "Seeded page [{$page->slug}] rendered empty HTML.");

            foreach ($page->blocks() as $block) {
                $this->assertNotNull(
                    app(WidgetRegistry::class)->get($block['widget']),
                    "Seeded page [{$page->slug}] uses unregistered widget [{$block['widget']}].",
                );
            }
        }
    }

    public function test_the_new_flagship_routes_fall_back_until_their_pages_are_published(): void
    {
        $this->seed();

        $this->get(route('portfolio.index'))->assertOk()->assertSee('that shipped, scaled and stayed shipped.');
        $this->get(route('projects.index'))->assertOk()->assertSee('in the making');
        $this->get(route('blog.index'))->assertOk()->assertSee('our latest articles');
        $this->get(route('contact.create'))->assertOk()->assertSee('with customers to your business');
        $this->get(route('requirements.create'))->assertOk()->assertSee('Tell us what to');
    }

    public function test_published_flagship_pages_take_over_their_routes(): void
    {
        $this->seed();

        $markers = [
            'portfolio' => 'MarkerPortfolio',
            'projects' => 'MarkerProjects',
            'blog' => 'MarkerBlog',
            'contact' => 'MarkerContact',
            'requirements' => 'MarkerRequirements',
        ];

        foreach ($markers as $slug => $marker) {
            Page::query()->where('slug', $slug)->update([
                'status' => 'published',
                'schema' => [
                    'blocks' => [
                        ['id' => 'a-'.$slug, 'widget' => 'statement', 'settings' => ['accent' => $marker, 'text' => 'is live.']],
                    ],
                ],
            ]);
        }

        $this->get(route('portfolio.index'))->assertOk()->assertSee('MarkerPortfolio');
        $this->get(route('projects.index'))->assertOk()->assertSee('MarkerProjects');
        $this->get(route('blog.index'))->assertOk()->assertSee('MarkerBlog');
        $this->get(route('contact.create'))->assertOk()->assertSee('MarkerContact');
        $this->get(route('requirements.create'))->assertOk()->assertSee('MarkerRequirements');
    }

    public function test_a_published_record_template_renders_every_detail_page_with_its_record(): void
    {
        $service = Service::factory()->create([
            'title' => 'Cloud Migration',
            'is_published' => true,
        ]);

        Page::factory()->published()->create([
            'slug' => 'service-detail',
            'schema' => [
                'blocks' => [
                    ['id' => 'a', 'widget' => 'page-hero', 'settings' => ['source' => 'record']],
                    ['id' => 'b', 'widget' => 'statement', 'settings' => ['accent' => 'TemplateMarker', 'text' => 'is live.']],
                ],
            ],
        ]);

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertSee('Cloud Migration')
            ->assertSee('TemplateMarker')
            ->assertSee('Cloud Migration | Agape Infosys Services', false);
    }

    public function test_a_draft_record_template_leaves_detail_pages_on_the_hand_built_view(): void
    {
        $service = Service::factory()->create([
            'title' => 'Cloud Migration',
            'is_published' => true,
        ]);

        Page::factory()->create([
            'slug' => 'service-detail',
            'schema' => [
                'blocks' => [
                    ['id' => 'b', 'widget' => 'statement', 'settings' => ['accent' => 'DraftMarker', 'text' => 'should not show.']],
                ],
            ],
        ]);

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertDontSee('DraftMarker')
            ->assertSee('Cloud Migration');
    }

    public function test_record_widgets_receive_the_record_from_the_detail_route(): void
    {
        $post = Post::factory()->create([
            'title' => 'Shipping Safer Releases',
            'is_published' => true,
            'published_at' => now(),
        ]);

        Page::factory()->published()->create([
            'slug' => 'article-detail',
            'schema' => [
                'blocks' => [
                    ['id' => 'a', 'widget' => 'page-hero', 'settings' => ['source' => 'record']],
                ],
            ],
        ]);

        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertSee('Shipping Safer Releases')
            ->assertSee('Shipping Safer Releases | Agape Infosys Blog', false);
    }
}
