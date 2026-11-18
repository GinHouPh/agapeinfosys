@extends('layouts.admin')

@section('title', 'Edit Software Product')
@section('heading', 'Edit Software Product')

@section('content')
    <div class="max-w-2xl">
        <form action="{{ route('admin.licensing.products.update', $product) }}" method="POST" class="admin-card space-y-6 !p-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-muted">Product Name</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name', $product->name) }}"
                    required
                    class="input mt-1 w-full"
                >
                @error('name')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="slug" class="block text-xs font-semibold uppercase tracking-wider text-muted">Slug (Identifier)</label>
                    <input
                        type="text"
                        name="slug"
                        id="slug"
                        value="{{ old('slug', $product->slug) }}"
                        required
                        class="input mt-1 w-full font-mono text-sm"
                    >
                    @error('slug')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="envato_item_id" class="block text-xs font-semibold uppercase tracking-wider text-muted">Envato Item ID</label>
                    <input
                        type="text"
                        name="envato_item_id"
                        id="envato_item_id"
                        value="{{ old('envato_item_id', $product->envato_item_id) }}"
                        class="input mt-1 w-full font-mono text-sm"
                    >
                    @error('envato_item_id')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="category" class="block text-xs font-semibold uppercase tracking-wider text-muted">Category</label>
                    <input
                        type="text"
                        name="category"
                        id="category"
                        value="{{ old('category', $product->metadata['category'] ?? 'ai-saas') }}"
                        class="input mt-1 w-full text-sm"
                    >
                </div>

                <div>
                    <label for="currency" class="block text-xs font-semibold uppercase tracking-wider text-muted">Default Currency</label>
                    <input
                        type="text"
                        name="currency"
                        id="currency"
                        value="{{ old('currency', $product->metadata['currency'] ?? 'USD') }}"
                        class="input mt-1 w-full text-sm"
                    >
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <input
                    type="checkbox"
                    name="is_active"
                    id="is_active"
                    value="1"
                    @checked(old('is_active', $product->is_active))
                    class="rounded border-line bg-ink text-accent focus:ring-0"
                >
                <label for="is_active" class="text-sm text-white">Active (Accepts license activations and update checks)</label>
            </div>

            <div class="flex items-center justify-between border-t border-line pt-6">
                <a href="{{ route('admin.licensing.products.index') }}" class="tag hover:border-line hover:text-white">
                    Cancel
                </a>

                <button type="submit" class="btn btn--accent">
                    Update Product
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>
            </div>
        </form>
    </div>
@endsection
