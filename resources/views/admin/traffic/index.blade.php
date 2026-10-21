@extends('layouts.admin')

@section('title', 'Traffic')
@section('heading', 'Traffic')

@section('content')
    <div class="mb-6 flex flex-wrap items-center gap-3">
        @foreach ([7 => '7 days', 30 => '30 days', 90 => '90 days', 365 => 'Year'] as $value => $label)
            <a href="{{ route('admin.traffic.index', ['days' => $value]) }}"
               class="tag {{ $days === $value ? '!border-accent !text-accent' : 'hover:border-accent hover:text-accent' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="mb-6 max-w-2xl text-sm text-muted">
        What visitors read. A visitor is a key rather than a person — no address is kept,
        so the same visitor can be recognised without holding anything that identifies them.
    </div>

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="admin-card">
            <span class="block font-display text-4xl text-white">{{ $report['views'] }}</span>
            <span class="mt-3 block font-display text-xs tracking-[0.24em] text-muted uppercase">Page views</span>
        </div>

        <div class="admin-card">
            <span class="block font-display text-4xl text-white">{{ $report['visitors'] }}</span>
            <span class="mt-3 block font-display text-xs tracking-[0.24em] text-muted uppercase">Visitors</span>
        </div>

        <div class="admin-card">
            <span class="block font-display text-4xl text-white">{{ count($report['paths']) }}</span>
            <span class="mt-3 block font-display text-xs tracking-[0.24em] text-muted uppercase">Pages read</span>
        </div>

        <div class="admin-card">
            <span class="block font-display text-4xl text-white">{{ count($report['referrers']) }}</span>
            <span class="mt-3 block font-display text-xs tracking-[0.24em] text-muted uppercase">Referrers</span>
        </div>
    </div>

    <section class="admin-card mt-8">
        <h2 class="mb-5 !text-xl text-white">Views by day</h2>

        <div class="text-accent">
            @include('admin.reports.partials.revenue-chart', ['series' => array_map(
                fn (array $day): array => ['date' => $day['date'], 'count' => $day['views'], 'total' => $day['views']],
                $report['daily'],
            )])
        </div>
    </section>

    <div class="mt-8 grid gap-6 xl:grid-cols-2">
        <section class="admin-card !p-0">
            <div class="border-b border-line p-5">
                <h2 class="!text-xl text-white">Most read</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Path</th>
                            <th class="text-right">Views</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report['paths'] as $path)
                            <tr>
                                <td class="font-mono text-xs text-body">{{ $path['value'] }}</td>
                                <td class="text-right text-muted">{{ $path['count'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="py-10 text-center text-muted">No traffic recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card !p-0">
            <div class="border-b border-line p-5">
                <h2 class="!text-xl text-white">Where they came from</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Referrer</th>
                            <th class="text-right">Views</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report['referrers'] as $referrer)
                            <tr>
                                <td class="font-mono text-xs text-body">{{ $referrer['value'] }}</td>
                                <td class="text-right text-muted">{{ $referrer['count'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="py-10 text-center text-muted">No referrers recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
