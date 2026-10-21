<?php

namespace Tests\Feature;

use App\Models\Locale;
use App\Models\Page;
use App\Models\Translation;
use App\Models\User;
use App\Services\LocaleService;
use App\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepeaterTranslationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    /**
     * A page with one section holding a cta-banner's buttons.
     */
    private function pageWithButtons(): Page
    {
        return Page::factory()->published()->create([
            'schema' => [
                'blocks' => [
                    [
                        'id' => 'sec1',
                        'widget' => 'cta-banner',
                        'settings' => [
                            'title' => 'Ready?',
                            'buttons' => [
                                ['id' => 'row1', 'label' => 'Talk to us', 'url' => '/contact', 'variant' => 'accent'],
                                ['id' => 'row2', 'label' => 'See the work', 'url' => '/portfolio', 'variant' => 'accent'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function test_a_repeater_row_is_offered_for_translation(): void
    {
        $page = $this->pageWithButtons();

        $fields = app(TranslationService::class)->fields($page);

        $this->assertArrayHasKey('block.sec1.buttons.row1.label', $fields);
        $this->assertArrayHasKey('block.sec1.buttons.row2.label', $fields);
        $this->assertArrayNotHasKey('block.sec1.buttons.row1.url', $fields);
    }

    public function test_a_repeater_row_is_translated_when_the_page_renders(): void
    {
        $page = $this->pageWithButtons();
        $locale = Locale::factory()->create(['code' => 'de']);

        Translation::query()->create([
            'locale_id' => $locale->id,
            'translatable_type' => Page::class,
            'translatable_id' => $page->id,
            'field' => 'block.sec1.buttons.row1.label',
            'value' => 'Sprechen Sie uns an',
        ]);

        $this->withCookie(LocaleService::COOKIE, 'de')
            ->get('/pages/'.$page->path)
            ->assertOk()
            ->assertSee('Sprechen Sie uns an', false)
            ->assertSee('See the work', false);
    }

    public function test_a_repeater_translation_follows_its_row_when_the_list_is_reordered(): void
    {
        $page = $this->pageWithButtons();
        $locale = Locale::factory()->create(['code' => 'de']);

        Translation::query()->create([
            'locale_id' => $locale->id,
            'translatable_type' => Page::class,
            'translatable_id' => $page->id,
            'field' => 'block.sec1.buttons.row1.label',
            'value' => 'Sprechen Sie uns an',
        ]);

        // The rows swap places; the translation must follow row1, not the
        // first position.
        $blocks = $page->schema['blocks'];
        $buttons = $blocks[0]['settings']['buttons'];
        $blocks[0]['settings']['buttons'] = [$buttons[1], $buttons[0]];
        $page->update(['schema' => ['blocks' => $blocks]]);

        $rendered = $this->withCookie(LocaleService::COOKIE, 'de')
            ->get('/pages/'.$page->path)
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Sprechen Sie uns an', $rendered);
        $this->assertStringContainsString('See the work', $rendered);
    }

    public function test_the_editor_shows_the_original_value_of_a_repeater_row(): void
    {
        $page = $this->pageWithButtons();
        Locale::factory()->create(['code' => 'de']);

        $this->actingAs($this->admin)
            ->get(route('admin.locales.edit', ['type' => 'page', 'id' => $page->id, 'locale' => 'de']))
            ->assertOk()
            ->assertSee('block.sec1.buttons.row1.label', false)
            ->assertSee('Talk to us', false);
    }
}
