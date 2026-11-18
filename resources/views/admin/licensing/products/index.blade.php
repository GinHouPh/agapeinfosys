@extends('layouts.admin')

@section('title', 'Software Products')
@section('heading', 'Software Products')

@section('content')
    @if (session('status'))
        <div class="mb-6 rounded-none border border-accent/40 bg-accent/10 px-4 py-3 text-sm text-accent">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-none border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-400">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-muted">Register and configure software titles sold via Envato CodeCanyon and Direct checkout.</p>
        </div>

        <a href="{{ route('admin.licensing.products.create') }}" class="btn btn--accent self-start">
            Register Software
            <span class="btn__strip" aria-hidden="true"></span>
        </a>
    </div>

    <div class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Slug</th>
                        <th>Envato Item ID</th>
                        <th>Licenses</th>
                        <th>Releases</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                <a href="{{ route('admin.licensing.products.edit', $product) }}" class="font-medium text-white hover:text-accent">
                                    {{ $product->name }}
                                </a>
                                <span class="block text-xs text-muted">{{ $product->metadata['category'] ?? 'Software' }}</span>
                            </td>
                            <td class="font-mono text-xs text-muted">{{ $product->slug }}</td>
                            <td class="font-mono text-xs text-muted">{{ $product->envato_item_id ?: '—' }}</td>
                            <td>
                                <a href="{{ route('admin.licensing.licenses.index', ['product_id' => $product->id]) }}" class="text-white hover:text-accent">
                                    {{ $product->licenses_count }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.licensing.releases.index', ['product_id' => $product->id]) }}" class="text-white hover:text-accent">
                                    {{ $product->releases_count }}
                                </a>
                            </td>
                            <td>
                                @if ($product->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium border border-emerald-500/30 bg-emerald-500/10 text-emerald-400">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium border border-red-500/30 bg-red-500/10 text-red-400">Inactive</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.licensing.products.edit', $product) }}" class="tag hover:border-accent hover:text-accent">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.licensing.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete this software product?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tag hover:border-red-500 hover:text-red-400">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-8">
                                No software products registered yet. Click &quot;Register Software&quot; above to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($products->hasPages())
            <div class="border-t border-line p-4">
                {{ $products->links() }}
            </div>
        @endif
    </div>
@endsection
