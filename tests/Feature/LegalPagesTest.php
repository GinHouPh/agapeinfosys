<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_policy_page_renders(): void
    {
        $markers = [
            route('legal.privacy') => 'Information we collect',
            route('legal.terms') => 'Acceptable use',
            route('legal.refunds') => 'Cancelling a subscription',
            route('legal.delivery') => 'Delivery timelines',
        ];

        foreach ($markers as $uri => $marker) {
            $this->get($uri)
                ->assertOk()
                ->assertSee($marker, false);
        }
    }

    public function test_the_refund_policy_explains_the_cancellation_and_refund_timelines(): void
    {
        $this->get(route('legal.refunds'))
            ->assertOk()
            ->assertSee('7–10 business days', false)
            ->assertSee('14 days', false);
    }

    public function test_the_delivery_policy_explains_digital_access_timelines(): void
    {
        $this->get(route('legal.delivery'))
            ->assertOk()
            ->assertSee('within minutes', false)
            ->assertSee('24 hours', false)
            ->assertSee('My Access', false);
    }

    public function test_policy_pages_link_to_each_other(): void
    {
        $this->get(route('legal.privacy'))
            ->assertOk()
            ->assertSee(route('legal.terms'), false)
            ->assertSee(route('legal.refunds'), false)
            ->assertSee(route('legal.delivery'), false);
    }

    public function test_the_footer_links_to_every_policy_page(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('legal.privacy'), false)
            ->assertSee(route('legal.terms'), false)
            ->assertSee(route('legal.refunds'), false)
            ->assertSee(route('legal.delivery'), false);
    }

    public function test_a_published_builder_page_takes_over_a_policy_route(): void
    {
        Page::factory()->published()->create([
            'slug' => 'privacy-policy',
            'schema' => [
                'blocks' => [
                    ['id' => 'a', 'widget' => 'statement', 'settings' => ['accent' => 'BuilderPrivacy', 'text' => 'is live.']],
                ],
            ],
        ]);

        $this->get(route('legal.privacy'))
            ->assertOk()
            ->assertSee('BuilderPrivacy')
            ->assertDontSee('Information we collect', false);
    }

    public function test_a_draft_policy_page_leaves_the_hand_built_view_in_place(): void
    {
        Page::factory()->create([
            'slug' => 'delivery-policy',
            'schema' => [
                'blocks' => [
                    ['id' => 'a', 'widget' => 'statement', 'settings' => ['accent' => 'DraftDelivery', 'text' => 'should not show.']],
                ],
            ],
        ]);

        $this->get(route('legal.delivery'))
            ->assertOk()
            ->assertDontSee('DraftDelivery', false)
            ->assertSee('Delivery timelines', false);
    }

    public function test_seeded_policy_pages_exist_as_drafts(): void
    {
        $this->seed();

        foreach (['privacy-policy', 'terms-and-conditions', 'cancellation-and-refunds', 'delivery-policy'] as $slug) {
            $page = Page::query()->where('slug', $slug)->first();

            $this->assertNotNull($page, "No seeded page found for policy slug [{$slug}].");
            $this->assertTrue($page->status->isDraft(), "Seeded policy page [{$slug}] must stay a draft.");
        }
    }
}
