<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\FormSubmission;
use App\Models\Order;
use App\Models\ShopOrder;
use App\Models\Subscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The numbers the dashboard and the reports are built from.
 *
 * Everything is bucketed in PHP rather than in SQL: a month of orders is
 * small, and bucketing by hand keeps the queries portable across SQLite,
 * MySQL and Postgres.
 */
class DashboardService
{
    /**
     * The period a request is asking about.
     *
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    public function range(?string $preset = null): array
    {
        $days = match ($preset) {
            '7d' => 7,
            '90d' => 90,
            '365d' => 365,
            default => 30,
        };

        $to = Carbon::today()->endOfDay();
        $from = Carbon::today()->subDays($days - 1)->startOfDay();

        return [$from, $to, $preset !== null && $preset !== '' ? $preset : '30d'];
    }

    /**
     * The report for a period, with the previous period beside it.
     *
     * @return array<string, mixed>
     */
    public function report(Carbon $from, Carbon $to): array
    {
        $previousFrom = $from->copy()->subDays($from->diffInDays($to) + 1);
        $previousTo = $from->copy()->subDay();

        return [
            'from' => $from,
            'to' => $to,
            'kpis' => $this->kpis($from, $to),
            'previous' => $this->kpis($previousFrom, $previousTo),
            'revenue' => $this->revenueSeries($from, $to),
            'topPlans' => $this->topPlans($from, $to),
            'topProducts' => $this->topProducts($from, $to),
        ];
    }

    /**
     * The headline numbers for a period.
     *
     * @return array<string, mixed>
     */
    public function kpis(Carbon $from, Carbon $to): array
    {
        $orders = $this->paidOrders($from, $to);
        $shopOrders = $this->paidShopOrders($from, $to);

        $revenue = [];

        foreach ($orders->concat($shopOrders) as $order) {
            $currency = $order->currency ?: 'INR';
            $revenue[$currency] = ($revenue[$currency] ?? 0) + (int) $order->amount;
        }

        return [
            'revenue' => $revenue,
            'revenue_total' => array_sum($revenue),
            'orders' => $orders->count(),
            'shop_orders' => $shopOrders->count(),
            'subscriptions' => Subscription::query()
                ->whereBetween('created_at', [$from, $to])
                ->count(),
            'active_subscriptions' => Subscription::query()
                ->active()
                ->count(),
            'submissions' => FormSubmission::query()
                ->whereBetween('created_at', [$from, $to])
                ->count(),
        ];
    }

    /**
     * The daily revenue across a period.
     *
     * @return array<int, array{date: string, total: int, count: int}>
     */
    public function revenueSeries(Carbon $from, Carbon $to): array
    {
        $orders = $this->paidOrders($from, $to)->concat($this->paidShopOrders($from, $to));

        $buckets = [];

        foreach ($orders as $order) {
            $day = $order->paid_at?->toDateString();

            if ($day === null) {
                continue;
            }

            $buckets[$day] ??= ['date' => $day, 'total' => 0, 'count' => 0];
            $buckets[$day]['total'] += (int) $order->amount;
            $buckets[$day]['count']++;
        }

        $series = [];

        for ($day = $from->copy()->startOfDay(); $day->lte($to); $day->addDay()) {
            $key = $day->toDateString();
            $series[] = $buckets[$key] ?? ['date' => $key, 'total' => 0, 'count' => 0];
        }

        return $series;
    }

    /**
     * The plans that earned the most in a period.
     *
     * @return array<int, array{name: string, total: int, count: int}>
     */
    public function topPlans(Carbon $from, Carbon $to, int $limit = 5): array
    {
        $orders = $this->paidOrders($from, $to);

        $buckets = [];

        foreach ($orders as $order) {
            $plan = $order->plan;
            $name = $plan?->name ?? 'Unlinked plan';

            $buckets[$name] ??= ['name' => $name, 'total' => 0, 'count' => 0];
            $buckets[$name]['total'] += (int) $order->amount;
            $buckets[$name]['count']++;
        }

        return $this->rank($buckets, $limit);
    }

    /**
     * The products that earned the most in a period.
     *
     * @return array<int, array{name: string, total: int, count: int}>
     */
    public function topProducts(Carbon $from, Carbon $to, int $limit = 5): array
    {
        $orders = $this->paidOrders($from, $to);

        $buckets = [];

        foreach ($orders as $order) {
            $product = $order->product;
            $name = $product?->name ?? 'Unlinked product';

            $buckets[$name] ??= ['name' => $name, 'total' => 0, 'count' => 0];
            $buckets[$name]['total'] += (int) $order->amount;
            $buckets[$name]['count']++;
        }

        return $this->rank($buckets, $limit);
    }

    /**
     * The paid orders in a period.
     *
     * @return Collection<int, Order>
     */
    private function paidOrders(Carbon $from, Carbon $to): Collection
    {
        return Order::query()
            ->where('status', OrderStatus::Paid)
            ->whereBetween('paid_at', [$from, $to])
            ->with(['plan', 'product'])
            ->get();
    }

    /**
     * The paid shop orders in a period.
     *
     * @return Collection<int, ShopOrder>
     */
    private function paidShopOrders(Carbon $from, Carbon $to): Collection
    {
        return ShopOrder::query()
            ->where('status', OrderStatus::Paid)
            ->whereBetween('paid_at', [$from, $to])
            ->get();
    }

    /**
     * Sort buckets by what they earned.
     *
     * @param  array<string, array{name: string, total: int, count: int}>  $buckets
     * @return array<int, array{name: string, total: int, count: int}>
     */
    private function rank(array $buckets, int $limit): array
    {
        $ranked = array_values($buckets);

        usort($ranked, fn (array $a, array $b): int => $b['total'] <=> $a['total']);

        return array_slice($ranked, 0, $limit);
    }
}
