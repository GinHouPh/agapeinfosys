@extends('layouts.app')

@section('title', 'My Access')
@section('description', 'The digital access unlocked by your purchases — subscriptions and one-off orders in one place.')

@section('content')
    @include('dashboard.partials.nav', [
        'title' => 'My Access',
        'lead' => 'Every product your purchases unlock. Access activates the moment payment confirms — see the Delivery Policy for the exact timelines.',
    ])

    @if (! $hasAccess)
        <div class="admin-card !p-10 text-center">
            <p class="text-muted">
                No access yet — purchases appear here the moment payment confirms.
                <a href="{{ route('shop.index') }}" class="text-accent hover:underline">Browse the shop</a>
                to get started.
            </p>
        </div>
    @else
        @if ($subscriptions->isNotEmpty())
            <h2 class="mb-5 font-display text-sm tracking-[0.2em] text-white uppercase">From subscriptions</h2>

            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($subscriptions as $subscription)
                    <article class="admin-card space-y-4 !p-8">
                        <div class="flex items-start justify-between gap-4">
                            <h3 class="!text-xl text-white">{{ $subscription->product?->name ?? 'Product' }}</h3>
                            <span class="badge {{ $subscription->isEntitled() ? 'badge--on' : 'badge--off' }}">
                                {{ $subscription->isEntitled() ? 'Access active' : 'Access ended' }}
                            </span>
                        </div>

                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-muted">Plan</dt>
                                <dd class="text-white">{{ $subscription->plan?->name ?? '—' }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-muted">Valid until</dt>
                                <dd class="text-white">{{ $subscription->ends_at?->format('d M Y') ?? 'No expiry' }}</dd>
                            </div>
                        </dl>

                        @if ($subscription->isEntitled() && $subscription->product?->domain)
                            <a href="https://{{ $subscription->product->domain }}" class="btn btn--ghost w-full text-center" target="_blank" rel="noopener">
                                Open {{ $subscription->product->name }}
                                <span class="btn__strip" aria-hidden="true"></span>
                            </a>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif

        @if ($oneTimeOrders->isNotEmpty())
            <h2 class="mb-5 mt-12 font-display text-sm tracking-[0.2em] text-white uppercase">From one-off purchases</h2>

            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($oneTimeOrders as $order)
                    <article class="admin-card space-y-4 !p-8">
                        <div class="flex items-start justify-between gap-4">
                            <h3 class="!text-xl text-white">{{ $order->product?->name ?? 'Product' }}</h3>
                            <span class="badge badge--on">Access active</span>
                        </div>

                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-muted">Plan</dt>
                                <dd class="text-white">{{ $order->plan?->name ?? '—' }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-muted">Order</dt>
                                <dd>
                                    <a href="{{ route('dashboard.orders.show', $order) }}" class="font-mono text-xs text-accent hover:underline">
                                        {{ $order->reference }}
                                    </a>
                                </dd>
                            </div>
                        </dl>

                        @if ($order->product?->domain)
                            <a href="https://{{ $order->product->domain }}" class="btn btn--ghost w-full text-center" target="_blank" rel="noopener">
                                Open {{ $order->product->name }}
                                <span class="btn__strip" aria-hidden="true"></span>
                            </a>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif

        <div class="admin-card mt-8 !p-8">
            <h3 class="mb-4 font-display text-sm tracking-[0.2em] text-white uppercase">Access not showing?</h3>
            <p class="text-sm text-body">
                Access normally activates within minutes of payment and is guaranteed within 24 hours. If a purchase is
                missing, <a href="{{ route('contact.create') }}" class="text-accent hover:underline">contact us</a> with
                your order reference and we will restore it within one business day.
            </p>
        </div>
    @endif

    @include('dashboard.partials.end')
@endsection
