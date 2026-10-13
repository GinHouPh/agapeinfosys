{{--
    Shared form-meta editor. Expects: $form (App\Models\Form, unsaved for create)
--}}
@php
    $isEdit = $form->exists;
    $action = $isEdit ? route('admin.forms.update', $form) : route('admin.forms.store');
@endphp

<form action="{{ $action }}" method="POST" class="space-y-6">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="grid gap-6 sm:grid-cols-2">
        <div class="field">
            <label class="field__label" for="title">Title *</label>
            <input class="input" type="text" id="title" name="title" value="{{ old('title', $form->title) }}"
                   placeholder="e.g. Partner Application" required>
            @error('title') <span class="field__error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label class="field__label" for="slug">Slug</label>
            <input class="input" type="text" id="slug" name="slug" value="{{ old('slug', $form->slug) }}"
                   placeholder="Generated from the title when blank">
            <p class="text-sm text-muted">Public address: <span class="font-mono text-xs text-accent">/forms/[slug]</span></p>
            @error('slug') <span class="field__error">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="field">
        <label class="field__label" for="description">Introduction</label>
        <textarea class="input" id="description" name="description" rows="3"
                  placeholder="A sentence or two shown above the questions">{{ old('description', $form->description) }}</textarea>
        @error('description') <span class="field__error">{{ $message }}</span> @enderror
    </div>

    <div class="grid gap-6 sm:grid-cols-3">
        <div class="field">
            <label class="field__label" for="submit_label">Submit Button *</label>
            <input class="input" type="text" id="submit_label" name="submit_label"
                   value="{{ old('submit_label', $form->submit_label ?? 'Submit') }}" required>
            @error('submit_label') <span class="field__error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label class="field__label" for="reference_prefix">Reference Prefix *</label>
            <input class="input" type="text" id="reference_prefix" name="reference_prefix"
                   value="{{ old('reference_prefix', $form->reference_prefix ?? 'REQ') }}" required>
            <p class="text-sm text-muted">Submissions get a <span class="font-mono text-xs text-accent">PREFIX-XXXXXX</span> ID.</p>
            @error('reference_prefix') <span class="field__error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label class="field__label" for="is_active">Visibility</label>
            <label class="flex cursor-pointer items-center gap-3 border border-line p-3 transition-colors duration-300 hover:border-accent has-[:checked]:border-accent">
                <input type="checkbox" id="is_active" name="is_active" value="1" class="h-4 w-4 accent-accent"
                       @checked(old('is_active', $form->is_active ?? true))>
                <span class="text-sm text-white">Live on the site</span>
            </label>
            @error('is_active') <span class="field__error">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="field">
        <label class="field__label" for="success_message">Success Message</label>
        <textarea class="input" id="success_message" name="success_message" rows="2"
                  placeholder="Shown after submit. Use @{{reference}} and @{{name}} placeholders.">{{ old('success_message', $form->success_message) }}</textarea>
        @error('success_message') <span class="field__error">{{ $message }}</span> @enderror
    </div>

    <div class="flex flex-wrap items-center gap-4 border-t border-line pt-6">
        <button type="submit" class="btn btn--accent">
            {{ $isEdit ? 'Save Details' : 'Create Form' }}
            <span class="btn__strip" aria-hidden="true"></span>
        </button>

        <a href="{{ route('admin.forms.index') }}" class="tag hover:border-accent hover:text-accent">Cancel</a>
    </div>
</form>
