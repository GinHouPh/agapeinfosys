@php
    $project = $project ?? null;
    $isEdit = $project !== null;
    $method = $isEdit ? 'PUT' : 'POST';
    $action = $isEdit ? route('admin.showcase-projects.update', $project) : route('admin.showcase-projects.store');
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-8">
    @csrf
    @method($method)

    <div class="admin-card space-y-6">
        <div class="grid gap-6 sm:grid-cols-2">
            <div class="field">
                <label class="field__label" for="title">Title *</label>
                <input class="input" type="text" id="title" name="title" value="{{ old('title', $project?->title) }}" required placeholder="Streamly">
                @error('title') <span class="field__error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label class="field__label" for="slug">Slug</label>
                <input class="input" type="text" id="slug" name="slug" value="{{ old('slug', $project?->slug) }}" placeholder="streamly">
                @error('slug') <span class="field__error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-3">
            <div class="field">
                <label class="field__label" for="industry">Industry *</label>
                <input class="input" type="text" id="industry" name="industry" list="industry-suggestions"
                       value="{{ old('industry', $project?->industry) }}" required placeholder="OTT & Media">
                <datalist id="industry-suggestions">
                    @foreach (\App\Models\ShowcaseProject::industries() as $suggestion)
                        <option value="{{ $suggestion }}"></option>
                    @endforeach
                </datalist>
                @error('industry') <span class="field__error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label class="field__label" for="status">Stage *</label>
                <select class="input" id="status" name="status" required>
                    @foreach (\App\Enums\ProjectStatus::cases() as $case)
                        <option value="{{ $case->value }}" @selected(old('status', $project?->status->value ?? 'ongoing') === $case->value)>
                            {{ $case->label() }}
                        </option>
                    @endforeach
                </select>
                @error('status') <span class="field__error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label class="field__label" for="expected_launch">Expected launch</label>
                <input class="input" type="date" id="expected_launch" name="expected_launch"
                       value="{{ old('expected_launch', $project?->expected_launch?->format('Y-m-d')) }}">
                @error('expected_launch') <span class="field__error">{{ $message }}</span> @enderror
                <span class="mt-1 block text-xs text-muted">Shown as "Expected Mon YYYY" on upcoming cards.</span>
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div class="field">
                <label class="field__label" for="poster">Poster image</label>
                <input class="input" type="file" id="poster" name="poster" accept="image/*">
                @error('poster') <span class="field__error">{{ $message }}</span> @enderror
                <span class="mt-1 block text-xs text-muted">Portrait 2:3 works best — JPG, PNG or WebP up to 4 MB.</span>
            </div>

            <div class="field flex items-end pb-2">
                @if ($project?->posterUrl())
                    <img src="{{ $project->posterUrl() }}" alt="{{ $project->title }}" class="h-36 w-24 border border-line object-cover">
                @endif
            </div>
        </div>

        <div class="field">
            <label class="field__label" for="tagline">Tagline</label>
            <input class="input" type="text" id="tagline" name="tagline" value="{{ old('tagline', $project?->tagline) }}"
                   maxlength="240" placeholder="One line shown on the poster card.">
            @error('tagline') <span class="field__error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label class="field__label" for="description">Description</label>
            <textarea class="input" id="description" name="description" rows="3">{{ old('description', $project?->description) }}</textarea>
            @error('description') <span class="field__error">{{ $message }}</span> @enderror
        </div>

        <div class="grid gap-6 sm:grid-cols-3">
            <div class="field">
                <label class="field__label" for="stack">Tech stack</label>
                <input class="input" type="text" id="stack" name="stack"
                       value="{{ old('stack', $project?->stack ? implode(', ', $project->stack) : '') }}" placeholder="Laravel, Vue, AWS">
                @error('stack') <span class="field__error">{{ $message }}</span> @enderror
                <span class="mt-1 block text-xs text-muted">Comma-separated chips under the card.</span>
            </div>

            <div class="field">
                <label class="field__label" for="sort_order">Sort order</label>
                <input class="input" type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $project?->sort_order ?? 0) }}" min="0">
                @error('sort_order') <span class="field__error">{{ $message }}</span> @enderror
            </div>

            <div class="field flex items-end pb-2">
                <label class="flex items-center gap-3 text-sm text-white">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $project?->is_active ?? true))>
                    Visible on the public wall
                </label>
            </div>
        </div>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="btn btn--accent">
            {{ $isEdit ? 'Save changes' : 'Create project' }}
            <span class="btn__strip" aria-hidden="true"></span>
        </button>

        <a href="{{ route('admin.showcase-projects.index') }}" class="tag self-center hover:border-accent hover:text-accent">Cancel</a>
    </div>
</form>
