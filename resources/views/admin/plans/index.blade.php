@extends('layouts.admin')

@section('title', 'Plans')
@section('heading', 'Plans')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.plans.index') }}" class="flex w-full max-w-sm gap-2">
            <select name="product" class="input !border-b-0 !bg-ink !px-4 !py-2.5">
                <option value="">All products</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected(request('product') == $product->id)>{{ $product->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="tag shrink-0 hover:border-accent hover:text-accent">Filter</button>
        </form>

        <a href="{{ route('admin.plans.create', request()->has('product') ? ['product' => request('product')] : []) }}" class="btn btn--accent self-start">
            New Plan
            <span class="btn__strip" aria-hidden="true"></span>
        </a>
    </div>

    <div class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Product</th>
                        <th>Billing</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($plans as $plan)
                        <tr>
                            <td>
                                <a href="{{ route('admin.plans.edit', $plan) }}" class="text-white hover:text-accent">
                                    {{ $plan->name }}
                                </a>
                                <span class="block text-xs text-muted">{{ $plan->code }}</span>
                            </td>
                            <td class="text-muted">{{ $plan->product?->name ?? '—' }}</td>
                            <td class="text-muted">
                                @if ($plan->billing_type === \App\Enums\BillingType::Recurring)
                                    Every {{ \App\Enums\BillingType::intervalLabel($plan->interval_days ?? 30) }}
                                @elseif ($plan->isLifetime())
                                    Lifetime
                                @else
                                    {{ $plan->validity_days ?? 365 }} days
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-white">{{ $plan->formattedPrice() }}</td>
                            <td>
                                <span class="badge {{ $plan->is_active ? 'badge--on' : 'badge--off' }}">
                                    {{ $plan->is_active ? 'Active' : 'Hidden' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.plans.edit', $plan) }}" class="text-xs tracking-[0.16em] text-accent uppercase">Edit</a>
                                    <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST"
                                          onsubmit="return confirm('Delete this plan?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs tracking-[0.16em] text-muted uppercase hover:text-accent">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-muted">
                                No plans yet — create the first one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $plans->links('partials.pagination') }}
@endsection
