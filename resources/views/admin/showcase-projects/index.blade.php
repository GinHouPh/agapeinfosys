@extends('layouts.admin')

@section('title', 'Projects')
@section('heading', 'Projects')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ route('admin.showcase-projects.index') }}" class="flex w-full max-w-md flex-wrap gap-2">
            <input
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="Search projects…"
                class="input !border-b-0 !bg-ink !px-4 !py-2.5"
            >

            <select name="status" class="input !border-b-0 !bg-ink !px-4 !py-2.5">
                <option value="">All stages</option>
                @foreach (\App\Enums\ProjectStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected($status === $case)>{{ $case->label() }}</option>
                @endforeach
            </select>

            <button type="submit" class="tag shrink-0 hover:border-accent hover:text-accent">Filter</button>
        </form>

        <a href="{{ route('admin.showcase-projects.create') }}" class="btn btn--accent self-start">
            New Project
            <span class="btn__strip" aria-hidden="true"></span>
        </a>
    </div>

    <div class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Industry</th>
                        <th>Stage</th>
                        <th>Launch</th>
                        <th>Order</th>
                        <th>Visibility</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $project)
                        <tr>
                            <td>
                                <a href="{{ route('admin.showcase-projects.edit', $project) }}" class="text-white hover:text-accent">
                                    {{ $project->title }}
                                </a>
                                <span class="block text-xs text-muted">/{{ $project->slug }}</span>
                            </td>
                            <td class="text-muted">{{ $project->industry }}</td>
                            <td>
                                <span class="badge {{ $project->status->badgeClass() }}">{{ $project->status->label() }}</span>
                            </td>
                            <td class="text-muted">
                                {{ $project->expected_launch?->format('M Y') ?? '—' }}
                            </td>
                            <td class="text-muted">{{ $project->sort_order }}</td>
                            <td>
                                <span class="badge {{ $project->is_active ? 'badge--on' : 'badge--off' }}">
                                    {{ $project->is_active ? 'Visible' : 'Hidden' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.showcase-projects.edit', $project) }}" class="text-xs tracking-[0.16em] text-accent uppercase">Edit</a>
                                    <form action="{{ route('admin.showcase-projects.destroy', $project) }}" method="POST"
                                          onsubmit="return confirm('Delete this project?');">
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
                            <td colspan="7" class="py-10 text-center text-muted">
                                {{ $search || $status ? 'No projects match your filters.' : 'No projects yet — create the first one.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $projects->links('partials.pagination') }}
@endsection
