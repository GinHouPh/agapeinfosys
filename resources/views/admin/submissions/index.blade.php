@extends('layouts.admin')

@section('title', 'Submissions')
@section('heading', 'Submissions')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ route('admin.submissions.index') }}" class="flex w-full max-w-2xl flex-wrap gap-2">
            <input
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="Search reference or answers…"
                class="input !border-b-0 !bg-ink !px-4 !py-2.5"
            >

            <select name="form" class="input !border-b-0 !bg-ink !px-4 !py-2.5">
                <option value="">All forms</option>
                @foreach ($forms as $form)
                    <option value="{{ $form->id }}" @selected($formId === $form->id)>{{ $form->title }}</option>
                @endforeach
            </select>

            <select name="status" class="input !border-b-0 !bg-ink !px-4 !py-2.5">
                <option value="">All statuses</option>
                @foreach (\App\Enums\SubmissionStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected($status === $case)>{{ $case->label() }}</option>
                @endforeach
            </select>

            <button type="submit" class="tag shrink-0 hover:border-accent hover:text-accent">Filter</button>
        </form>

        <a href="{{ route('admin.forms.index') }}" class="tag hover:border-accent hover:text-accent">Manage Forms</a>
    </div>

    <div class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Form</th>
                        <th>Answers</th>
                        <th>Status</th>
                        <th>Received</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($submissions as $submission)
                        <tr>
                            <td>
                                <a href="{{ route('admin.submissions.show', $submission) }}" class="font-mono text-xs text-accent">
                                    {{ $submission->reference }}
                                </a>
                            </td>
                            <td class="text-muted">{{ $submission->form->title }}</td>
                            <td>
                                <span class="text-white">{{ $submission->answers['name'] ?? $submission->answers['email'] ?? '—' }}</span>
                                <span class="block max-w-xs truncate text-xs text-muted">
                                    {{ collect($submission->answers)->filter(fn ($value) => is_scalar($value) && $value !== '')->take(3)->implode(' · ') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $submission->status->badgeClass() }}">{{ $submission->status->label() }}</span>
                            </td>
                            <td class="text-muted">{{ $submission->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.submissions.show', $submission) }}" class="text-xs tracking-[0.16em] text-accent uppercase">View</a>
                                    <form action="{{ route('admin.submissions.destroy', $submission) }}" method="POST"
                                          onsubmit="return confirm('Delete this submission?');">
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
                                {{ $search || $status || $formId ? 'No submissions match your filters.' : 'No submissions yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $submissions->links('partials.pagination') }}
@endsection
