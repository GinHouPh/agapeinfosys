@extends('layouts.admin')

@section('title', 'Requirements')
@section('heading', 'Requirements')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ route('admin.requirements.index') }}" class="flex w-full max-w-md flex-wrap gap-2">
            <input
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="Search reference, name, email…"
                class="input !border-b-0 !bg-ink !px-4 !py-2.5"
            >

            <select name="status" class="input !border-b-0 !bg-ink !px-4 !py-2.5">
                <option value="">All statuses</option>
                @foreach (\App\Enums\RequirementStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected($status === $case)>{{ $case->label() }}</option>
                @endforeach
            </select>

            <button type="submit" class="tag shrink-0 hover:border-accent hover:text-accent">Filter</button>
        </form>
    </div>

    <div class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Client</th>
                        <th>Project</th>
                        <th>Industry</th>
                        <th>Budget</th>
                        <th>Status</th>
                        <th>Received</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($submissions as $submission)
                        <tr>
                            <td>
                                <a href="{{ route('admin.requirements.show', $submission) }}" class="font-mono text-xs text-accent">
                                    {{ $submission->reference }}
                                </a>
                            </td>
                            <td>
                                <span class="text-white">{{ $submission->name }}</span>
                                <span class="block text-xs text-muted">{{ $submission->company ?: $submission->email }}</span>
                            </td>
                            <td class="text-muted">{{ $submission->project_name }}</td>
                            <td class="text-muted">{{ $submission->industry }}</td>
                            <td class="text-muted">{{ $submission->budget }}</td>
                            <td>
                                <span class="badge {{ $submission->status->badgeClass() }}">{{ $submission->status->label() }}</span>
                            </td>
                            <td class="text-muted">{{ $submission->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.requirements.show', $submission) }}" class="text-xs tracking-[0.16em] text-accent uppercase">View</a>
                                    <form action="{{ route('admin.requirements.destroy', $submission) }}" method="POST"
                                          onsubmit="return confirm('Delete this requirement?');">
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
                            <td colspan="8" class="py-10 text-center text-muted">
                                {{ $search || $status ? 'No requirements match your filters.' : 'No requirement briefs yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $submissions->links('partials.pagination') }}
@endsection
