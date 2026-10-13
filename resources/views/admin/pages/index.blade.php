@extends('layouts.admin')

@section('title', 'Pages')
@section('heading', 'Pages')

@section('content')
    @php
        $flagshipSlugs = array_keys(\App\Models\Page::flagshipRoutes());
        $templateSlugs = array_keys(\App\Models\Page::recordTemplates());
    @endphp

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <p class="max-w-2xl text-sm text-muted">
            Build pages from widgets in the visual canvas. A page published with a reserved slug
            takes over that route —
            @foreach ($flagshipSlugs as $index => $slug)
                <span class="font-mono text-xs text-accent">{{ $slug }}</span>@if (! $loop->last), @endif
            @endforeach
            for the marketing pages, and
            @foreach ($templateSlugs as $index => $slug)
                <span class="font-mono text-xs text-accent">{{ $slug }}</span>@if (! $loop->last), @endif
            @endforeach
            for every record detail page. Anything else lives at
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
                        <tr>
                            <td>
                                <a href="{{ route('admin.pages.edit', $page) }}" class="text-white hover:text-accent">
                                    {{ $page->title }}
                                </a>
                                @if ($page->isFlagship())
                                    <span class="tag ml-2">Flagship</span>
                                @elseif ($page->template() !== null)
                                    <span class="tag ml-2">Template</span>
                                @endif
                            </td>
                            <td>
                                @if ($page->targetUrl() !== null)
                                    <a href="{{ $page->targetUrl() }}"
                                       target="_blank" rel="noopener"
                                       class="font-mono text-xs text-accent">
                                        {{ $page->targetPath() }}
                                    </a>
                                @else
                                    <span class="font-mono text-xs text-muted">{{ $page->targetPath() }}</span>
                                @endif
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
