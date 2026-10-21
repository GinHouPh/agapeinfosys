<?php

namespace Tests\Feature;

use App\Models\PageView;
use App\Models\User;
use App\Services\TrafficService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrafficTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_a_public_page_view_is_recorded(): void
    {
        $this->get('/about')->assertOk();

        $view = PageView::query()->firstOrFail();

        $this->assertSame('/about', $view->path);
        $this->assertSame(200, $view->status);
        $this->assertNotEmpty($view->visitor);
    }

    public function test_the_control_panel_is_not_recorded(): void
    {
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk();

        $this->assertDatabaseCount('page_views', 0);
    }

    public function test_the_address_is_never_stored(): void
    {
        $this->get('/about')->assertOk();

        $view = PageView::query()->firstOrFail();

        $this->assertNotNull($view->ip_hash);
        $this->assertSame(64, strlen($view->ip_hash));
    }

    public function test_a_referrer_from_another_site_is_not_kept(): void
    {
        $this->withHeader('referer', 'https://somewhere-else.example/page')
            ->get('/about')
            ->assertOk();

        $this->assertNull(PageView::query()->firstOrFail()->referrer);
    }

    public function test_a_referrer_from_this_site_is_kept(): void
    {
        $this->withHeader('referer', config('app.url').'/blog')
            ->get('/about')
            ->assertOk();

        $this->assertNotNull(PageView::query()->firstOrFail()->referrer);
    }

    public function test_the_report_counts_views_and_visitors(): void
    {
        PageView::factory()->create(['path' => '/about', 'visitor' => 'a']);
        PageView::factory()->create(['path' => '/about', 'visitor' => 'a']);
        PageView::factory()->create(['path' => '/blog', 'visitor' => 'b']);

        $report = app(TrafficService::class)->report(now()->subDay(), now()->addDay());

        $this->assertSame(3, $report['views']);
        $this->assertSame(2, $report['visitors']);
        $this->assertSame('/about', $report['paths'][0]['value']);
        $this->assertSame(2, $report['paths'][0]['count']);
    }

    public function test_the_report_ignores_views_outside_the_period(): void
    {
        PageView::factory()->create(['created_at' => now()->subDays(40)]);
        PageView::factory()->create(['created_at' => now()]);

        $report = app(TrafficService::class)->report(now()->subDays(7), now());

        $this->assertSame(1, $report['views']);
    }

    public function test_the_traffic_screen_renders(): void
    {
        PageView::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.traffic.index'))
            ->assertOk()
            ->assertSee('Views by day', false);
    }
}
