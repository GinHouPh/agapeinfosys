@extends('layouts.admin')

@section('title', 'Submission '.$submission->reference)
@section('heading', 'Submission')

@section('content')
    <div class="grid gap-6 xl:grid-cols-3">
        <div class="admin-card xl:col-span-2">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-line pb-6">
                <div>
                    <p class="font-mono text-sm text-accent">{{ $submission->reference }}</p>
                    <h2 class="mt-2 !text-2xl text-white">{{ $submission->form->title }}</h2>
                    <p class="mt-2 text-sm text-muted">
                        Received {{ $submission->created_at->format('d M Y, H:i') }}
                    </p>
                </div>

                <form action="{{ route('admin.submissions.status', $submission) }}" method="POST" class="flex items-center gap-3">
                    @csrf
                    @method('PATCH')

                    <select name="status" class="input !border-b-0 !bg-ink !px-4 !py-2.5" onchange="this.form.submit()">
                        @foreach (\App\Enums\SubmissionStatus::cases() as $case)
                            <option value="{{ $case->value }}" @selected($submission->status === $case)>{{ $case->label() }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="space-y-8">
                @foreach ($submission->form->fields as $field)
                    @php $answer = $submission->answerFor($field); @endphp

                    <div>
                        <span class="block font-display text-xs tracking-[0.24em] text-muted uppercase">
                            {{ $field->label }}
                            @if ($field->group)
                                <span class="normal-case">· {{ $field->group }}</span>
                            @endif
                        </span>

                        @if (is_array($submission->answers[$field->name] ?? null) && $answer !== '—')
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($submission->answers[$field->name] as $choice)
                                    <span class="tag">{{ $choice }}</span>
                                @endforeach
                            </div>
                        @elseif ($field->type === \App\Enums\FormFieldType::Textarea && $answer !== '—')
                            <p class="mt-3 whitespace-pre-line text-body">{{ $answer }}</p>
                        @else
                            <p class="mt-3 text-body">{{ $answer }}</p>
                        @endif
                    </div>
                @endforeach

                @php
                    $known = $submission->form->fields->pluck('name')->all();
                    $extra = collect($submission->answers)->except($known);
                @endphp

                @if ($extra->isNotEmpty())
                    <div class="border-t border-line pt-6">
                        <span class="block font-display text-xs tracking-[0.24em] text-muted uppercase">Other Answers</span>
                        @foreach ($extra as $key => $value)
                            <div class="mt-3 flex items-start justify-between gap-4 text-sm">
                                <span class="text-muted">{{ $key }}</span>
                                <span class="text-right text-white">{{ is_array($value) ? implode(', ', $value) : $value }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="mt-8 flex flex-wrap gap-3 border-t border-line pt-6">
                @php $email = collect($submission->answers)->first(fn ($value, $key): bool => str_contains($key, 'email') && is_string($value)); @endphp

                @if ($email)
                    <a href="mailto:{{ $email }}?subject={{ rawurlencode('Re: Your submission '.$submission->reference) }}"
                       class="btn btn--accent">
                        Reply By Email
                        <span class="btn__strip" aria-hidden="true"></span>
                    </a>
                @endif

                <form action="{{ route('admin.submissions.destroy', $submission) }}" method="POST"
                      onsubmit="return confirm('Delete this submission?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn--ghost">Delete</button>
                </form>
            </div>
        </div>

        <aside class="admin-card h-fit space-y-6">
            <div>
                <span class="block font-display text-xs tracking-[0.24em] text-muted uppercase">Form</span>
                <a href="{{ route('admin.submissions.index', ['form' => $submission->form_id]) }}"
                   class="mt-3 block text-lg text-white hover:text-accent">{{ $submission->form->title }}</a>
                <a href="{{ route('forms.show', $submission->form) }}" target="_blank" rel="noopener"
                   class="mt-2 block font-mono text-xs text-accent">/forms/{{ $submission->form->slug }}</a>
            </div>

            <div class="border-t border-line pt-6">
                <span class="block font-display text-xs tracking-[0.24em] text-muted uppercase">Summary</span>

                <div class="mt-3 flex items-center justify-between gap-4 text-sm">
                    <span class="text-muted">Answers</span>
                    <span class="text-white">{{ count($submission->answers ?? []) }}</span>
                </div>
                <div class="mt-3 flex items-center justify-between gap-4 text-sm">
                    <span class="text-muted">Status</span>
                    <span class="badge {{ $submission->status->badgeClass() }}">{{ $submission->status->label() }}</span>
                </div>
            </div>

            <div class="border-t border-line pt-6">
                <a href="{{ route('admin.submissions.index') }}" class="font-display text-xs tracking-[0.24em] text-accent uppercase">
                    &larr; Back to submissions
                </a>
            </div>
        </aside>
    </div>
@endsection
