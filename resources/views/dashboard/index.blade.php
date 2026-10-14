@extends('layouts.app')

@section('title', 'Client Dashboard')
@section('description', 'Your Agape Infosys client dashboard: orders, subscriptions, digital access and account details.')

@section('content')
    @include('dashboard.partials.nav', [
        'title' => 'Welcome back, '.auth()->user()->name,
        'lead' => 'Everything you have bought from us in one place — orders, subscriptions and the digital access they unlock.',
    ])

    <div class="grid gap-5 sm:grid-cols-3">
        <div class="admin-card">
            <span class="block font-display text-5xl text-white">{{ $paidOrderCount }}</span>
            <span class="mt-3 block font-display text-xs tracking-[0.24em] text-muted uppercase">Paid orders</span>
        </div>

        <div class="admin-card">
            <span class="block font-display text-5xl text-white">{{ $activeSubscriptionCount }}</span>
            <span class="mt-3 block font-display text-xs tracking-[0.24em] text-muted uppercase">Active subscriptions</span>
        </div>

        <div class="admin-card">
            <span class="block font-display text-5xl text-white">{{ $pendingOrderCount }}</span>
            <span class="mt-3 block font-display text-xs tracking-[0.24em] text-muted uppercase">Awaiting payment</span>
        </div>
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-3">
        <section class="admin-card !p-0 xl:col-span-2">
            <div class="flex items-center justify-between gap-4 px-6 pt-6">
                <h2 class="!text-xl text-white">Recent orders</h2>
                <a href="{{ route('dashboard.orders.index') }}" class="text-xs tracking-[0.2em] text-accent uppercase">
                    View all
                </a>
            </div>

            <div class="overflow-x-auto p-6">
                @if ($recentOrders->isEmpty())
                    <p class="text-muted">No orders yet — anything you buy appears here instantly.</p>
                @else
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('dashboard.orders.show', $order) }}" class="font-mono text-xs text-white hover:text-accent">
                                            {{ $order->reference }}
                                        </a>
                                        <span class="block text-xs text-muted">{{ $order->created_at->format('d M Y') }}</span>
                                    </td>
                                    <td class="text-body">
                                        {{ $order->product?->name ?? '—' }}
                                        <span class="block text-xs text-muted">{{ $order->plan?->name ?? '—' }}</span>
                                    </td>
                                    <td class="whitespace-nowrap text-white">{{ $order->formattedAmount() }}</td>
                                    <td><span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </section>

        <section class="admin-card">
            <h2 class="mb-5 !text-xl text-white">Active subscriptions</h2>

            <ul class="space-y-4">
                @forelse ($activeSubscriptions as $subscription)
                    <li class="border-b border-line pb-4 last:border-0 last:pb-0">
                        <span class="block text-white">{{ $subscription->product?->name ?? '—' }}</span>
                        <span class="block text-xs text-muted">
                            {{ $subscription->plan?->name ?? '—' }} ·
                            {{ $subscription->ends_at !== null ? 'until '.$subscription->ends_at->format('d M Y') : 'no expiry' }}
                        </span>
                    </li>
                @empty
                    <li class="text-muted">No active subscriptions yet.</li>
                @endforelse
            </ul>

            <div class="mt-6 space-y-3 border-t border-line pt-6">
                <a href="{{ route('dashboard.access.index') }}" class="btn btn--ghost w-full text-center">
                    My Access
                    <span class="btn__strip" aria-hidden="true"></span>
                </a>
                <a href="{{ route('shop.index') }}" class="btn btn--ghost w-full text-center">
                    Browse The Shop
                    <span class="btn__strip" aria-hidden="true"></span>
                </a>
            </div>
        </section>
    </div>

    @include('dashboard.partials.end')
@endsection
