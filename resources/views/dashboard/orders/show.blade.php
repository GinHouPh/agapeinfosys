@extends('layouts.app')

@section('title', 'Order '.$order->reference)
@section('description', 'Receipt for order '.$order->reference.' — '.$order->product?->name.'.')

@section('content')
    @include('dashboard.partials.nav', [
        'title' => 'Order '.$order->reference,
        'lead' => 'The full receipt for this purchase. Keep it for your records — it is also emailed to you at checkout.',
    ])

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="admin-card space-y-6 !p-8 xl:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h2 class="!text-2xl text-white">
                    {{ $order->product?->name ?? 'Order' }}
                    <span class="block text-base text-muted">{{ $order->plan?->name ?? 'Plan' }}</span>
                </h2>

                <span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span>
            </div>

            @if ($order->status === \App\Enums\OrderStatus::Paid)
                <p class="text-sm text-body">
                    Payment received. Your plan is active and the connected product already has access for
                    <strong class="text-white">{{ $order->subscriber_key }}</strong>.
                </p>
            @elseif ($order->status === \App\Enums\OrderStatus::Pending)
                <p class="text-sm text-body">
                    We have not received a confirmation for this order yet. If you completed the payment it is
                    confirmed automatically within a few minutes.
                    <a href="{{ route('dashboard.orders.show', $order) }}" class="text-accent hover:underline">Check again</a>.
                </p>
            @else
                <p class="text-sm text-body">
                    @if ($order->status === \App\Enums\OrderStatus::Expired)
                        This checkout session timed out before payment completed. No money was captured.
                    @elseif ($order->status === \App\Enums\OrderStatus::Refunded)
                        This order was refunded. See the
                        <a href="{{ route('legal.refunds') }}" class="text-accent hover:underline">Refund Policy</a>
                        for the timeline.
                    @else
                        We could not complete the payment for this order. You have not been charged.
                    @endif
                </p>
            @endif

            <dl class="grid gap-4 border-y border-line py-6 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-muted">Reference</dt>
                    <dd class="mt-1 font-mono text-white">{{ $order->reference }}</dd>
                </div>
                <div>
                    <dt class="text-muted">Amount</dt>
                    <dd class="mt-1 font-display text-lg text-accent">{{ $order->formattedAmount() }}</dd>
                </div>
                <div>
                    <dt class="text-muted">Placed</dt>
                    <dd class="mt-1 text-white">{{ $order->created_at->format('d M Y, H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-muted">Paid</dt>
                    <dd class="mt-1 text-white">{{ $order->paid_at?->format('d M Y, H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-muted">Payment method</dt>
                    <dd class="mt-1 text-white">{{ $order->gateway?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-muted">Account</dt>
                    <dd class="mt-1 break-all text-white">{{ $order->subscriber_key }}</dd>
                </div>
            </dl>

            <div class="flex flex-wrap gap-3">
                @if ($order->status === \App\Enums\OrderStatus::Paid && $order->product?->domain)
                    <a href="https://{{ $order->product->domain }}" class="btn btn--accent" target="_blank" rel="noopener">
                        Open {{ $order->product->name }}
                        <span class="btn__strip" aria-hidden="true"></span>
                    </a>
                @endif

                <button type="button" onclick="window.print()" class="btn btn--ghost">
                    Print Receipt
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>

                <a href="{{ route('dashboard.orders.index') }}" class="btn btn--ghost">
                    All Orders
                    <span class="btn__strip" aria-hidden="true"></span>
                </a>
            </div>
        </section>

        <aside class="admin-card space-y-6 !p-8">
            <h3 class="font-display text-sm tracking-[0.2em] text-white uppercase">Subscription</h3>

            @if ($order->subscription === null)
                <p class="text-sm text-muted">
                    This order is not linked to a subscription. Recurring plans appear here with their renewal dates.
                </p>
            @else
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center gap-2">
                        <dt class="w-28 text-muted">Status</dt>
                        <dd><span class="badge {{ $order->subscription->status->badgeClass() }}">{{ $order->subscription->status->label() }}</span></dd>
                    </div>
                    <div class="flex items-center gap-2">
                        <dt class="w-28 text-muted">Plan</dt>
                        <dd class="text-white">{{ $order->subscription->plan?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center gap-2">
                        <dt class="w-28 text-muted">Valid until</dt>
                        <dd class="text-white">{{ $order->subscription->ends_at?->format('d M Y') ?? 'No expiry' }}</dd>
                    </div>
                    @if ($order->subscription->renews_at)
                        <div class="flex items-center gap-2">
                            <dt class="w-28 text-muted">Renews on</dt>
                            <dd class="text-white">{{ $order->subscription->renews_at->format('d M Y') }}</dd>
                        </div>
                    @endif
                </dl>

                <a href="{{ route('dashboard.subscriptions.index') }}" class="font-display text-xs tracking-[0.24em] text-accent uppercase">
                    Manage subscriptions
                </a>
            @endif

            <div class="border-t border-line pt-6">
                <p class="text-sm text-muted">
                    Questions about this order? Reply to the confirmation email or
                    <a href="{{ route('contact.create') }}" class="text-accent hover:underline">contact us</a>
                    with the reference above.
                </p>
            </div>
        </aside>
    </div>

    @include('dashboard.partials.end')
@endsection
