<?php

namespace Tests\Feature\Console;

use App\Models\DomainBinding;
use App\Models\License;
use App\Models\LicenseAuditLog;
use App\Models\SoftwareProduct;
use App\Models\SoftwareRelease;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class LicensingCommandsTest extends TestCase
{
    use RefreshDatabase;

    private SoftwareProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = SoftwareProduct::create([
            'name' => 'SRS Genie',
            'slug' => 'srs-genie',
            'is_active' => true,
        ]);
    }

    public function test_license_issue_artisan_command(): void
    {
        $exitCode = Artisan::call('license:issue', [
            'product' => 'srs-genie',
            'email' => 'cli-buyer@agency.com',
            '--tier' => 'agency',
            '--username' => 'CLI Agency',
        ]);

        $this->assertEquals(0, $exitCode);

        $license = License::where('buyer_email', 'cli-buyer@agency.com')->first();
        $this->assertNotNull($license);
        $this->assertEquals('agency', $license->tier);
        $this->assertEquals(5, $license->max_domains);
        $this->assertStringStartsWith('AGAPE-SRS-', $license->license_key);
    }

    public function test_release_publish_artisan_command(): void
    {
        Storage::fake('local');

        $tempFile = tempnam(sys_get_temp_dir(), 'rel');
        file_put_contents($tempFile, 'PK...Sample CLI Release Content');

        $exitCode = Artisan::call('release:publish', [
            'product' => 'srs-genie',
            'version' => '2.7.0',
            'file' => $tempFile,
            '--changelog' => 'New release from CLI',
        ]);

        $this->assertEquals(0, $exitCode);

        $release = SoftwareRelease::where('version', '2.7.0')->first();
        $this->assertNotNull($release);
        $this->assertEquals(hash('sha256', 'PK...Sample CLI Release Content'), $release->sha256_checksum);

        @unlink($tempFile);
    }

    public function test_prune_logs_artisan_command(): void
    {
        $license = License::create([
            'product_id' => $this->product->id,
            'license_key' => 'AGAPE-SRS-PRUN-0001-TEST',
            'channel' => 'agape_direct',
            'tier' => 'regular',
            'status' => 'active',
            'client_secret_salt' => Str::random(32),
        ]);

        // Old log (100 days ago)
        $oldLog = LicenseAuditLog::create([
            'license_id' => $license->id,
            'event' => 'heartbeat',
            'created_at' => now()->subDays(100),
        ]);

        // Recent log (2 days ago)
        $recentLog = LicenseAuditLog::create([
            'license_id' => $license->id,
            'event' => 'heartbeat',
            'created_at' => now()->subDays(2),
        ]);

        Artisan::call('license:prune-logs', ['--days' => 30]);

        $this->assertDatabaseMissing('license_audit_logs', ['id' => $oldLog->id]);
        $this->assertDatabaseHas('license_audit_logs', ['id' => $recentLog->id]);
    }

    public function test_detect_abuse_artisan_command_suspends_ip_velocity_violator(): void
    {
        $license = License::create([
            'product_id' => $this->product->id,
            'license_key' => 'AGAPE-SRS-ABUS-0002-TEST',
            'channel' => 'agape_direct',
            'tier' => 'regular',
            'max_domains' => 1,
            'status' => 'active',
            'client_secret_salt' => Str::random(32),
        ]);

        $binding = DomainBinding::create([
            'license_id' => $license->id,
            'domain' => 'leaked-site.com',
            'status' => 'active',
            'activated_at' => now(),
        ]);

        // Simulate 12 distinct public IPs pinging within 24 hours
        for ($i = 1; $i <= 12; $i++) {
            LicenseAuditLog::create([
                'license_id' => $license->id,
                'event' => 'heartbeat',
                'ip_address' => "198.51.100.{$i}",
                'domain' => 'leaked-site.com',
                'created_at' => now()->subMinutes($i * 5),
            ]);
        }

        $exitCode = Artisan::call('license:detect-abuse');
        $this->assertEquals(0, $exitCode);

        $license->refresh();
        $binding->refresh();

        $this->assertEquals('suspended', $license->status);
        $this->assertEquals('deactivated', $binding->status);

        $this->assertDatabaseHas('license_audit_logs', [
            'license_id' => $license->id,
            'event' => 'abuse_ip_velocity_auto_suspend',
        ]);
    }
}
