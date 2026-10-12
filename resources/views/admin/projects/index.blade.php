@extends('layouts.admin')

@section('title', 'Case Studies')
@section('heading', 'Case Studies')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.projects.index') }}" class="flex w-full max-w-sm gap-2">
            <input
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="Search case studies…"
                class="input !border-b-0 !bg-ink !px-4 !py-2.5"
            >
            <button type="submit" class="tag shrink-0 hover:border-accent hover:text-accent">Search</button>
        </form>

        <a href="{{ route('admin.projects.create') }}" class="btn btn--accent self-start">
            New Case Study
            <span class="btn__strip" aria-hidden="true"></span>
        </a>
    </div>

    <div class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Client</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $project)
                        <tr>
                            <td>
                                <a href="{{ route('admin.projects.edit', $project) }}" class="text-white hover:text-accent">
                                    {{ $project->title }}
                                </a>
                                <span class="block text-xs text-muted">/portfolio/{{ $project->slug }}</span>
                            </td>
                            <td class="text-muted">{{ $project->client ?: '—' }}</td>
                            <td class="text-muted">{{ $project->category ?: '—' }}</td>
                            <td>
                                <span class="badge {{ $project->is_published ? 'badge--on' : 'badge--off' }}">
                                    {{ $project->is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap text-muted">{{ $project->updated_at->diffForHumans() }}</td>
                            <td>
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.projects.edit', $project) }}" class="text-xs tracking-[0.16em] text-accent uppercase">Edit</a>
                                    <form action="{{ route('admin.projects.destroy', $project) }}" method="POST"
                                          onsubmit="return confirm('Delete this case study?');">
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
                                {{ $search ? 'No case studies match your search.' : 'No case studies yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $projects->links('partials.pagination') }}
@endsection
