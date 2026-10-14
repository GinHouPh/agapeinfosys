@extends('layouts.app')

@section('title', 'My Subscriptions')
@section('description', 'Your subscriptions, renewal dates and self-service cancellation.')

@section('content')
    @include('dashboard.partials.nav', [
        'title' => 'My Subscriptions',
        'lead' => 'Renewal dates, access windows and cancellation — cancelling takes one click and keeps your access until the period you paid for ends.',
    ])

    <section class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Valid until</th>
                        <th>Renews</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subscriptions as $subscription)
                        <tr>
                            <td class="text-white">{{ $subscription->product?->name ?? '—' }}</td>
                            <td class="text-muted">{{ $subscription->plan?->name ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $subscription->status->badgeClass() }}">{{ $subscription->status->label() }}</span>
                                @if ($subscription->cancelled_at)
                                    <span class="block text-xs text-muted">on {{ $subscription->cancelled_at->format('d M Y') }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-muted">
                                {{ $subscription->ends_at?->format('d M Y') ?? 'No expiry' }}
                            </td>
                            <td class="whitespace-nowrap text-muted">{{ $subscription->renews_at?->format('d M Y') ?? '—' }}</td>
                            <td class="text-right">
                                @if ($subscription->status === \App\Enums\SubscriptionStatus::Active)
                                    <form
                                        method="POST"
                                        action="{{ route('dashboard.subscriptions.cancel', $subscription) }}"
                                        onsubmit="return confirm('Cancel this subscription? Access continues until the end of the period you already paid for.')"
                                    >
                                        @csrf
                                        <button type="submit" class="tag hover:border-accent hover:text-accent">Cancel</button>
                                    </form>
                                @elseif ($subscription->lastOrder)
                                    <a href="{{ route('dashboard.orders.show', $subscription->lastOrder) }}" class="tag hover:border-accent hover:text-accent">
                                        Last order
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-muted">
                                No subscriptions yet. Recurring plans bought at checkout appear here.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{ $subscriptions->links('partials.pagination') }}

    <div class="admin-card mt-8 !p-8">
        <h3 class="mb-4 font-display text-sm tracking-[0.2em] text-white uppercase">About cancelling</h3>
        <p class="text-sm text-body">
            Cancelling here stops future renewals immediately — no further charges are taken. Access continues until the
            end of the period you have already paid for. Refund eligibility is covered in the
            <a href="{{ route('legal.refunds') }}" class="text-accent hover:underline">Cancellation, Refund &amp; Subscription Cancellation Policy</a>.
        </p>
    </div>

    @include('dashboard.partials.end')
@endsection
