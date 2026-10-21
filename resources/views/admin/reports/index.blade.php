@extends('layouts.admin')

@section('title', 'Reports')
@section('heading', 'Revenue Report')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-center gap-3">
            @foreach (['7d' => 'Last 7 days', '30d' => 'Last 30 days', '90d' => 'Last 90 days', '365d' => 'Last year'] as $value => $label)
                <a href="{{ route('admin.reports.index', ['range' => $value]) }}"
                   class="tag {{ $preset === $value ? '!border-accent !text-accent' : 'hover:border-accent hover:text-accent' }}">
                    {{ $label }}
                </a>
            @endforeach
        </form>

        <a href="{{ route('admin.reports.export', ['range' => $preset]) }}" class="tag hover:border-accent hover:text-accent">
            Export CSV
        </a>
    </div>

    <p class="mb-6 text-sm text-muted">
        {{ $report['from']->format('d M Y') }} to {{ $report['to']->format('d M Y') }}
    </p>

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="admin-card">
            <span class="block font-display text-4xl text-white">
                {{ \Illuminate\Support\Number::currency($report['kpis']['revenue_total'] / 100) }}
            </span>
            <span class="mt-3 block font-display text-xs tracking-[0.24em] text-muted uppercase">Revenue</span>
        </div>

        <div class="admin-card">
            <span class="block font-display text-4xl text-white">{{ $report['kpis']['orders'] + $report['kpis']['shop_orders'] }}</span>
            <span class="mt-3 block font-display text-xs tracking-[0.24em] text-muted uppercase">Orders</span>
        </div>

        <div class="admin-card">
            <span class="block font-display text-4xl text-white">{{ $report['kpis']['subscriptions'] }}</span>
            <span class="mt-3 block font-display text-xs tracking-[0.24em] text-muted uppercase">New subscriptions</span>
        </div>

        <div class="admin-card">
            <span class="block font-display text-4xl text-white">{{ $report['kpis']['submissions'] }}</span>
            <span class="mt-3 block font-display text-xs tracking-[0.24em] text-muted uppercase">Submissions</span>
        </div>
    </div>

    <section class="admin-card mt-8">
        <h2 class="mb-5 !text-xl text-white">Revenue by day</h2>

        <div class="text-accent">
            @include('admin.reports.partials.revenue-chart', ['series' => $report['revenue']])
        </div>
    </section>

    <div class="mt-8 grid gap-6 xl:grid-cols-2">
        <section class="admin-card !p-0">
            <div class="border-b border-line p-5">
                <h2 class="!text-xl text-white">Top plans</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Plan</th>
                            <th>Orders</th>
                            <th class="text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report['topPlans'] as $plan)
                            <tr>
                                <td class="text-body">{{ $plan['name'] }}</td>
                                <td class="text-muted">{{ $plan['count'] }}</td>
                                <td class="text-right text-white">
                                    {{ \Illuminate\Support\Number::currency($plan['total'] / 100) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-10 text-center text-muted">No sales in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card !p-0">
            <div class="border-b border-line p-5">
                <h2 class="!text-xl text-white">Top products</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Orders</th>
                            <th class="text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report['topProducts'] as $product)
                            <tr>
                                <td class="text-body">{{ $product['name'] }}</td>
                                <td class="text-muted">{{ $product['count'] }}</td>
                                <td class="text-right text-white">
                                    {{ \Illuminate\Support\Number::currency($product['total'] / 100) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-10 text-center text-muted">No sales in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
