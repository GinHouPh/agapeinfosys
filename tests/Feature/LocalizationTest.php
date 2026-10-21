<?php

namespace Tests\Feature;

use App\Models\Locale;
use App\Models\Page;
use App\Models\Translation;
use App\Models\User;
use App\Services\LocaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_a_language_can_be_added(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.locales.store'), [
                'code' => 'de',
                'name' => 'German',
                'native_name' => 'Deutsch',
            ])
            ->assertRedirect(route('admin.locales.index'));

        $this->assertDatabaseHas('locales', ['code' => 'de', 'name' => 'German']);
    }

    public function test_a_language_can_be_made_the_default(): void
    {
        $default = Locale::factory()->default()->create();
        $german = Locale::factory()->create(['code' => 'de']);

        $this->actingAs($this->admin)
            ->post(route('admin.locales.activate', $german))
            ->assertRedirect();

        $this->assertTrue($german->refresh()->is_default);
        $this->assertFalse($default->refresh()->is_default);
    }

    public function test_the_default_language_cannot_be_deleted(): void
    {
        $locale = Locale::factory()->default()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.locales.destroy', $locale))
            ->assertSessionHasErrors('locale');
    }

    public function test_a_visitor_can_choose_a_language(): void
    {
        Locale::factory()->create(['code' => 'de', 'name' => 'German']);

        $this->get(route('locale.choose', 'de'))
            ->assertRedirect()
            ->assertCookie(LocaleService::COOKIE, 'de');
    }

    public function test_a_visitor_cannot_choose_a_language_that_does_not_exist(): void
    {
        $this->get(route('locale.choose', 'xx'))
            ->assertRedirect()
            ->assertCookieMissing(LocaleService::COOKIE);
    }

    public function test_a_translation_can_be_saved_for_a_record(): void
    {
        $locale = Locale::factory()->create(['code' => 'de']);
        $page = Page::factory()->create(['title' => 'About us']);

        $this->actingAs($this->admin)
            ->put(route('admin.locales.translations.update', [
                'type' => 'page',
                'id' => $page->id,
                'locale' => 'de',
            ]), [
                'values' => ['title' => 'Über uns'],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('translations', [
            'locale_id' => $locale->id,
            'translatable_id' => $page->id,
            'field' => 'title',
            'value' => 'Über uns',
        ]);
    }

    public function test_a_translation_is_used_when_the_page_is_rendered(): void
    {
        $locale = Locale::factory()->create(['code' => 'de']);
        $page = Page::factory()->published()->create([
            'title' => 'About us',
            'schema' => [
                'blocks' => [
                    [
                        'id' => 'abc123',
                        'widget' => 'statement',
                        'settings' => ['text' => 'Built to last.'],
                    ],
                ],
            ],
        ]);

        Translation::query()->create([
            'locale_id' => $locale->id,
            'translatable_type' => Page::class,
            'translatable_id' => $page->id,
            'field' => 'block.abc123.text',
            'value' => 'Auf Dauer gebaut.',
        ]);

        $this->withCookie(LocaleService::COOKIE, 'de')
            ->get('/pages/'.$page->path)
            ->assertOk()
            ->assertSee('Auf Dauer gebaut.', false)
            ->assertDontSee('Built to last.', false);
    }

    public function test_a_page_falls_back_to_its_own_text_without_a_translation(): void
    {
        Locale::factory()->create(['code' => 'de']);
        $page = Page::factory()->published()->create([
            'schema' => [
                'blocks' => [
                    [
                        'id' => 'abc123',
                        'widget' => 'statement',
                        'settings' => ['text' => 'Built to last.'],
                    ],
                ],
            ],
        ]);

        $this->withCookie(LocaleService::COOKIE, 'de')
            ->get('/pages/'.$page->path)
            ->assertOk()
            ->assertSee('Built to last.', false);
    }

    public function test_the_translation_editor_lists_the_fields_of_a_record(): void
    {
        Locale::factory()->create(['code' => 'de']);
        $page = Page::factory()->create([
            'title' => 'About us',
            'schema' => [
                'blocks' => [
                    [
                        'id' => 'abc123',
                        'widget' => 'statement',
                        'settings' => ['text' => 'Built to last.'],
                    ],
                ],
            ],
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.locales.edit', ['type' => 'page', 'id' => $page->id, 'locale' => 'de']))
            ->assertOk()
            ->assertSee('About us', false)
            ->assertSee('block.abc123.text', false);
    }

    public function test_the_browser_preference_is_used_when_nothing_was_chosen(): void
    {
        Locale::factory()->create(['code' => 'de']);

        $this->withHeader('Accept-Language', 'de-DE,de;q=0.9,en;q=0.8')
            ->get('/')
            ->assertOk();

        $this->assertSame('de', app(LocaleService::class)->currentCode());
    }
}
