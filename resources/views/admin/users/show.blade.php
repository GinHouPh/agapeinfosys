@extends('layouts.admin')

@section('title', $user->name)
@section('heading', 'Client detail')

@section('content')
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
            <span class="block font-display text-lg text-white">
                @forelse ($lifetimeSpend as $currency => $total)
                    <span class="block">{{ $total }}</span>
                @empty
                    <span class="text-5xl">—</span>
                @endforelse
            </span>
            <span class="mt-3 block font-display text-xs tracking-[0.24em] text-muted uppercase">Lifetime spend</span>
        </div>
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <section class="admin-card !p-0">
                <div class="flex items-center justify-between gap-4 px-6 pt-6">
                    <h3 class="font-display text-sm tracking-[0.2em] text-white uppercase">Orders</h3>
                    <a href="{{ route('admin.orders.index', ['q' => $user->email]) }}" class="text-xs tracking-[0.2em] text-accent uppercase">
                        All orders
                    </a>
                </div>

                <div class="overflow-x-auto p-6">
                    @if ($recentOrders->isEmpty())
                        <p class="text-muted">No orders placed with this email address yet.</p>
                    @else
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Product</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Paid</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-xs text-white hover:text-accent">
                                                {{ $order->reference }}
                                            </a>
                                        </td>
                                        <td class="text-body">
                                            {{ $order->product?->name ?? '—' }}
                                            <span class="block text-xs text-muted">{{ $order->plan?->name ?? '—' }}</span>
                                        </td>
                                        <td class="whitespace-nowrap text-white">{{ $order->formattedAmount() }}</td>
                                        <td><span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span></td>
                                        <td class="whitespace-nowrap text-muted">{{ $order->paid_at?->format('d M Y, H:i') ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>

            <section class="admin-card !p-0">
                <div class="flex items-center justify-between gap-4 px-6 pt-6">
                    <h3 class="font-display text-sm tracking-[0.2em] text-white uppercase">Subscriptions</h3>
                    <a href="{{ route('admin.subscriptions.index', ['q' => $user->email]) }}" class="text-xs tracking-[0.2em] text-accent uppercase">
                        All subscriptions
                    </a>
                </div>

                <div class="overflow-x-auto p-6">
                    @if ($recentSubscriptions->isEmpty())
                        <p class="text-muted">No subscriptions held under this email address yet.</p>
                    @else
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Plan</th>
                                    <th>Status</th>
                                    <th>Valid until</th>
                                    <th>Renews</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentSubscriptions as $subscription)
                                    <tr>
                                        <td class="text-white">{{ $subscription->product?->name ?? '—' }}</td>
                                        <td class="text-muted">{{ $subscription->plan?->name ?? '—' }}</td>
                                        <td><span class="badge {{ $subscription->status->badgeClass() }}">{{ $subscription->status->label() }}</span></td>
                                        <td class="whitespace-nowrap text-muted">{{ $subscription->ends_at?->format('d M Y') ?? 'No expiry' }}</td>
                                        <td class="whitespace-nowrap text-muted">{{ $subscription->renews_at?->format('d M Y') ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <div class="admin-card space-y-4 !p-6">
                <h3 class="font-display text-sm tracking-[0.2em] text-white uppercase">Account</h3>

                <dl class="space-y-3 text-sm">
                    <div class="flex items-center gap-2">
                        <dt class="w-28 text-muted">Name</dt>
                        <dd class="text-white">{{ $user->name }}</dd>
                    </div>
                    <div class="flex items-center gap-2">
                        <dt class="w-28 text-muted">Email</dt>
                        <dd class="break-all text-white">{{ $user->email }}</dd>
                    </div>
                    <div class="flex items-center gap-2">
                        <dt class="w-28 text-muted">Phone</dt>
                        <dd class="text-white">{{ $user->phone ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center gap-2">
                        <dt class="w-28 text-muted">Role</dt>
                        <dd>
                            <span class="badge {{ $user->is_admin ? 'badge--on' : 'badge--off' }}">
                                {{ $user->is_admin ? 'Admin' : 'Client' }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex items-center gap-2">
                        <dt class="w-28 text-muted">Verified</dt>
                        <dd class="text-white">{{ $user->email_verified_at?->format('d M Y') ?? 'Not verified' }}</dd>
                    </div>
                    <div class="flex items-center gap-2">
                        <dt class="w-28 text-muted">Joined</dt>
                        <dd class="text-white">{{ $user->created_at->format('d M Y') }}</dd>
                    </div>
                </dl>

                <div class="flex flex-col gap-3 border-t border-line pt-6">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn--accent w-full">
                        Edit Account
                        <span class="btn__strip" aria-hidden="true"></span>
                    </a>

                    <form
                        method="POST"
                        action="{{ route('admin.users.destroy', $user) }}"
                        onsubmit="return confirm('Delete this client account? Their orders stay on record.')"
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn--ghost w-full">Delete Account</button>
                    </form>
                </div>
            </div>

            <div class="border-t border-line pt-6">
                <a href="{{ route('admin.users.index') }}" class="font-display text-xs tracking-[0.24em] text-accent uppercase">
                    &larr; Back to clients
                </a>
            </div>
        </aside>
    </div>
@endsection
