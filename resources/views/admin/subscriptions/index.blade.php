@extends('layouts.admin')

@section('title', 'Subscriptions')
@section('heading', 'Subscriptions')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="flex w-full max-w-2xl flex-wrap gap-2">
            <input
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="Search account or email…"
                class="input !w-auto !border-b-0 !bg-ink !px-4 !py-2.5"
            >

            <select name="status" class="input !w-auto !border-b-0 !bg-ink !px-4 !py-2.5">
                <option value="">All statuses</option>
                @foreach ($statuses as $option)
                    <option value="{{ $option->value }}" @selected($status === $option->value)>{{ $option->label() }}</option>
                @endforeach
            </select>

            <select name="product" class="input !w-auto !border-b-0 !bg-ink !px-4 !py-2.5">
                <option value="">All products</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected(request('product') == $product->id)>{{ $product->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="tag shrink-0 hover:border-accent hover:text-accent">Filter</button>
        </form>
    </div>

    <div class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Account</th>
                        <th>Product</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Valid until</th>
                        <th>Renews</th>
                        <th>Last order</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subscriptions as $subscription)
                        <tr>
                            <td>
                                <span class="block max-w-[16rem] truncate text-white">{{ $subscription->subscriber_key }}</span>
                                <span class="block text-xs text-muted">{{ $subscription->email ?: '—' }}</span>
                            </td>
                            <td class="text-muted">{{ $subscription->product?->name ?? '—' }}</td>
                            <td class="text-muted">{{ $subscription->plan?->name ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $subscription->status->badgeClass() }}">{{ $subscription->status->label() }}</span>
                            </td>
                            <td class="whitespace-nowrap text-muted">{{ $subscription->ends_at?->format('d M Y') ?? 'No expiry' }}</td>
                            <td class="whitespace-nowrap text-muted">{{ $subscription->renews_at?->format('d M Y') ?? '—' }}</td>
                            <td>
                                @if ($subscription->lastOrder)
                                    <a href="{{ route('admin.orders.show', $subscription->lastOrder) }}" class="font-mono text-xs text-accent hover:underline">
                                        {{ $subscription->lastOrder->reference }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-muted">
                                {{ $search || $status || request('product') ? 'No subscriptions match your filters.' : 'No subscriptions yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $subscriptions->links('partials.pagination') }}
@endsection
