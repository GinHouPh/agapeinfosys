@extends('layouts.admin')

@section('title', 'Activity Log')
@section('heading', 'Activity Log')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <p class="max-w-2xl text-sm text-muted">
            Every change an admin makes in the control panel, newest first.
        </p>

        <form method="GET" action="{{ route('admin.activity.index') }}" class="flex flex-wrap items-center gap-3">
            <select class="input" name="action" aria-label="Filter by action">
                <option value="">All actions</option>
                @foreach ($actions as $option)
                    <option value="{{ $option }}" @selected($action === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>

            <button type="submit" class="tag hover:border-accent hover:text-accent">Filter</button>

            @if ($action !== '')
                <a href="{{ route('admin.activity.index') }}" class="tag hover:border-accent hover:text-accent">Clear</a>
            @endif
        </form>
    </div>

    <div class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Admin</th>
                        <th>Action</th>
                        <th>Detail</th>
                        <th>Subject</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td class="whitespace-nowrap text-muted">{{ $entry->created_at?->format('d M Y H:i') }}</td>
                            <td class="text-body">{{ $entry->user?->name ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $entry->action === 'deleted' ? 'badge--off' : 'badge--on' }}">
                                    {{ ucfirst($entry->action) }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $entry->description }}</td>
                            <td class="font-mono text-xs text-muted">
                                {{ $entry->subject_type ? class_basename($entry->subject_type).' #'.$entry->subject_id : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-muted">
                                Nothing recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $entries->links('partials.pagination') }}
@endsection
