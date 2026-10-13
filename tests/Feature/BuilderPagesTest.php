<?php

namespace Tests\Feature;

use App\Models\Page;
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

        $this->get(route('pages.show', $page))
            ->assertOk()
            ->assertSee('Hello')
            ->assertSee('from the builder.')
            ->assertSee('A campaign landing page.', false);
    }

    public function test_a_draft_page_returns_404(): void
    {
        $page = Page::factory()->create();

        $this->get(route('pages.show', $page))->assertNotFound();
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
}
