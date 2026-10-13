@php
    $page = $page ?? null;
@endphp

<div class="grid gap-6 sm:grid-cols-2">
    <div class="field">
        <label class="field__label" for="title">Title *</label>
        <input class="input" type="text" id="title" name="title"
               value="{{ old('title', $page?->title) }}" required>
        @error('title') <span class="field__error">{{ $message }}</span> @enderror
    </div>

    <div class="field">
        <label class="field__label" for="slug">Slug</label>
        <input class="input" type="text" id="slug" name="slug"
               value="{{ old('slug', $page?->slug) }}" placeholder="auto from title">
        <p class="text-sm text-muted">
            Use <span class="font-mono text-xs text-accent">home</span>,
            <span class="font-mono text-xs text-accent">about</span> or
            <span class="font-mono text-xs text-accent">services</span> to replace that flagship route.
        </p>
        @error('slug') <span class="field__error">{{ $message }}</span> @enderror
    </div>

    <div class="field">
        <label class="field__label" for="status">Status *</label>
        <select class="input" id="status" name="status" required>
            @foreach (\App\Enums\PageStatus::cases() as $case)
                <option value="{{ $case->value }}" @selected(old('status', $page?->status->value ?? 'draft') === $case->value)>
                    {{ $case->label() }}
                </option>
            @endforeach
        </select>
        @error('status') <span class="field__error">{{ $message }}</span> @enderror
    </div>

    <div class="field sm:col-span-2">
        <label class="field__label" for="meta_description">Meta description</label>
        <textarea class="input" id="meta_description" name="meta_description" rows="2"
                  placeholder="Shown in search results">{{ old('meta_description', $page?->meta_description) }}</textarea>
        @error('meta_description') <span class="field__error">{{ $message }}</span> @enderror
    </div>
</div>
