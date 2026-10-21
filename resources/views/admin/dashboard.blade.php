@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <p class="text-sm text-muted">
            {{ $report['from']->format('d M Y') }} to {{ $report['to']->format('d M Y') }}
        </p>

        <div class="flex flex-wrap items-center gap-3">
            @foreach (['7d' => '7 days', '30d' => '30 days', '90d' => '90 days', '365d' => 'Year'] as $value => $label)
                <a href="{{ route('admin.dashboard', ['range' => $value]) }}"
                   class="tag {{ $preset === $value ? '!border-accent !text-accent' : 'hover:border-accent hover:text-accent' }}">
                    {{ $label }}
                </a>
            @endforeach

            <a href="{{ route('admin.reports.index', ['range' => $preset]) }}" class="tag hover:border-accent hover:text-accent">
                Full report
            </a>
        </div>
    </div>

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

    <section class="admin-card mt-6">
        <h2 class="mb-5 !text-xl text-white">Revenue by day</h2>

        <div class="text-accent">
            @include('admin.reports.partials.revenue-chart', ['series' => $report['revenue']])
        </div>
    </section>

    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ($counts as $count)
            <a href="{{ route($count['route']) }}" class="admin-card group transition-colors duration-300 hover:border-accent">
                <span class="block font-display text-5xl text-white group-hover:text-accent">
                    {{ $count['value'] }}
                </span>
                <span class="mt-3 block font-display text-xs tracking-[0.24em] text-muted uppercase">
                    {{ $count['label'] }}
                </span>
            </a>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-3">
        <section class="admin-card xl:col-span-2">
            <div class="mb-5 flex items-center justify-between gap-4">
                <h2 class="!text-xl text-white">Unread messages</h2>
                <a href="{{ route('admin.messages.index', ['inbox' => 'unread']) }}" class="text-xs tracking-[0.2em] text-accent uppercase">
                    View all
                </a>
            </div>

            @if ($unreadMessages->isEmpty())
                <p class="text-muted">Inbox zero — no unread enquiries.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>From</th>
                                <th>Subject</th>
                                <th>Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($unreadMessages as $message)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.messages.show', $message) }}" class="text-white hover:text-accent">
                                            {{ $message->name }}
                                        </a>
                                        <span class="block text-xs text-muted">{{ $message->email }}</span>
                                    </td>
                                    <td class="text-body">{{ $message->subject ?: \Illuminate\Support\Str::limit($message->message, 60) }}</td>
                                    <td class="whitespace-nowrap text-muted">{{ $message->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="admin-card">
            <h2 class="mb-5 !text-xl text-white">Drafts</h2>

            <ul class="space-y-4">
                <li class="flex items-center justify-between border-b border-line pb-4">
                    <span class="text-body">Unpublished services</span>
                    <span class="font-display text-2xl text-white">{{ $drafts['services'] }}</span>
                </li>
                <li class="flex items-center justify-between border-b border-line pb-4">
                    <span class="text-body">Unpublished case studies</span>
                    <span class="font-display text-2xl text-white">{{ $drafts['projects'] }}</span>
                </li>
                <li class="flex items-center justify-between">
                    <span class="text-body">Unpublished articles</span>
                    <span class="font-display text-2xl text-white">{{ $drafts['posts'] }}</span>
                </li>
            </ul>

            <div class="mt-6 border-t border-line pt-6">
                <h3 class="mb-4 font-display text-xs tracking-[0.24em] text-muted uppercase">Latest articles</h3>

                <ul class="space-y-3 text-sm">
                    @forelse ($recentPosts as $post)
                        <li>
                            <a href="{{ route('admin.posts.edit', $post) }}" class="text-body hover:text-accent">
                                {{ \Illuminate\Support\Str::limit($post->title, 46) }}
                            </a>
                        </li>
                    @empty
                        <li class="text-muted">No articles yet.</li>
                    @endforelse
                </ul>
            </div>
        </section>
    </div>
@endsection
