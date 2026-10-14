@extends('layouts.app')

@section('title', 'My Orders')
@section('description', 'Every order placed with your client account, with receipts and payment status.')

@section('content')
    @include('dashboard.partials.nav', [
        'title' => 'My Orders',
        'lead' => 'Every order placed with your email address, newest first. Open one for the full receipt.',
    ])

    <section class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Product</th>
                        <th>Plan</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Placed</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('dashboard.orders.show', $order) }}" class="font-mono text-xs text-white hover:text-accent">
                                    {{ $order->reference }}
                                </a>
                            </td>
                            <td class="text-body">{{ $order->product?->name ?? '—' }}</td>
                            <td class="text-muted">{{ $order->plan?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap text-white">{{ $order->formattedAmount() }}</td>
                            <td><span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span></td>
                            <td class="whitespace-nowrap text-muted">{{ $order->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-muted">
                                No orders yet. Anything you buy shows up here the moment payment confirms —
                                see the <a href="{{ route('legal.delivery') }}" class="text-accent hover:underline">Delivery Policy</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{ $orders->links('partials.pagination') }}

    @include('dashboard.partials.end')
@endsection
