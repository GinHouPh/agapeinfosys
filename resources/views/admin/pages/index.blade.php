@extends('layouts.admin')

@section('title', 'Pages')
@section('heading', 'Pages')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <p class="max-w-2xl text-sm text-muted">
            Build landing pages from widgets in the visual canvas. A page published with the slug
            <span class="font-mono text-xs text-accent">home</span>,
            <span class="font-mono text-xs text-accent">about</span> or
            <span class="font-mono text-xs text-accent">services</span>
            replaces that flagship route; every other page lives at
            <span class="font-mono text-xs text-accent">/pages/[slug]</span>.
        </p>

        <a href="{{ route('admin.pages.create') }}" class="btn btn--accent self-start">
            New Page
            <span class="btn__strip" aria-hidden="true"></span>
        </a>
    </div>

    <div class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Page</th>
                        <th>Public URL</th>
                        <th>Sections</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pages as $page)
                        @php
                            $flagship = ['home', 'about', 'services'];
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.pages.edit', $page) }}" class="text-white hover:text-accent">
                                    {{ $page->title }}
                                </a>
                                @if (in_array($page->slug, $flagship, true))
                                    <span class="tag ml-2">Flagship</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ in_array($page->slug, $flagship, true) ? route($page->slug === 'services' ? 'services.index' : $page->slug) : route('pages.show', $page) }}"
                                   target="_blank" rel="noopener"
                                   class="font-mono text-xs text-accent">
                                    /{{ in_array($page->slug, $flagship, true) ? $page->slug : 'pages/'.$page->slug }}
                                </a>
                            </td>
                            <td class="text-muted">{{ count($page->blocks()) }}</td>
                            <td>
                                <span class="badge {{ $page->status->badgeClass() }}">
                                    {{ $page->status->label() }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $page->updated_at?->diffForHumans() }}</td>
                            <td>
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="text-xs tracking-[0.16em] text-accent uppercase">Edit</a>
                                    <form action="{{ route('admin.pages.destroy', $page) }}" method="POST"
                                          onsubmit="return confirm('Delete this page?');">
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
                                No pages yet — create your first one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $pages->links('partials.pagination') }}
@endsection
