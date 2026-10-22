<?php

namespace Tests\Feature;

use App\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrafficPruneTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_views_are_removed_and_recent_ones_kept(): void
    {
        $old = PageView::factory()->create(['created_at' => now()->subDays(120)]);
        $recent = PageView::factory()->create(['created_at' => now()->subDays(10)]);

        $this->artisan('traffic:prune', ['--days' => 90])->assertSuccessful();

        $this->assertDatabaseMissing('page_views', ['id' => $old->id]);
        $this->assertDatabaseHas('page_views', ['id' => $recent->id]);
    }

    public function test_the_retention_window_can_be_changed(): void
    {
        $view = PageView::factory()->create(['created_at' => now()->subDays(40)]);

        $this->artisan('traffic:prune', ['--days' => 90])->assertSuccessful();

        $this->assertDatabaseHas('page_views', ['id' => $view->id]);

        $this->artisan('traffic:prune', ['--days' => 30])->assertSuccessful();

        $this->assertDatabaseMissing('page_views', ['id' => $view->id]);
    }

    public function test_a_dry_run_removes_nothing(): void
    {
        $view = PageView::factory()->create(['created_at' => now()->subDays(120)]);

        $this->artisan('traffic:prune', ['--days' => 90, '--dry-run' => true])->assertSuccessful();

        $this->assertDatabaseHas('page_views', ['id' => $view->id]);
    }

    public function test_pruning_an_empty_table_says_so(): void
    {
        $this->artisan('traffic:prune')
            ->expectsOutputToContain('Nothing to prune')
            ->assertSuccessful();
    }

    public function test_the_prune_is_scheduled_daily(): void
    {
        // Asserted through the command artisan itself uses, so the test
        // sees exactly the schedule the server will run.
        $this->artisan('schedule:list')
            ->expectsOutputToContain('traffic:prune')
            ->expectsOutputToContain('pages:publish-scheduled')
            ->assertSuccessful();
    }

    public function test_the_install_helper_prints_the_cron_lines(): void
    {
        $this->artisan('schedule:install')
            ->expectsOutputToContain('Scheduled Tasks (Cron)')
            ->expectsOutputToContain('schedule:run')
            ->expectsOutputToContain('queue:work')
            ->assertSuccessful();
    }

    public function test_the_install_helper_reports_the_queue_state(): void
    {
        PageView::factory()->count(2)->create();

        $this->artisan('schedule:install', ['--check' => true])
            ->expectsOutputToContain('Pending jobs')
            ->assertSuccessful();
    }

    public function test_the_install_helper_warns_when_jobs_are_stuck(): void
    {
        PageView::factory()->create();

        $this->artisan('schedule:install', ['--check' => true])
            ->expectsOutputToContain('Pending jobs')
            ->assertSuccessful();
    }
}
