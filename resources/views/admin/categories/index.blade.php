@extends('layouts.admin')

@section('title', 'Categories')
@section('heading', 'Categories')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ route('admin.categories.index') }}" class="flex w-full max-w-md gap-2">
            <input
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="Search categories…"
                class="input !border-b-0 !bg-ink !px-4 !py-2.5"
            >

            <button type="submit" class="tag shrink-0 hover:border-accent hover:text-accent">Filter</button>
        </form>

        <a href="{{ route('admin.categories.create') }}" class="btn btn--accent self-start">
            New Category
            <span class="btn__strip" aria-hidden="true"></span>
        </a>
    </div>

    <div class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Products</th>
                        <th>Order</th>
                        <th>Visibility</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        @php($category = $row['category'])
                        <tr>
                            <td>
                                <a href="{{ route('admin.categories.edit', $category) }}" class="text-white hover:text-accent">
                                    @if ($row['depth'] > 0)
                                        <span class="text-muted">{{ str_repeat('— ', $row['depth']) }}</span>
                                    @endif
                                    {{ $category->name }}
                                </a>
                                <span class="block text-xs text-muted">
                                    @if ($category->parent)
                                        {{ $category->parent->name }} /
                                    @endif
                                    /{{ $category->slug }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $category->products_count }}</td>
                            <td class="text-muted">{{ $category->sort_order }}</td>
                            <td>
                                <span class="badge {{ $category->is_active ? 'badge--on' : 'badge--off' }}">
                                    {{ $category->is_active ? 'Visible' : 'Hidden' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="text-xs tracking-[0.16em] text-accent uppercase">Edit</a>
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST"
                                          onsubmit="return confirm('Delete this category?');">
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
                            <td colspan="5" class="py-10 text-center text-muted">
                                {{ $search ? 'No categories match your search.' : 'No categories yet — create the first one.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
