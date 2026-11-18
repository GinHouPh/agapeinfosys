<?php

namespace Tests\Feature\Licensing;

use App\Models\DomainBinding;
use App\Models\License;
use App\Models\SoftwareProduct;
use App\Models\SoftwareRelease;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class LicenseApiTest extends TestCase
{
    use RefreshDatabase;

    private SoftwareProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = SoftwareProduct::create([
            'name' => 'SRS Genie',
            'slug' => 'srs-genie',
            'envato_item_id' => '55234981',
            'is_active' => true,
        ]);
    }

    public function test_cannot_verify_with_nonexistent_product(): void
    {
        $response = $this->postJson(route('api.license.verify'), [
            'license_key' => 'AGAPE-SRS-TEST-KEY1-1111',
            'domain' => 'clientdomain.com',
            'product_slug' => 'nonexistent-software',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'valid' => false,
                'message' => 'Product not recognized or inactive.',
            ]);
    }

    public function test_envato_purchase_code_verification_success(): void
    {
        $purchaseCode = 'c2f8e120-410a-4b95-a249-1663b6528d9a';

        Http::fake([
            'https://api.envato.com/v3/market/author/sale*' => Http::response([
                'buyer' => 'john_doe',
                'item' => [
                    'id' => '55234981',
                    'name' => 'SRS Genie',
                ],
                'license' => 'Regular License',
                'supported_until' => now()->addMonths(6)->toIso8601String(),
            ], 200),
        ]);

        $response = $this->postJson(route('api.license.verify'), [
            'license_key' => $purchaseCode,
            'domain' => 'https://www.customer-portal.com:443/app',
            'product_slug' => 'srs-genie',
            'installed_version' => '2.5.0',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'valid' => true,
                'buyer' => 'john_doe',
                'tier' => 'regular',
                'channel' => 'envato',
            ])
            ->assertJsonStructure([
                'valid',
                'message',
                'buyer',
                'tier',
                'channel',
                'activation_hash',
                'client_salt',
                'features',
            ]);

        $this->assertDatabaseHas('licenses', [
            'license_key' => $purchaseCode,
            'channel' => 'envato',
            'buyer_username' => 'john_doe',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('domain_bindings', [
            'domain' => 'customer-portal.com',
            'status' => 'active',
        ]);
    }

    public function test_envato_purchase_code_verification_failure(): void
    {
        $purchaseCode = '11111111-2222-3333-4444-555555555555';

        Http::fake([
            'https://api.envato.com/v3/market/author/sale*' => Http::response([
                'error' => 404,
                'description' => 'Sale not found',
            ], 404),
        ]);

        $response = $this->postJson(route('api.license.verify'), [
            'license_key' => $purchaseCode,
            'domain' => 'clientdomain.com',
            'product_slug' => 'srs-genie',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'valid' => false,
                'message' => 'Invalid Envato Purchase Code for this item.',
            ]);
    }

    public function test_agape_direct_license_verification_success(): void
    {
        $license = License::create([
            'product_id' => $this->product->id,
            'license_key' => 'AGAPE-SRS-9K2M-7B3X-L8Q1',
            'channel' => 'agape_direct',
            'buyer_email' => 'buyer@agency.com',
            'buyer_username' => 'Agency Buyer',
            'tier' => 'agency',
            'max_domains' => 5,
            'status' => 'active',
            'client_secret_salt' => Str::random(32),
        ]);

        $response = $this->postJson(route('api.license.verify'), [
            'license_key' => $license->license_key,
            'domain' => 'agencyportal.com',
            'product_slug' => 'srs-genie',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'valid' => true,
                'buyer' => 'Agency Buyer',
                'tier' => 'agency',
                'channel' => 'agape_direct',
            ]);

        $this->assertDatabaseHas('domain_bindings', [
            'license_id' => $license->id,
            'domain' => 'agencyportal.com',
            'status' => 'active',
        ]);
    }

    public function test_localhost_and_staging_bypass_domain_limits(): void
    {
        $license = License::create([
            'product_id' => $this->product->id,
            'license_key' => 'AGAPE-SRS-1111-2222-3333',
            'channel' => 'agape_direct',
            'buyer_email' => 'dev@test.com',
            'tier' => 'regular',
            'max_domains' => 1,
            'status' => 'active',
            'client_secret_salt' => Str::random(32),
        ]);

        // 1. Verify localhost: should succeed without creating domain binding
        $resLocal = $this->postJson(route('api.license.verify'), [
            'license_key' => $license->license_key,
            'domain' => 'http://localhost:8080/setup',
            'product_slug' => 'srs-genie',
        ]);
        $resLocal->assertStatus(200)->assertJson(['valid' => true]);

        // 2. Verify staging: should also succeed
        $resStaging = $this->postJson(route('api.license.verify'), [
            'license_key' => $license->license_key,
            'domain' => 'staging.myclientsite.test',
            'product_slug' => 'srs-genie',
        ]);
        $resStaging->assertStatus(200)->assertJson(['valid' => true]);

        // Active bindings count should still be 0
        $this->assertEquals(0, $license->activeBindings()->count());
    }

    public function test_domain_limit_enforced_for_standard_license(): void
    {
        $license = License::create([
            'product_id' => $this->product->id,
            'license_key' => 'AGAPE-SRS-AAAA-BBBB-CCCC',
            'channel' => 'agape_direct',
            'buyer_email' => 'single@domain.com',
            'tier' => 'regular',
            'max_domains' => 1,
            'status' => 'active',
            'client_secret_salt' => Str::random(32),
        ]);

        // First domain: succeeds
        $res1 = $this->postJson(route('api.license.verify'), [
            'license_key' => $license->license_key,
            'domain' => 'production-site-one.com',
            'product_slug' => 'srs-genie',
        ]);
        $res1->assertStatus(200)->assertJson(['valid' => true]);

        // Second domain: fails with 403 domain limit reached
        $res2 = $this->postJson(route('api.license.verify'), [
            'license_key' => $license->license_key,
            'domain' => 'production-site-two.com',
            'product_slug' => 'srs-genie',
        ]);
        $res2->assertStatus(403)
            ->assertJson([
                'valid' => false,
                'message' => 'License domain limit reached (1). Deactivate an existing domain first.',
            ]);
    }

    public function test_heartbeat_updates_timestamp_and_version(): void
    {
        $license = License::create([
            'product_id' => $this->product->id,
            'license_key' => 'AGAPE-SRS-BEAT-BEAT-BEAT',
            'channel' => 'agape_direct',
            'tier' => 'regular',
            'status' => 'active',
            'client_secret_salt' => Str::random(32),
        ]);

        $binding = DomainBinding::create([
            'license_id' => $license->id,
            'domain' => 'liveapp.com',
            'installed_version' => '2.5.0',
            'status' => 'active',
            'activated_at' => now()->subDays(5),
            'last_heartbeat_at' => now()->subDays(5),
        ]);

        $response = $this->postJson(route('api.license.heartbeat'), [
            'license_key' => $license->license_key,
            'domain' => 'https://liveapp.com',
            'version' => '2.5.1',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'valid' => true,
                'status' => 'active',
            ]);

        $binding->refresh();
        $this->assertEquals('2.5.1', $binding->installed_version);
        $this->assertTrue($binding->last_heartbeat_at->isAfter(now()->subMinute()));
    }

    public function test_deactivate_unbounds_domain(): void
    {
        $license = License::create([
            'product_id' => $this->product->id,
            'license_key' => 'AGAPE-SRS-DEAC-TIVE-KEY1',
            'channel' => 'agape_direct',
            'tier' => 'regular',
            'max_domains' => 1,
            'status' => 'active',
            'client_secret_salt' => Str::random(32),
        ]);

        DomainBinding::create([
            'license_id' => $license->id,
            'domain' => 'oldhost.com',
            'status' => 'active',
            'activated_at' => now(),
        ]);

        $this->assertEquals(1, $license->activeBindings()->count());

        $response = $this->postJson(route('api.license.deactivate'), [
            'license_key' => $license->license_key,
            'domain' => 'oldhost.com',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals(0, $license->activeBindings()->count());

        // Now activating on newhost.com should succeed!
        $resNew = $this->postJson(route('api.license.verify'), [
            'license_key' => $license->license_key,
            'domain' => 'newhost.com',
            'product_slug' => 'srs-genie',
        ]);

        $resNew->assertStatus(200)->assertJson(['valid' => true]);
    }

    public function test_check_update_returns_latest_release_when_available(): void
    {
        SoftwareRelease::create([
            'product_id' => $this->product->id,
            'version' => '2.6.0',
            'min_php_version' => '8.2.0',
            'changelog' => 'New AI agents added',
            'file_path' => 'releases/srs-genie-v2.6.0.zip',
            'sha256_checksum' => 'hash123',
            'is_active' => true,
        ]);

        License::create([
            'product_id' => $this->product->id,
            'license_key' => 'AGAPE-SRS-ANY-KEY-0000',
            'channel' => 'agape_direct',
            'tier' => 'regular',
            'status' => 'active',
            'client_secret_salt' => Str::random(32),
        ]);

        $response = $this->getJson(route('api.license.check-update', [
            'product' => 'srs-genie',
            'current_version' => '2.5.0',
            'license_key' => 'AGAPE-SRS-ANY-KEY-0000',
        ]));

        $response->assertStatus(200)
            ->assertJson([
                'update_available' => true,
                'current_version' => '2.5.0',
                'latest_version' => '2.6.0',
                'changelog' => 'New AI agents added',
                'sha256' => 'hash123',
            ]);

        $this->assertNotNull($response->json('download_url'));
    }

    public function test_download_update_serves_zip_file_with_valid_license(): void
    {
        Storage::fake('local');

        $license = License::create([
            'product_id' => $this->product->id,
            'license_key' => 'AGAPE-SRS-DOWN-LOAD-KEY1',
            'channel' => 'agape_direct',
            'tier' => 'regular',
            'status' => 'active',
            'client_secret_salt' => Str::random(32),
        ]);

        $zipContent = 'PK...dummy zip content for testing';
        Storage::disk('local')->put('releases/srs-genie-v2.6.0.zip', $zipContent);

        $release = SoftwareRelease::create([
            'product_id' => $this->product->id,
            'version' => '2.6.0',
            'min_php_version' => '8.2.0',
            'changelog' => 'Release 2.6.0',
            'file_path' => 'releases/srs-genie-v2.6.0.zip',
            'sha256_checksum' => hash('sha256', $zipContent),
            'is_active' => true,
        ]);

        $downloadUrl = URL::temporarySignedRoute('api.license.download', now()->addHour(), [
            'release_id' => $release->id,
            'license_key' => $license->license_key,
        ]);

        $response = $this->get($downloadUrl);
        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=srs-genie-v2.6.0.zip');
    }

    public function test_cashfree_webhook_creates_agape_license(): void
    {
        $payload = [
            'data' => [
                'order' => [
                    'order_id' => 'order_12345',
                    'order_tags' => [
                        'product_slug' => 'srs-genie',
                        'tier' => 'agency',
                    ],
                ],
                'payment' => [
                    'payment_status' => 'SUCCESS',
                ],
                'customer_details' => [
                    'customer_email' => 'client@agencyhub.com',
                ],
            ],
        ];

        $response = $this->postJson(route('api.license.webhooks.cashfree'), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'handled',
            ]);

        $licenseKey = $response->json('license_key');
        $this->assertStringStartsWith('AGAPE-SRS-', $licenseKey);

        $this->assertDatabaseHas('licenses', [
            'license_key' => $licenseKey,
            'buyer_email' => 'client@agencyhub.com',
            'tier' => 'agency',
            'max_domains' => 5,
            'status' => 'active',
        ]);
    }
}
