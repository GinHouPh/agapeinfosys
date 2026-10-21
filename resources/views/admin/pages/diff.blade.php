@extends('layouts.admin')

@section('title', 'Compare')
@section('heading', 'Compare Versions')

@section('content')
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <a href="{{ $page->adminRoute('admin.pages.revisions') }}" class="tag hover:border-accent hover:text-accent">
            Page history
        </a>
        <a href="{{ $page->adminRoute('admin.pages.edit') }}" class="tag hover:border-accent hover:text-accent">
            Back to the builder
        </a>
    </div>

    <div class="mb-6 max-w-2xl text-sm text-muted">
        What changed between the version saved on
        <span class="text-white">{{ $revision->created_at?->format('d M Y H:i') }}</span>
        and the layout as it stands now.
    </div>

    <section class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Section</th>
                        <th>Setting</th>
                        <th>Was</th>
                        <th>Now</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($changes as $change)
                        <tr>
                            <td class="font-mono text-xs text-muted">{{ $change['widget'] }}</td>
                            <td class="font-mono text-xs text-accent">{{ $change['setting'] }}</td>
                            <td class="text-muted">{{ $change['before'] === null ? '—' : Str::limit((string) $change['before'], 80) }}</td>
                            <td class="text-body">{{ $change['after'] === null ? '—' : Str::limit((string) $change['after'], 80) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-muted">
                                These two versions are identical.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <form action="{{ $page->adminRoute('admin.pages.restore', ['revision' => $revision->id]) }}" method="POST" class="mt-6"
          onsubmit="return confirm('Restore this version? The current layout will be kept as a version too.');">
        @csrf
        <button type="submit" class="btn btn--accent">
            Restore this version
            <span class="btn__strip" aria-hidden="true"></span>
        </button>
    </form>
@endsection
