@extends('layouts.admin')

@section('title', 'Edit Form')
@section('heading', $form->title)

@section('content')
    <div class="grid gap-6 xl:grid-cols-3">
        <div class="admin-card xl:col-span-2">
            <h2 class="mb-6 border-b border-line pb-4 font-display text-sm tracking-[0.24em] text-white uppercase">
                Form Details
            </h2>

            @include('admin.forms.form')

            <div class="mt-10 flex flex-wrap items-center justify-between gap-4 border-t border-line pt-6">
                <h2 class="font-display text-sm tracking-[0.24em] text-white uppercase">
                    Questions <span class="text-accent">{{ $form->fields_count ?? $form->fields->count() }}</span>
                </h2>

                <a href="{{ route('forms.show', $form) }}" target="_blank" rel="noopener"
                   class="tag hover:border-accent hover:text-accent">
                    View live form
                </a>
            </div>

            <div class="mt-6 space-y-6">
                @forelse ($form->fields as $field)
                    <form action="{{ route('admin.forms.fields.update', [$form, $field]) }}" method="POST"
                          class="border border-line p-5">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="field">
                                <label class="field__label" for="label-{{ $field->id }}">Question *</label>
                                <input class="input" type="text" id="label-{{ $field->id }}" name="label"
                                       value="{{ old('label', $field->label) }}" required>
                                @error('label') <span class="field__error">{{ $message }}</span> @enderror
                            </div>

                            <div class="field">
                                <label class="field__label" for="name-{{ $field->id }}">Key</label>
                                <input class="input" type="text" id="name-{{ $field->id }}" name="name"
                                       value="{{ old('name', $field->name) }}" placeholder="auto from question">
                                <p class="text-sm text-muted">Stored answer key — lowercase letters, digits, underscores.</p>
                                @error('name') <span class="field__error">{{ $message }}</span> @enderror
                            </div>

                            <div class="field">
                                <label class="field__label" for="type-{{ $field->id }}">Type *</label>
                                <select class="input" id="type-{{ $field->id }}" name="type" required>
                                    @foreach (\App\Enums\FormFieldType::cases() as $case)
                                        <option value="{{ $case->value }}" @selected(old('type', $field->type->value) === $case->value)>
                                            {{ $case->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type') <span class="field__error">{{ $message }}</span> @enderror
                            </div>

                            <div class="field">
                                <label class="field__label" for="group-{{ $field->id }}">Section</label>
                                <input class="input" type="text" id="group-{{ $field->id }}" name="group"
                                       value="{{ old('group', $field->group) }}"
                                       placeholder="e.g. Your details" list="group-suggestions">
                                @error('group') <span class="field__error">{{ $message }}</span> @enderror
                            </div>

                            <div class="field sm:col-span-2">
                                <label class="field__label" for="options-{{ $field->id }}">Options</label>
                                <textarea class="input" id="options-{{ $field->id }}" name="options" rows="3"
                                          placeholder="One per line — used by dropdown and choice types">{{ old('options', implode("\n", $field->options ?? [])) }}</textarea>
                                @error('options') <span class="field__error">{{ $message }}</span> @enderror
                            </div>

                            <div class="field">
                                <label class="field__label" for="placeholder-{{ $field->id }}">Placeholder</label>
                                <input class="input" type="text" id="placeholder-{{ $field->id }}" name="placeholder"
                                       value="{{ old('placeholder', $field->placeholder) }}">
                                @error('placeholder') <span class="field__error">{{ $message }}</span> @enderror
                            </div>

                            <div class="field">
                                <label class="field__label" for="help_text-{{ $field->id }}">Help Text</label>
                                <input class="input" type="text" id="help_text-{{ $field->id }}" name="help_text"
                                       value="{{ old('help_text', $field->help_text) }}">
                                @error('help_text') <span class="field__error">{{ $message }}</span> @enderror
                            </div>

                            <div class="field">
                                <label class="field__label" for="width-{{ $field->id }}">Width</label>
                                <select class="input" id="width-{{ $field->id }}" name="width">
                                    <option value="full" @selected(old('width', $field->width) === 'full')>Full row</option>
                                    <option value="half" @selected(old('width', $field->width) === 'half')>Half row</option>
                                </select>
                                @error('width') <span class="field__error">{{ $message }}</span> @enderror
                            </div>

                            <div class="field">
                                <label class="field__label" for="validation-{{ $field->id }}">Extra rules</label>
                                <input class="input" type="text" id="validation-{{ $field->id }}" name="validation"
                                       value="{{ old('validation', implode(', ', $field->validationRules())) }}"
                                       placeholder="min:3, max:120">
                                <p class="text-xs text-muted">Laravel rules, comma separated.</p>
                                @error('validation') <span class="field__error">{{ $message }}</span> @enderror
                            </div>

                            <div class="field">
                                <span class="field__label">Show this question only when</span>
                                <div class="grid gap-3 sm:grid-cols-3">
                                    <select class="input" id="condition_field-{{ $field->id }}" name="condition_field">
                                        <option value="">Always</option>
                                        @foreach ($form->fields as $candidate)
                                            @continue($candidate->id === $field->id)
                                            <option value="{{ $candidate->name }}"
                                                @selected(old('condition_field', $field->condition[0]['field'] ?? null) === $candidate->name)>
                                                {{ $candidate->label }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <select class="input" id="condition_operator-{{ $field->id }}" name="condition_operator">
                                        @foreach (['equals', 'not_equals', 'contains', 'not_contains', 'is_filled', 'is_empty'] as $operator)
                                            <option value="{{ $operator }}"
                                                @selected(old('condition_operator', $field->condition[0]['operator'] ?? null) === $operator)>
                                                {{ str_replace('_', ' ', $operator) }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <input class="input" type="text" id="condition_value-{{ $field->id }}" name="condition_value"
                                           value="{{ old('condition_value', $field->condition[0]['value'] ?? '') }}"
                                           placeholder="value">
                                </div>
                                @error('condition_field') <span class="field__error">{{ $message }}</span> @enderror
                            </div>

                            @if ($field->type->isFile())
                                <div class="field sm:col-span-2">
                                    <span class="field__label">Uploads</span>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <input class="input" type="text" id="file_mimes-{{ $field->id }}" name="file_mimes"
                                               value="{{ old('file_mimes', implode(', ', $field->acceptedMimes())) }}"
                                               placeholder="pdf, doc, docx">
                                        <input class="input" type="number" id="file_max_kilobytes-{{ $field->id }}" name="file_max_kilobytes"
                                               value="{{ old('file_max_kilobytes', $field->maxUploadKilobytes()) }}" min="1" max="204800">
                                    </div>
                                    <p class="text-xs text-muted">Accepted types, comma separated, and the size limit in kilobytes.</p>
                                    @error('file_mimes') <span class="field__error">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            <div class="field">
                                <span class="field__label">Required</span>
                                <label class="flex cursor-pointer items-center gap-3 border border-line p-3 transition-colors duration-300 hover:border-accent has-[:checked]:border-accent">
                                    <input type="checkbox" name="is_required" value="1" class="h-4 w-4 accent-accent"
                                           @checked(old('is_required', $field->is_required))>
                                    <span class="text-sm text-white">Must be answered</span>
                                </label>
                            </div>

                            <div class="field">
                                <span class="field__label">Order</span>
                                <p class="border border-line px-4 py-3 font-mono text-sm text-muted">#{{ $field->sort_order }}</p>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap items-center gap-4 border-t border-line pt-4">
                            <button type="submit" class="tag hover:border-accent hover:text-accent">Save Question</button>

                            <button type="submit" form="delete-field-{{ $field->id }}"
                                    class="text-xs tracking-[0.16em] text-muted uppercase hover:text-accent">
                                Delete
                            </button>
                        </div>
                    </form>

                    <form action="{{ route('admin.forms.fields.destroy', [$form, $field]) }}" method="POST"
                          id="delete-field-{{ $field->id }}"
                          onsubmit="return confirm('Delete this question?');">
                        @csrf
                        @method('DELETE')
                    </form>
                @empty
                    <p class="border border-dashed border-line p-6 text-center text-muted">
                        No questions yet — add the first one below.
                    </p>
                @endforelse
            </div>

            <div class="mt-10 border border-accent/40 p-5">
                <h3 class="mb-5 font-display text-sm tracking-[0.24em] text-white uppercase">
                    <span class="text-accent">+</span> Add Question
                </h3>

                <form action="{{ route('admin.forms.fields.store', $form) }}" method="POST" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @csrf

                    <div class="field">
                        <label class="field__label" for="new-label">Question *</label>
                        <input class="input" type="text" id="new-label" name="label" value="{{ old('label') }}" required>
                        @error('label') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="new-name">Key</label>
                        <input class="input" type="text" id="new-name" name="name" value="{{ old('name') }}" placeholder="auto from question">
                        @error('name') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="new-type">Type *</label>
                        <select class="input" id="new-type" name="type" required>
                            @foreach (\App\Enums\FormFieldType::cases() as $case)
                                <option value="{{ $case->value }}" @selected(old('type') === $case->value)>{{ $case->label() }}</option>
                            @endforeach
                        </select>
                        @error('type') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="new-group">Section</label>
                        <input class="input" type="text" id="new-group" name="group" value="{{ old('group') }}"
                               placeholder="e.g. Your details" list="group-suggestions">
                        @error('group') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field sm:col-span-2">
                        <label class="field__label" for="new-options">Options</label>
                        <textarea class="input" id="new-options" name="options" rows="3"
                                  placeholder="One per line — used by dropdown and choice types">{{ old('options') }}</textarea>
                        @error('options') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="new-placeholder">Placeholder</label>
                        <input class="input" type="text" id="new-placeholder" name="placeholder" value="{{ old('placeholder') }}">
                        @error('placeholder') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="new-help_text">Help Text</label>
                        <input class="input" type="text" id="new-help_text" name="help_text" value="{{ old('help_text') }}">
                        @error('help_text') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <span class="field__label">Required</span>
                        <label class="flex cursor-pointer items-center gap-3 border border-line p-3 transition-colors duration-300 hover:border-accent has-[:checked]:border-accent">
                            <input type="checkbox" name="is_required" value="1" class="h-4 w-4 accent-accent"
                                   @checked(old('is_required'))>
                            <span class="text-sm text-white">Must be answered</span>
                        </label>
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="btn btn--accent">
                            Add Question
                            <span class="btn__strip" aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <aside class="admin-card h-fit space-y-6">
            <div>
                <span class="block font-display text-xs tracking-[0.24em] text-muted uppercase">Publishing</span>

                <div class="mt-3 flex items-center justify-between gap-4 text-sm">
                    <span class="text-muted">Status</span>
                    <span class="badge {{ $form->is_active ? 'badge--on' : 'badge--off' }}">
                        {{ $form->is_active ? 'Active' : 'Draft' }}
                    </span>
                </div>
                <div class="mt-3 flex items-center justify-between gap-4 text-sm">
                    <span class="text-muted">Submissions</span>
                    <a href="{{ route('admin.submissions.index', ['form' => $form->id]) }}" class="text-accent hover:underline">
                        {{ $form->submissions_count ?? $form->submissions()->count() }} in inbox
                    </a>
                </div>
            </div>

            <div class="border-t border-line pt-6">
                <span class="block font-display text-xs tracking-[0.24em] text-muted uppercase">Public Address</span>
                <a href="{{ route('forms.show', $form) }}" target="_blank" rel="noopener"
                   class="mt-3 block font-mono text-sm text-accent">/forms/{{ $form->slug }}</a>

                @if ($form->slug === \App\Http\Controllers\FormController::REQUIREMENT_FORM_SLUG)
                    <a href="{{ route('requirements.create') }}" target="_blank" rel="noopener"
                       class="mt-2 block font-mono text-sm text-accent">/requirements</a>
                @endif
            </div>

            <div class="border-t border-line pt-6">
                <a href="{{ route('admin.forms.index') }}" class="font-display text-xs tracking-[0.24em] text-accent uppercase">
                    &larr; Back to forms
                </a>
            </div>
        </aside>
    </div>

    <datalist id="group-suggestions">
        @foreach ($form->fields->pluck('group')->filter()->unique()->sort() as $group)
            <option value="{{ $group }}"></option>
        @endforeach
    </datalist>
@endsection
