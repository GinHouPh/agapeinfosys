<?php

namespace Tests\Feature\Dashboard;

use App\Mail\LicenseIssuedMail;
use App\Models\DomainBinding;
use App\Models\License;
use App\Models\SoftwareProduct;
use App\Models\SoftwareRelease;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardLicensingTest extends TestCase
{
    use RefreshDatabase;

    private function client(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'email' => 'client@agencyhub.com',
        ], $attributes));
    }

    public function test_guest_cannot_access_client_licenses(): void
    {
        $this->get(route('dashboard.licenses.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_client_can_view_owned_licenses(): void
    {
        $client = $this->client();
        $otherClient = User::factory()->create(['email' => 'other@company.com']);

        $product = SoftwareProduct::create([
            'name' => 'SRS Genie',
            'slug' => 'srs-genie',
            'is_active' => true,
        ]);

        $ownedLicense = License::create([
            'product_id' => $product->id,
            'license_key' => 'AGAPE-SRS-MINE-0001-TEST',
            'channel' => 'agape_direct',
            'buyer_email' => $client->email,
            'buyer_username' => 'Agency Hub',
            'tier' => 'agency',
            'max_domains' => 5,
            'status' => 'active',
            'client_secret_salt' => Str::random(32),
        ]);

        $otherLicense = License::create([
            'product_id' => $product->id,
            'license_key' => 'AGAPE-SRS-OTHER-0002-TEST',
            'channel' => 'agape_direct',
            'buyer_email' => $otherClient->email,
            'tier' => 'regular',
            'status' => 'active',
            'client_secret_salt' => Str::random(32),
        ]);

        $response = $this->actingAs($client)->get(route('dashboard.licenses.index'));

        $response->assertOk()
            ->assertSee('AGAPE-SRS-MINE-0001-TEST')
            ->assertSee('SRS Genie')
            ->assertDontSee('AGAPE-SRS-OTHER-0002-TEST');
    }

    public function test_client_can_self_service_unbind_domain(): void
    {
        $client = $this->client();
        $product = SoftwareProduct::create([
            'name' => 'SRS Genie',
            'slug' => 'srs-genie',
            'is_active' => true,
        ]);

        $license = License::create([
            'product_id' => $product->id,
            'license_key' => 'AGAPE-SRS-UNBD-0003-TEST',
            'channel' => 'agape_direct',
            'buyer_email' => $client->email,
            'tier' => 'regular',
            'max_domains' => 1,
            'status' => 'active',
            'client_secret_salt' => Str::random(32),
        ]);

        $binding = DomainBinding::create([
            'license_id' => $license->id,
            'domain' => 'oldhost.agency.com',
            'status' => 'active',
            'activated_at' => now(),
        ]);

        $response = $this->actingAs($client)
            ->post(route('dashboard.licenses.unbind', [$license, $binding]));

        $response->assertRedirect();
        $binding->refresh();
        $this->assertEquals('deactivated', $binding->status);

        $this->assertDatabaseHas('license_audit_logs', [
            'license_id' => $license->id,
            'event' => 'client_self_service_deactivation',
            'domain' => 'oldhost.agency.com',
        ]);
    }

    public function test_client_cannot_unbind_another_users_license_domain(): void
    {
        $client = $this->client();
        $otherUser = User::factory()->create(['email' => 'victim@victim.com']);

        $product = SoftwareProduct::create([
            'name' => 'SRS Genie',
            'slug' => 'srs-genie',
            'is_active' => true,
        ]);

        $otherLicense = License::create([
            'product_id' => $product->id,
            'license_key' => 'AGAPE-SRS-VICT-0004-TEST',
            'channel' => 'agape_direct',
            'buyer_email' => $otherUser->email,
            'tier' => 'regular',
            'status' => 'active',
            'client_secret_salt' => Str::random(32),
        ]);

        $binding = DomainBinding::create([
            'license_id' => $otherLicense->id,
            'domain' => 'victim-site.com',
            'status' => 'active',
            'activated_at' => now(),
        ]);

        $this->actingAs($client)
            ->post(route('dashboard.licenses.unbind', [$otherLicense, $binding]))
            ->assertForbidden();
    }

    public function test_client_can_download_latest_authorized_release(): void
    {
        Storage::fake('local');
        $client = $this->client();

        $product = SoftwareProduct::create([
            'name' => 'SRS Genie',
            'slug' => 'srs-genie',
            'is_active' => true,
        ]);

        $license = License::create([
            'product_id' => $product->id,
            'license_key' => 'AGAPE-SRS-DOWN-0005-TEST',
            'channel' => 'agape_direct',
            'buyer_email' => $client->email,
            'tier' => 'regular',
            'status' => 'active',
            'client_secret_salt' => Str::random(32),
        ]);

        $content = 'PK...Release Archive Content For Client';
        Storage::disk('local')->put('releases/srs-genie/srs-genie-v2.5.0.zip', $content);

        SoftwareRelease::create([
            'product_id' => $product->id,
            'version' => '2.5.0',
            'min_php_version' => '8.2.0',
            'changelog' => 'Production release',
            'file_path' => 'releases/srs-genie/srs-genie-v2.5.0.zip',
            'file_size_bytes' => strlen($content),
            'sha256_checksum' => hash('sha256', $content),
            'is_active' => true,
        ]);

        $response = $this->actingAs($client)
            ->get(route('dashboard.licenses.download', $license));

        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=srs-genie-v2.5.0.zip');
    }

    public function test_license_issued_mailable_content(): void
    {
        $product = SoftwareProduct::create([
            'name' => 'SRS Genie',
            'slug' => 'srs-genie',
            'is_active' => true,
        ]);

        $license = License::create([
            'product_id' => $product->id,
            'license_key' => 'AGAPE-SRS-MAIL-0006-TEST',
            'channel' => 'agape_direct',
            'buyer_email' => 'buyer@company.com',
            'buyer_username' => 'John Buyer',
            'tier' => 'agency',
            'max_domains' => 5,
            'status' => 'active',
            'client_secret_salt' => Str::random(32),
        ]);

        $mailable = new LicenseIssuedMail($license);
        $mailable->assertSeeInHtml('AGAPE-SRS-MAIL-0006-TEST');
        $mailable->assertSeeInHtml('SRS Genie');
        $mailable->assertSeeInHtml('agency');
        $mailable->assertSeeInHtml('5');
    }

    public function test_cashfree_webhook_queues_license_issued_email(): void
    {
        Mail::fake();

        SoftwareProduct::create([
            'name' => 'SRS Genie',
            'slug' => 'srs-genie',
            'is_active' => true,
        ]);

        $payload = [
            'data' => [
                'order' => [
                    'order_id' => 'order_cf_99',
                    'order_tags' => [
                        'product_slug' => 'srs-genie',
                        'tier' => 'extended',
                    ],
                ],
                'payment' => [
                    'payment_status' => 'SUCCESS',
                ],
                'customer_details' => [
                    'customer_email' => 'newbuyer@codecanyon.net',
                ],
            ],
        ];

        $response = $this->postJson(route('api.license.webhooks.cashfree'), $payload);
        $response->assertStatus(200);

        Mail::assertQueued(LicenseIssuedMail::class, function ($mail) {
            return $mail->hasTo('newbuyer@codecanyon.net');
        });
    }
}
