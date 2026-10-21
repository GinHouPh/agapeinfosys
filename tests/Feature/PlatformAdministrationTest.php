<?php

namespace Tests\Feature;

use App\Models\FeatureFlag;
use App\Models\Setting;
use App\Models\User;
use App\Services\FeatureFlagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PlatformAdministrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    protected function tearDown(): void
    {
        Cache::forget('settings.all');

        parent::tearDown();
    }

    public function test_guests_are_redirected_from_the_system_console(): void
    {
        $this->get(route('admin.system.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.system.flags'))->assertRedirect(route('admin.login'));
    }

    public function test_the_system_console_reports_the_platform_health(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.system.index'))
            ->assertOk()
            ->assertSee('Health')
            ->assertSee('Database')
            ->assertSee('Payment gateways');
    }

    public function test_maintenance_mode_holds_the_public_site_back(): void
    {
        Setting::store(['maintenance' => ['enabled' => true, 'message' => 'Scheduled work in progress.']]);

        $this->get(route('home'))
            ->assertStatus(503)
            ->assertSee('Scheduled work in progress.');
    }

    public function test_maintenance_mode_can_be_switched_on_from_the_console(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.system.maintenance'), [
                'enabled' => '1',
                'message' => 'Scheduled work in progress.',
            ])
            ->assertRedirect(route('admin.system.index'));

        $maintenance = Setting::structured('maintenance', []);

        $this->assertTrue($maintenance['enabled']);
        $this->assertSame('Scheduled work in progress.', $maintenance['message']);
    }

    public function test_admins_get_through_maintenance_mode(): void
    {
        Setting::store(['maintenance' => ['enabled' => true, 'message' => 'Down for work.']]);

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_visitors_are_let_back_in_when_maintenance_mode_is_switched_off(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.system.maintenance'), [
                'enabled' => '1',
                'message' => 'Scheduled work in progress.',
            ]);

        $this->actingAs($this->admin)
            ->put(route('admin.system.maintenance'), [
                'enabled' => '0',
            ]);

        Auth::logout();

        $this->get(route('home'))->assertOk();
    }

    public function test_a_flag_that_does_not_exist_reads_as_off(): void
    {
        $this->assertFalse(app(FeatureFlagService::class)->enabled('never-created'));
    }

    public function test_a_flag_can_be_created_and_read(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.system.flags.store'), [
                'key' => 'new-checkout',
                'name' => 'New checkout',
                'rollout_percentage' => 100,
            ])
            ->assertRedirect(route('admin.system.flags'));

        $flag = FeatureFlag::query()->where('key', 'new-checkout')->firstOrFail();

        $this->assertFalse($flag->is_enabled);
        $this->assertFalse(app(FeatureFlagService::class)->enabled('new-checkout'));
    }

    public function test_a_flag_can_be_switched_on(): void
    {
        $flag = FeatureFlag::factory()->create(['key' => 'new-checkout']);

        $this->actingAs($this->admin)
            ->put(route('admin.system.flags.update', $flag), [
                'name' => $flag->name,
                'is_enabled' => '1',
                'rollout_percentage' => 100,
            ])
            ->assertRedirect(route('admin.system.flags'));

        $this->assertTrue($flag->refresh()->is_enabled);
        $this->assertTrue(app(FeatureFlagService::class)->enabled('new-checkout'));
    }

    public function test_a_flag_can_be_deleted(): void
    {
        $flag = FeatureFlag::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.system.flags.destroy', $flag))
            ->assertRedirect(route('admin.system.flags'));

        $this->assertDatabaseMissing('feature_flags', ['id' => $flag->id]);
    }

    public function test_a_flag_inside_its_schedule_does_not_apply(): void
    {
        $flag = FeatureFlag::factory()->on()->create([
            'starts_at' => now()->addDay(),
        ]);

        $this->assertFalse($flag->appliesTo());
        $this->assertFalse(app(FeatureFlagService::class)->enabled($flag->key));
    }

    public function test_a_flag_past_its_end_date_does_not_apply(): void
    {
        $flag = FeatureFlag::factory()->on()->create([
            'ends_at' => now()->subDay(),
        ]);

        $this->assertFalse($flag->appliesTo());
    }

    public function test_a_partial_rollout_reaches_some_buckets_and_not_others(): void
    {
        $flag = FeatureFlag::factory()->on()->create(['rollout_percentage' => 50]);

        $reached = 0;

        for ($i = 0; $i < 100; $i++) {
            if ($flag->appliesTo('visitor-'.$i)) {
                $reached++;
            }
        }

        $this->assertGreaterThan(0, $reached);
        $this->assertLessThan(100, $reached);
    }

    public function test_a_visitor_always_sees_the_same_side_of_a_rollout(): void
    {
        $flag = FeatureFlag::factory()->on()->create(['rollout_percentage' => 50]);

        $first = $flag->appliesTo('visitor-42');

        for ($i = 0; $i < 5; $i++) {
            $this->assertSame($first, $flag->appliesTo('visitor-42'));
        }
    }

    public function test_a_zero_percent_rollout_reaches_nobody(): void
    {
        $flag = FeatureFlag::factory()->on()->create(['rollout_percentage' => 0]);

        $this->assertFalse($flag->appliesTo('anyone'));
    }
}
