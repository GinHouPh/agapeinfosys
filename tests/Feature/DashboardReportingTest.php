<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Product;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardReportingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    /**
     * A paid order, placed on the given day.
     */
    private function paidOrder(int $amount, string $day, ?Plan $plan = null): Order
    {
        $product = Product::factory()->create();
        $plan ??= Plan::factory()->create(['product_id' => $product->id]);

        return Order::query()->create([
            'product_id' => $product->id,
            'plan_id' => $plan->id,
            'reference' => 'AGP-'.str_pad((string) Order::query()->count(), 6, '0', STR_PAD_LEFT),
            'subscriber_key' => 'guest@example.com',
            'name' => 'A Buyer',
            'email' => 'guest@example.com',
            'amount' => $amount,
            'currency' => 'INR',
            'status' => OrderStatus::Paid,
            'paid_at' => Carbon::parse($day),
        ]);
    }

    public function test_the_dashboard_reports_revenue_and_orders(): void
    {
        $this->paidOrder(50000, now()->subDays(2)->toDateString());
        $this->paidOrder(25000, now()->subDay()->toDateString());

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Revenue by day', false);
    }

    public function test_the_kpis_count_only_paid_orders(): void
    {
        $this->paidOrder(50000, now()->toDateString());

        Order::query()->create([
            'product_id' => Product::factory()->create()->id,
            'plan_id' => Plan::factory()->create()->id,
            'reference' => 'AGP-PENDING',
            'subscriber_key' => 'guest@example.com',
            'name' => 'A Buyer',
            'email' => 'guest@example.com',
            'amount' => 99000,
            'currency' => 'INR',
            'status' => OrderStatus::Pending,
        ]);

        [$from, $to] = app(DashboardService::class)->range('30d');

        $kpis = app(DashboardService::class)->kpis($from, $to);

        $this->assertSame(50000, $kpis['revenue_total']);
        $this->assertSame(1, $kpis['orders']);
    }

    public function test_orders_outside_the_period_are_left_out(): void
    {
        $this->paidOrder(50000, now()->subDays(40)->toDateString());

        [$from, $to] = app(DashboardService::class)->range('7d');

        $kpis = app(DashboardService::class)->kpis($from, $to);

        $this->assertSame(0, $kpis['revenue_total']);
    }

    public function test_the_revenue_series_covers_every_day_of_the_period(): void
    {
        $this->paidOrder(50000, now()->toDateString());

        [$from, $to] = app(DashboardService::class)->range('7d');

        $series = app(DashboardService::class)->revenueSeries($from, $to);

        $this->assertCount(7, $series);
        $this->assertSame(50000, $series[6]['total']);
    }

    public function test_the_top_plans_are_ranked_by_revenue(): void
    {
        $product = Product::factory()->create();
        $big = Plan::factory()->create(['product_id' => $product->id, 'name' => 'Big plan']);
        $small = Plan::factory()->create(['product_id' => $product->id, 'name' => 'Small plan']);

        $this->paidOrder(90000, now()->toDateString(), $big);
        $this->paidOrder(10000, now()->toDateString(), $small);

        [$from, $to] = app(DashboardService::class)->range('30d');

        $plans = app(DashboardService::class)->topPlans($from, $to);

        $this->assertSame('Big plan', $plans[0]['name']);
        $this->assertSame(90000, $plans[0]['total']);
    }

    public function test_the_report_page_renders(): void
    {
        $this->paidOrder(50000, now()->toDateString());

        $this->actingAs($this->admin)
            ->get(route('admin.reports.index', ['range' => '30d']))
            ->assertOk()
            ->assertSee('Top plans', false)
            ->assertSee('Top products', false);
    }

    public function test_the_report_can_be_exported_as_csv(): void
    {
        $this->paidOrder(50000, now()->toDateString());

        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.export', ['range' => '30d']));

        $response->assertOk();

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Date', $csv);
        $this->assertStringContainsString('50000', $csv);
    }

    public function test_submissions_are_counted_in_the_period(): void
    {
        $form = Form::factory()->create();

        FormSubmission::query()->create([
            'form_id' => $form->id,
            'answers' => [],
        ]);

        [$from, $to] = app(DashboardService::class)->range('30d');

        $kpis = app(DashboardService::class)->kpis($from, $to);

        $this->assertSame(1, $kpis['submissions']);
    }
}
