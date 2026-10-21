@php
    $page = $page ?? null;
    $pageTemplates = $pageTemplates ?? collect();
    $parentOptions = \App\Models\Page::query()
        ->when($page, fn ($query) => $query->where('id', '!=', $page->id))
        ->orderBy('title')
        ->get();
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
            Reserved slugs take over a live route when published —
            @foreach (array_keys(\App\Models\Page::flagshipRoutes()) as $slug)
                <span class="font-mono text-xs text-accent">{{ $slug }}</span>@if (! $loop->last), @endif
            @endforeach
            for the marketing pages, and
            @foreach (array_keys(\App\Models\Page::recordTemplates()) as $slug)
                <span class="font-mono text-xs text-accent">{{ $slug }}</span>@if (! $loop->last), @endif
            @endforeach
            for record detail pages.
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

    <div class="field">
        <label class="field__label" for="scheduled_for">Go live on</label>
        <input class="input" type="datetime-local" id="scheduled_for" name="scheduled_for"
               value="{{ old('scheduled_for', $page?->scheduled_for?->format('Y-m-d\TH:i')) }}">
        <p class="text-sm text-muted">Only needed when the status is Scheduled.</p>
        @error('scheduled_for') <span class="field__error">{{ $message }}</span> @enderror
    </div>

    <div class="field sm:col-span-2">
        <label class="field__label" for="parent_id">Nest under</label>
        <select class="input" id="parent_id" name="parent_id">
            <option value="">Top level</option>
            @foreach ($parentOptions as $parent)
                <option value="{{ $parent->id }}" @selected((string) old('parent_id', $page?->parent_id) === (string) $parent->id)>
                    {{ $parent->title }}
                </option>
            @endforeach
        </select>
        <p class="text-sm text-muted">
            A nested page lives at <span class="font-mono text-xs text-accent">/pages/&lt;parent&gt;/&lt;page&gt;</span>.
        </p>
        @error('parent_id') <span class="field__error">{{ $message }}</span> @enderror
    </div>

    <div class="field sm:col-span-2">
        <label class="field__label" for="meta_description">Meta description</label>
        <textarea class="input" id="meta_description" name="meta_description" rows="2"
                  placeholder="Shown in search results">{{ old('meta_description', $page?->meta_description) }}</textarea>
        @error('meta_description') <span class="field__error">{{ $message }}</span> @enderror
    </div>

    @if ($page === null && $pageTemplates->isNotEmpty())
        <div class="field sm:col-span-2">
            <label class="field__label" for="template">Start from a template</label>
            <select class="input" id="template" name="template">
                <option value="">Blank canvas</option>
                @foreach ($pageTemplates as $pageTemplate)
                    <option value="{{ $pageTemplate->id }}" @selected((string) old('template') === (string) $pageTemplate->id)>
                        {{ $pageTemplate->name }}
                    </option>
                @endforeach
            </select>
            <p class="text-sm text-muted">The template's sections land on the canvas and stay editable.</p>
            @error('template') <span class="field__error">{{ $message }}</span> @enderror
        </div>
    @endif
</div>
