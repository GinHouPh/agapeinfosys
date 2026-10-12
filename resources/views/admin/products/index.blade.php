@extends('layouts.admin')

@section('title', 'Products')
@section('heading', 'Products')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.products.index') }}" class="flex w-full max-w-sm gap-2">
            <input
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="Search products…"
                class="input !border-b-0 !bg-ink !px-4 !py-2.5"
            >
            <button type="submit" class="tag shrink-0 hover:border-accent hover:text-accent">Search</button>
        </form>

        <a href="{{ route('admin.products.create') }}" class="btn btn--accent self-start">
            New Product
            <span class="btn__strip" aria-hidden="true"></span>
        </a>
    </div>

    <div class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Domain</th>
                        <th>API Key</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                <a href="{{ route('admin.products.edit', $product) }}" class="text-white hover:text-accent">
                                    {{ $product->name }}
                                </a>
                                <span class="block text-xs text-muted">/{{ $product->slug }}</span>
                            </td>
                            <td class="text-muted">{{ $product->domain ?: '—' }}</td>
                            <td class="font-mono text-xs text-muted">{{ Str::limit($product->api_key, 14) }}</td>
                            <td class="text-muted">{{ $product->sort_order }}</td>
                            <td>
                                <span class="badge {{ $product->is_active ? 'badge--on' : 'badge--off' }}">
                                    {{ $product->is_active ? 'Active' : 'Hidden' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.plans.index', ['product' => $product->id]) }}" class="text-xs tracking-[0.16em] text-accent uppercase">Plans</a>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="text-xs tracking-[0.16em] text-accent uppercase">Edit</a>
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                                          onsubmit="return confirm('Delete this product and all of its plans, orders and subscriptions?');">
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
                                {{ $search ? 'No products match your search.' : 'No products yet — create the first one.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $products->links('partials.pagination') }}
@endsection
