@extends('layouts.admin')

@section('title', 'Services')
@section('heading', 'Services')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.services.index') }}" class="flex w-full max-w-sm gap-2">
            <input
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="Search services…"
                class="input !border-b-0 !bg-ink !px-4 !py-2.5"
            >
            <button type="submit" class="tag shrink-0 hover:border-accent hover:text-accent">Search</button>
        </form>

        <a href="{{ route('admin.services.create') }}" class="btn btn--accent self-start">
            New Service
            <span class="btn__strip" aria-hidden="true"></span>
        </a>
    </div>

    <div class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Order</th>
                        <th>Featured</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($services as $service)
                        <tr>
                            <td>
                                <a href="{{ route('admin.services.edit', $service) }}" class="text-white hover:text-accent">
                                    {{ $service->title }}
                                </a>
                                <span class="block text-xs text-muted">/services/{{ $service->slug }}</span>
                            </td>
                            <td class="text-muted">{{ $service->sort_order }}</td>
                            <td>{{ $service->is_featured ? 'Yes' : '—' }}</td>
                            <td>
                                <span class="badge {{ $service->is_published ? 'badge--on' : 'badge--off' }}">
                                    {{ $service->is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap text-muted">{{ $service->updated_at->diffForHumans() }}</td>
                            <td>
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.services.edit', $service) }}" class="text-xs tracking-[0.16em] text-accent uppercase">Edit</a>
                                    <a href="{{ route('admin.seo.edit', ['type' => 'service', 'id' => $service->id]) }}"
                                       class="text-xs tracking-[0.16em] text-muted uppercase hover:text-accent">SEO</a>
                                    <form action="{{ route('admin.services.destroy', $service) }}" method="POST"
                                          onsubmit="return confirm('Delete this service?');">
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
                                {{ $search ? 'No services match your search.' : 'No services yet — create the first one.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $services->links('partials.pagination') }}
@endsection
