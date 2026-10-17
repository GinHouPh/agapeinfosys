<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BusinessSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SettingSeeder::class);
        $this->admin = User::factory()->admin()->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'brand_name' => 'Agape Digital',
            'legal_name' => 'Agape Digital Labs LLP',
            'tagline' => 'Build. Ship. Scale.',
            'founded_year' => '2016',
            'industry' => 'Software Products',
            'registration_number' => 'AAE-1234',
            'tax_number' => '29AABCA0000A1Z5',
            'website' => 'https://agape.example.com',
            'support_hours' => 'Mon – Sat, 10:00 – 19:00 IST',
            'contact_email' => 'sales@agape.example.com',
            'contact_phone' => '+91 90000 00000',
            'office_address' => 'Tower B, Example Park',
            'mailing_address' => 'PO Box 99, Example City',
            'socials' => [
                'linkedin' => 'https://www.linkedin.com/company/agape',
                'twitter' => '',
                'facebook' => '',
                'instagram' => '',
                'youtube' => '',
            ],
        ];
    }

    public function test_guests_are_redirected_from_the_business_settings(): void
    {
        $this->get(route('admin.business.edit'))->assertRedirect(route('admin.login'));
    }

    public function test_the_settings_page_shows_current_values(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.business.edit'))
            ->assertOk()
            ->assertSee('Agape Infosys Private Limited')
            ->assertSee('hello@agapeinfosys.com');
    }

    public function test_business_details_can_be_saved(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.business.update'), $this->validPayload())
            ->assertRedirect(route('admin.business.edit'));

        $this->assertSame('sales@agape.example.com', Setting::value('contact_email'));
        $this->assertSame('Tower B, Example Park', Setting::value('office_address'));

        $business = Setting::structured('business');

        $this->assertSame('Agape Digital', $business['brand_name']);
        $this->assertSame('Agape Digital Labs LLP', $business['legal_name']);
        $this->assertSame('29AABCA0000A1Z5', $business['tax_number']);
        $this->assertSame('https://www.linkedin.com/company/agape', $business['socials']['linkedin']);
    }

    public function test_saved_details_flow_to_the_frontend(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.business.update'), $this->validPayload());

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('sales@agape.example.com')
            ->assertSee('Agape Digital. All Rights Reserved.')
            ->assertSee('linkedin.com/company/agape');

        $this->get(route('contact.create'))
            ->assertOk()
            ->assertSee('Agape Digital Labs LLP')
            ->assertSee('29AABCA0000A1Z5')
            ->assertSee('AAE-1234')
            ->assertSee('Mon – Sat, 10:00 – 19:00 IST');
    }

    public function test_invalid_values_are_rejected(): void
    {
        $payload = array_merge($this->validPayload(), [
            'contact_email' => 'not-an-email',
            'socials' => array_merge($this->validPayload()['socials'], ['linkedin' => 'www.linkedin.com/no-scheme']),
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.business.update'), $payload)
            ->assertSessionHasErrors(['contact_email', 'socials.linkedin']);

        $this->assertSame('hello@agapeinfosys.com', Setting::value('contact_email'));
    }

    public function test_the_built_in_wordmark_renders_by_default(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('brand--dark', false)
            ->assertSee('Agape<span class="text-accent">Infosys</span>', false);
    }

    public function test_a_full_logo_image_can_be_uploaded_for_a_theme(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)
            ->put(route('admin.business.update'), array_merge($this->validPayload(), [
                'logos' => [
                    'dark' => ['mode' => 'full', 'full' => UploadedFile::fake()->image('dark-logo.png')],
                ],
            ]))
            ->assertRedirect(route('admin.business.edit'));

        $logos = Setting::structured('brand_logos');

        $this->assertSame('full', $logos['dark']['mode']);
        $this->assertNull($logos['light']['full']);
        Storage::disk('public')->assertExists($logos['dark']['full']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($logos['dark']['full'], false);
    }

    public function test_a_square_icon_renders_beside_the_brand_name(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)
            ->put(route('admin.business.update'), array_merge($this->validPayload(), [
                'logos' => [
                    'dark' => ['mode' => 'icon', 'icon' => UploadedFile::fake()->image('mark.png')],
                ],
            ]))
            ->assertRedirect(route('admin.business.edit'));

        $logos = Setting::structured('brand_logos');

        $this->assertSame('icon', $logos['dark']['mode']);
        Storage::disk('public')->assertExists($logos['dark']['icon']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($logos['dark']['icon'], false);
    }

    public function test_a_replaced_logo_image_is_pruned(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)
            ->put(route('admin.business.update'), array_merge($this->validPayload(), [
                'logos' => [
                    'dark' => ['mode' => 'full', 'full' => UploadedFile::fake()->image('first.png')],
                ],
            ]));

        $first = Setting::structured('brand_logos')['dark']['full'];

        $this->actingAs($this->admin)
            ->put(route('admin.business.update'), array_merge($this->validPayload(), [
                'logos' => [
                    'dark' => ['mode' => 'full', 'full' => UploadedFile::fake()->image('second.png')],
                ],
            ]));

        $second = Setting::structured('brand_logos')['dark']['full'];

        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);
    }

    public function test_an_invalid_logo_mode_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.business.update'), array_merge($this->validPayload(), [
                'logos' => ['dark' => ['mode' => 'banner']],
            ]))
            ->assertSessionHasErrors('logos.dark.mode');
    }
}
