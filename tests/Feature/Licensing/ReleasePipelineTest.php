<?php

namespace Tests\Feature\Licensing;

use App\Models\License;
use App\Models\SoftwareProduct;
use App\Models\SoftwareRelease;
use App\Models\User;
use App\Services\Licensing\ReleaseManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReleasePipelineTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_release_upload_stores_archive_and_computes_sha256(): void
    {
        Storage::fake('local');
        $admin = $this->admin();

        $product = SoftwareProduct::create([
            'name' => 'SRS Genie',
            'slug' => 'srs-genie',
            'is_active' => true,
        ]);

        $zipPayload = 'PK...Production-Software-Payload-Content';
        $file = UploadedFile::fake()->createWithContent('srs-genie-v2.6.0.zip', $zipPayload);

        $response = $this->actingAs($admin)->post(route('admin.licensing.releases.store'), [
            'product_id' => $product->id,
            'version' => '2.6.0',
            'min_php_version' => '8.2.0',
            'changelog' => '### v2.6.0\n- Enterprise multi-agent updates',
            'release_file' => $file,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.licensing.releases.index'));

        $release = SoftwareRelease::where('version', '2.6.0')->firstOrFail();

        $this->assertEquals(hash('sha256', $zipPayload), $release->sha256_checksum);
        $this->assertTrue(Storage::disk('local')->exists($release->file_path));
    }

    public function test_release_integrity_check_verifies_checksum_match(): void
    {
        Storage::fake('local');
        $admin = $this->admin();
        $releaseManager = app(ReleaseManagerService::class);

        $product = SoftwareProduct::create([
            'name' => 'SRS Genie',
            'slug' => 'srs-genie',
            'is_active' => true,
        ]);

        $content = 'PK...Sample Valid File Archive';
        Storage::disk('local')->put('releases/srs-genie/srs-genie-v2.6.0.zip', $content);

        $release = SoftwareRelease::create([
            'product_id' => $product->id,
            'version' => '2.6.0',
            'min_php_version' => '8.2.0',
            'changelog' => 'Release 2.6.0',
            'file_path' => 'releases/srs-genie/srs-genie-v2.6.0.zip',
            'file_size_bytes' => strlen($content),
            'sha256_checksum' => hash('sha256', $content),
            'is_active' => true,
        ]);

        // 1. Service method check
        $this->assertTrue($releaseManager->verifyReleaseIntegrity($release));

        // 2. Admin endpoint check
        $this->actingAs($admin)
            ->post(route('admin.licensing.releases.verify-integrity', $release))
            ->assertRedirect()
            ->assertSessionHas('status');

        // 3. Corrupt content and verify detection
        Storage::disk('local')->put('releases/srs-genie/srs-genie-v2.6.0.zip', 'Tampered Content');
        $this->assertFalse($releaseManager->verifyReleaseIntegrity($release));

        $this->actingAs($admin)
            ->post(route('admin.licensing.releases.verify-integrity', $release))
            ->assertRedirect()
            ->assertSessionHasErrors(['error']);
    }

    public function test_admin_can_download_release_directly(): void
    {
        Storage::fake('local');
        $admin = $this->admin();

        $product = SoftwareProduct::create([
            'name' => 'SRS Genie',
            'slug' => 'srs-genie',
            'is_active' => true,
        ]);

        $content = 'PK...Direct Admin Download';
        Storage::disk('local')->put('releases/srs-genie/srs-genie-v2.6.0.zip', $content);

        $release = SoftwareRelease::create([
            'product_id' => $product->id,
            'version' => '2.6.0',
            'min_php_version' => '8.2.0',
            'changelog' => 'Release notes',
            'file_path' => 'releases/srs-genie/srs-genie-v2.6.0.zip',
            'file_size_bytes' => strlen($content),
            'sha256_checksum' => hash('sha256', $content),
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.licensing.releases.download', $release));
        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=srs-genie-v2.6.0.zip');
    }

    public function test_signed_download_fails_if_license_is_revoked_or_suspended(): void
    {
        Storage::fake('local');
        $releaseManager = app(ReleaseManagerService::class);

        $product = SoftwareProduct::create([
            'name' => 'SRS Genie',
            'slug' => 'srs-genie',
            'is_active' => true,
        ]);

        $license = License::create([
            'product_id' => $product->id,
            'license_key' => 'AGAPE-SRS-REV0-0001-TEST',
            'channel' => 'agape_direct',
            'tier' => 'regular',
            'status' => 'revoked',
            'client_secret_salt' => Str::random(32),
        ]);

        $release = SoftwareRelease::create([
            'product_id' => $product->id,
            'version' => '2.6.0',
            'file_path' => 'releases/srs-genie/srs-genie-v2.6.0.zip',
            'sha256_checksum' => hash('sha256', 'dummy'),
            'is_active' => true,
        ]);

        $url = $releaseManager->generateSignedDownloadUrl($release, $license);

        $response = $this->get($url);
        $response->assertStatus(403);
    }

    public function test_signed_download_succeeds_for_active_license_and_logs_audit(): void
    {
        Storage::fake('local');
        $releaseManager = app(ReleaseManagerService::class);

        $product = SoftwareProduct::create([
            'name' => 'SRS Genie',
            'slug' => 'srs-genie',
            'is_active' => true,
        ]);

        $license = License::create([
            'product_id' => $product->id,
            'license_key' => 'AGAPE-SRS-ACT1-0001-TEST',
            'channel' => 'agape_direct',
            'tier' => 'regular',
            'status' => 'active',
            'client_secret_salt' => Str::random(32),
        ]);

        $content = 'PK...Authenticated Download Stream';
        Storage::disk('local')->put('releases/srs-genie/srs-genie-v2.6.0.zip', $content);

        $release = SoftwareRelease::create([
            'product_id' => $product->id,
            'version' => '2.6.0',
            'min_php_version' => '8.2.0',
            'changelog' => 'Release notes',
            'file_path' => 'releases/srs-genie/srs-genie-v2.6.0.zip',
            'file_size_bytes' => strlen($content),
            'sha256_checksum' => hash('sha256', $content),
            'is_active' => true,
        ]);

        $url = $releaseManager->generateSignedDownloadUrl($release, $license);

        $response = $this->get($url);
        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=srs-genie-v2.6.0.zip');

        $this->assertDatabaseHas('license_audit_logs', [
            'license_id' => $license->id,
            'event' => 'update_download',
            'response_code' => 200,
        ]);
    }
}
