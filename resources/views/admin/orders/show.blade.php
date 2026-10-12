@extends('layouts.admin')

@section('title', 'Order '.$order->reference)
@section('heading', 'Order '.$order->reference)

@section('content')
    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="admin-card space-y-4 !p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h3 class="font-display text-sm tracking-[0.2em] text-white uppercase">Order details</h3>
                    <span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span>
                </div>

                <dl class="grid gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-muted">Reference</dt>
                        <dd class="mt-1 font-mono text-white">{{ $order->reference }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">Created</dt>
                        <dd class="mt-1 text-white">{{ $order->created_at->format('d M Y, H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">Product</dt>
                        <dd class="mt-1 text-white">{{ $order->product?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">Plan</dt>
                        <dd class="mt-1 text-white">{{ $order->plan?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">Account (subscriber key)</dt>
                        <dd class="mt-1 break-all text-white">{{ $order->subscriber_key }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">Customer</dt>
                        <dd class="mt-1 text-white">
                            {{ $order->name ?: '—' }}<br>
                            <span class="text-muted">{{ $order->email ?: '—' }} · {{ $order->phone ?: '—' }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted">Amount</dt>
                        <dd class="mt-1 font-display text-lg text-accent">{{ $order->formattedAmount() }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">Paid at</dt>
                        <dd class="mt-1 text-white">{{ $order->paid_at?->format('d M Y, H:i') ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="admin-card !p-0">
                <h3 class="px-6 pt-6 font-display text-sm tracking-[0.2em] text-white uppercase">Payment events</h3>

                <div class="overflow-x-auto p-6">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Direction</th>
                                <th>Verified</th>
                                <th>Processed</th>
                                <th>Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($order->events as $event)
                                <tr>
                                    <td class="font-mono text-xs text-white">{{ $event->type }}</td>
                                    <td class="text-muted">{{ $event->direction }}</td>
                                    <td>
                                        <span class="badge {{ $event->is_verified ? 'badge--on' : 'badge--off' }}">
                                            {{ $event->is_verified ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $event->is_processed ? 'badge--on' : 'badge--off' }}">
                                            {{ $event->is_processed ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap text-muted">{{ $event->received_at?->format('d M Y, H:i') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-muted">No events recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="admin-card space-y-4 !p-6">
                <h3 class="font-display text-sm tracking-[0.2em] text-white uppercase">Subscription</h3>

                @if ($order->subscription)
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
                                <dt class="w-28 text-muted">Renews</dt>
                                <dd class="text-white">{{ $order->subscription->renews_at->format('d M Y') }}</dd>
                            </div>
                        @endif
                    </dl>
                @else
                    <p class="text-sm text-muted">No subscription linked yet.</p>
                @endif
            </div>

            <div class="admin-card space-y-3 !p-6">
                <h3 class="font-display text-sm tracking-[0.2em] text-white uppercase">Provider</h3>

                <dl class="space-y-3 text-sm">
                    <div class="flex items-center gap-2">
                        <dt class="w-28 text-muted">Gateway</dt>
                        <dd class="text-white">{{ $order->gateway?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center gap-2">
                        <dt class="w-28 text-muted">Order ID</dt>
                        <dd class="break-all font-mono text-xs text-white">{{ $order->gateway_order_id ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center gap-2">
                        <dt class="w-28 text-muted">Payment ID</dt>
                        <dd class="break-all font-mono text-xs text-white">{{ $order->gateway_payment_id ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
@endsection
