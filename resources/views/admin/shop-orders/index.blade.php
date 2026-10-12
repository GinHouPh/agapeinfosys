@extends('layouts.admin')

@section('title', 'Shop orders')
@section('heading', 'Shop orders')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ route('admin.shop-orders.index') }}" class="flex w-full max-w-2xl flex-wrap gap-2">
            <input
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="Search reference, email or account…"
                class="input !w-auto !border-b-0 !bg-ink !px-4 !py-2.5"
            >

            <select name="status" class="input !w-auto !border-b-0 !bg-ink !px-4 !py-2.5">
                <option value="">All statuses</option>
                @foreach ($statuses as $option)
                    <option value="{{ $option->value }}" @selected($status === $option->value)>{{ $option->label() }}</option>
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
                        <th>Reference</th>
                        <th>Items</th>
                        <th>Account</th>
                        <th>Amount</th>
                        <th>Gateway</th>
                        <th>Status</th>
                        <th>Paid</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.shop-orders.show', $order) }}" class="font-mono text-xs text-white hover:text-accent">
                                    {{ $order->reference }}
                                </a>
                                <span class="block text-xs text-muted">{{ $order->created_at->format('d M Y, H:i') }}</span>
                            </td>
                            <td class="text-muted">
                                <span class="block max-w-[16rem] truncate">{{ $order->items_count }} plan(s)</span>
                            </td>
                            <td class="text-muted">
                                <span class="block max-w-[16rem] truncate">{{ $order->subscriber_key }}</span>
                                <span class="block text-xs">{{ $order->email ?: '—' }}</span>
                            </td>
                            <td class="whitespace-nowrap text-white">{{ $order->formattedAmount() }}</td>
                            <td class="text-muted">{{ $order->gateway?->name ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span>
                            </td>
                            <td class="whitespace-nowrap text-muted">{{ $order->paid_at?->format('d M Y, H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-muted">
                                {{ $search || $status ? 'No shop orders match your filters.' : 'No shop orders yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $orders->links('partials.pagination') }}
@endsection
