@extends('layouts.admin')

@section('title', 'Forms')
@section('heading', 'Forms')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <p class="max-w-2xl text-sm text-muted">
            Build any public form from the admin — questions, choices and sections included.
            Every form lives at <span class="font-mono text-xs text-accent">/forms/[slug]</span>;
            the requirement brief is mirrored at <span class="font-mono text-xs text-accent">/requirements</span>.
        </p>

        <a href="{{ route('admin.forms.create') }}" class="btn btn--accent self-start">
            New Form
            <span class="btn__strip" aria-hidden="true"></span>
        </a>
    </div>

    <div class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Form</th>
                        <th>Public URL</th>
                        <th>Questions</th>
                        <th>Submissions</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($forms as $form)
                        <tr>
                            <td>
                                <a href="{{ route('admin.forms.edit', $form) }}" class="text-white hover:text-accent">
                                    {{ $form->title }}
                                </a>
                                <span class="block text-xs text-muted">{{ $form->reference_prefix }}- reference prefix</span>
                            </td>
                            <td>
                                <a href="{{ route('forms.show', $form) }}" target="_blank" rel="noopener"
                                   class="font-mono text-xs text-accent">/forms/{{ $form->slug }}</a>
                            </td>
                            <td class="text-muted">{{ $form->fields_count }}</td>
                            <td class="text-muted">{{ $form->submissions_count }}</td>
                            <td>
                                <span class="badge {{ $form->is_active ? 'badge--on' : 'badge--off' }}">
                                    {{ $form->is_active ? 'Active' : 'Draft' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.forms.edit', $form) }}" class="text-xs tracking-[0.16em] text-accent uppercase">Edit</a>
                                    <a href="{{ route('admin.submissions.index', ['form' => $form->id]) }}"
                                       class="text-xs tracking-[0.16em] text-muted uppercase hover:text-accent">Inbox</a>
                                    <form action="{{ route('admin.forms.destroy', $form) }}" method="POST"
                                          onsubmit="return confirm('Delete this form with all its questions and submissions?');">
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
                                No forms yet — create your first one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $forms->links('partials.pagination') }}
@endsection
