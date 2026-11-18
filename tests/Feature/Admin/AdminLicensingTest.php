<?php

namespace Tests\Feature\Admin;

use App\Models\DomainBinding;
use App\Models\License;
use App\Models\LicenseAuditLog;
use App\Models\SoftwareProduct;
use App\Models\SoftwareRelease;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminLicensingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_guest_cannot_access_admin_licensing(): void
    {
        $this->get(route('admin.licensing.products.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.licensing.licenses.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.licensing.releases.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.licensing.audit-logs.index'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_software_products_list(): void
    {
        SoftwareProduct::create([
            'name' => 'SRS Genie',
            'slug' => 'srs-genie',
            'envato_item_id' => '55234981',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.licensing.products.index'))
            ->assertOk()
            ->assertSee('SRS Genie')
            ->assertSee('srs-genie')
            ->assertSee('55234981');
    }

    public function test_admin_can_create_and_update_software_product(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.licensing.products.create'))
            ->assertOk()
            ->assertSee('Register Software Product');

        $storeResponse = $this->actingAs($admin)->post(route('admin.licensing.products.store'), [
            'name' => 'Agent Architect',
            'slug' => 'agent-architect',
            'envato_item_id' => '99887766',
            'category' => 'ai-saas',
            'currency' => 'USD',
            'is_active' => '1',
        ]);

        $storeResponse->assertRedirect(route('admin.licensing.products.index'));

        $this->assertDatabaseHas('software_products', [
            'name' => 'Agent Architect',
            'slug' => 'agent-architect',
            'envato_item_id' => '99887766',
            'is_active' => true,
        ]);

        $product = SoftwareProduct::where('slug', 'agent-architect')->firstOrFail();

        $updateResponse = $this->actingAs($admin)->put(route('admin.licensing.products.update', $product), [
            'name' => 'Agent Architect Pro',
            'slug' => 'agent-architect-pro',
            'envato_item_id' => '99887766',
            'category' => 'ai-saas',
            'currency' => 'USD',
            'is_active' => '1',
        ]);

        $updateResponse->assertRedirect(route('admin.licensing.products.index'));

        $this->assertDatabaseHas('software_products', [
            'id' => $product->id,
            'name' => 'Agent Architect Pro',
            'slug' => 'agent-architect-pro',
        ]);
    }

    public function test_admin_can_view_licenses_and_issue_manual_license(): void
    {
        $admin = $this->admin();
        $product = SoftwareProduct::create([
            'name' => 'SRS Genie',
            'slug' => 'srs-genie',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.licensing.licenses.create'))
            ->assertOk()
            ->assertSee('Issue Manual Software License');

        $response = $this->actingAs($admin)->post(route('admin.licensing.licenses.store'), [
            'product_id' => $product->id,
            'tier' => 'agency',
            'channel' => 'partner',
            'buyer_email' => 'partner@enterprisecorp.com',
            'buyer_username' => 'Enterprise Corp',
            'max_domains' => 5,
            'support_until' => now()->addYear()->format('Y-m-d'),
        ]);

        $license = License::where('buyer_email', 'partner@enterprisecorp.com')->firstOrFail();
        $response->assertRedirect(route('admin.licensing.licenses.show', $license));

        $this->assertStringStartsWith('AGAPE-SRS-', $license->license_key);
        $this->assertEquals(5, $license->max_domains);
        $this->assertEquals('agency', $license->tier);

        // View licenses index
        $this->actingAs($admin)
            ->get(route('admin.licensing.licenses.index'))
            ->assertOk()
            ->assertSee($license->license_key)
            ->assertSee('Enterprise Corp');
    }

    public function test_admin_can_view_license_detail_and_manage_status(): void
    {
        $admin = $this->admin();
        $product = SoftwareProduct::create([
            'name' => 'SRS Genie',
            'slug' => 'srs-genie',
            'is_active' => true,
        ]);

        $license = License::create([
            'product_id' => $product->id,
            'license_key' => 'AGAPE-SRS-MANA-GE01-TEST',
            'channel' => 'agape_direct',
            'buyer_email' => 'buyer@test.com',
            'buyer_username' => 'Buyer Test',
            'tier' => 'regular',
            'max_domains' => 1,
            'status' => 'active',
            'client_secret_salt' => 'salt1234567890123456789012345678',
        ]);

        $binding = DomainBinding::create([
            'license_id' => $license->id,
            'domain' => 'clientdomain.com',
            'installed_version' => '2.5.0',
            'status' => 'active',
            'activated_at' => now(),
        ]);

        // Show view
        $this->actingAs($admin)
            ->get(route('admin.licensing.licenses.show', $license))
            ->assertOk()
            ->assertSee('AGAPE-SRS-MANA-GE01-TEST')
            ->assertSee('clientdomain.com');

        // Suspend status
        $this->actingAs($admin)
            ->post(route('admin.licensing.licenses.status', $license), [
                'status' => 'suspended',
            ])
            ->assertRedirect();

        $license->refresh();
        $binding->refresh();
        $this->assertEquals('suspended', $license->status);
        $this->assertEquals('deactivated', $binding->status);

        // Reactivate
        $this->actingAs($admin)
            ->post(route('admin.licensing.licenses.status', $license), [
                'status' => 'active',
            ])
            ->assertRedirect();

        $license->refresh();
        $this->assertEquals('active', $license->status);
    }

    public function test_admin_can_unbind_domain_and_extend_support(): void
    {
        $admin = $this->admin();
        $product = SoftwareProduct::create([
            'name' => 'SRS Genie',
            'slug' => 'srs-genie',
            'is_active' => true,
        ]);

        $license = License::create([
            'product_id' => $product->id,
            'license_key' => 'AGAPE-SRS-UNBI-ND01-TEST',
            'channel' => 'agape_direct',
            'buyer_email' => 'client@host.com',
            'tier' => 'regular',
            'max_domains' => 1,
            'status' => 'active',
            'support_until' => now()->addMonths(2),
            'client_secret_salt' => 'salt1234567890123456789012345678',
        ]);

        $binding = DomainBinding::create([
            'license_id' => $license->id,
            'domain' => 'oldhost.agency.com',
            'status' => 'active',
            'activated_at' => now(),
        ]);

        // Unbind domain
        $this->actingAs($admin)
            ->post(route('admin.licensing.licenses.unbind', [$license, $binding]))
            ->assertRedirect();

        $binding->refresh();
        $this->assertEquals('deactivated', $binding->status);

        // Extend support by 12 months
        $this->actingAs($admin)
            ->post(route('admin.licensing.licenses.extend-support', $license), [
                'months' => 12,
            ])
            ->assertRedirect();

        $license->refresh();
        $this->assertTrue($license->support_until->isAfter(now()->addMonths(13)));
    }

    public function test_admin_can_view_audit_logs(): void
    {
        $admin = $this->admin();
        $product = SoftwareProduct::create([
            'name' => 'SRS Genie',
            'slug' => 'srs-genie',
            'is_active' => true,
        ]);

        $license = License::create([
            'product_id' => $product->id,
            'license_key' => 'AGAPE-SRS-AUDI-T001-TEST',
            'channel' => 'agape_direct',
            'tier' => 'regular',
            'status' => 'active',
            'client_secret_salt' => 'salt1234567890123456789012345678',
        ]);

        LicenseAuditLog::create([
            'license_id' => $license->id,
            'event' => 'verification_success',
            'domain' => 'portal.example.com',
            'ip_address' => '203.0.113.195',
            'response_code' => 200,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.licensing.audit-logs.index'))
            ->assertOk()
            ->assertSee('verification_success')
            ->assertSee('portal.example.com')
            ->assertSee('203.0.113.195');
    }

    public function test_admin_can_create_and_manage_software_release(): void
    {
        Storage::fake('local');
        $admin = $this->admin();
        $product = SoftwareProduct::create([
            'name' => 'SRS Genie',
            'slug' => 'srs-genie',
            'is_active' => true,
        ]);

        $zipFile = UploadedFile::fake()->create('srs-genie-v2.6.0.zip', 2048, 'application/zip');

        $response = $this->actingAs($admin)->post(route('admin.licensing.releases.store'), [
            'product_id' => $product->id,
            'version' => '2.6.0',
            'min_php_version' => '8.2.0',
            'changelog' => 'New release changelog notes',
            'release_file' => $zipFile,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.licensing.releases.index'));

        $this->assertDatabaseHas('software_releases', [
            'product_id' => $product->id,
            'version' => '2.6.0',
            'is_active' => true,
        ]);

        $release = SoftwareRelease::where('version', '2.6.0')->firstOrFail();
        $this->assertTrue(Storage::disk('local')->exists($release->file_path));

        // Index view
        $this->actingAs($admin)
            ->get(route('admin.licensing.releases.index'))
            ->assertOk()
            ->assertSee('v2.6.0');
    }
}
