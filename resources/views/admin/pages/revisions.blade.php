@extends('layouts.admin')

@section('title', 'History')
@section('heading', 'Page History')

@section('content')
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <a href="{{ $page->adminRoute('admin.pages.edit') }}" class="tag hover:border-accent hover:text-accent">
            Back to the builder
        </a>
        <a href="{{ route('admin.pages.index') }}" class="tag hover:border-accent hover:text-accent">All pages</a>
    </div>

    <div class="mb-6 max-w-2xl text-sm text-muted">
        Every change to the layout keeps the version it replaced. The last 20 are kept.
    </div>

    <section class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Version</th>
                        <th>Sections</th>
                        <th>By</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($revisions as $revision)
                        <tr>
                            <td class="whitespace-nowrap text-muted">
                                {{ $revision->created_at?->format('d M Y H:i') }}
                            </td>
                            <td class="text-body">{{ $revision->title }}</td>
                            <td class="text-muted">{{ count($revision->schema['blocks'] ?? []) }}</td>
                            <td class="text-muted">{{ $revision->creator?->name ?? '—' }}</td>
                            <td>
                                <div class="flex justify-end gap-3">
                                    <a href="{{ $page->adminRoute('admin.pages.diff', ['revision' => $revision->id]) }}"
                                       class="text-xs tracking-[0.16em] text-accent uppercase">Compare</a>
                                    <form action="{{ $page->adminRoute('admin.pages.restore', ['revision' => $revision->id]) }}" method="POST"
                                          onsubmit="return confirm('Restore this version? The current layout will be kept as a version too.');">
                                        @csrf
                                        <button type="submit" class="text-xs tracking-[0.16em] text-muted uppercase hover:text-accent">
                                            Restore
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-muted">
                                No saved versions yet — they appear as the layout changes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
