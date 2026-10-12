@php
    $project = $project ?? null;
    $method = $project ? 'PUT' : 'POST';
    $action = $project ? route('admin.projects.update', $project) : route('admin.projects.store');
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-8">
    @csrf
    @method($method)

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="admin-card space-y-6">
                <div class="field">
                    <label class="field__label" for="title">Title *</label>
                    <input class="input" type="text" id="title" name="title" value="{{ old('title', $project?->title) }}" required>
                    @error('title') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="field">
                        <label class="field__label" for="slug">Slug</label>
                        <input class="input" type="text" id="slug" name="slug" value="{{ old('slug', $project?->slug) }}" placeholder="auto-generated">
                        @error('slug') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="client">Client</label>
                        <input class="input" type="text" id="client" name="client" value="{{ old('client', $project?->client) }}">
                        @error('client') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="category">Category</label>
                        <input class="input" type="text" id="category" name="category" value="{{ old('category', $project?->category) }}" placeholder="e.g. Fintech">
                        @error('category') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="service_type">Engagement type</label>
                        <input class="input" type="text" id="service_type" name="service_type" value="{{ old('service_type', $project?->service_type) }}" placeholder="e.g. End-to-end build">
                        @error('service_type') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="year">Year</label>
                        <input class="input" type="number" min="2000" max="2100" id="year" name="year" value="{{ old('year', $project?->year) }}">
                        @error('year') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="project_url">Project URL</label>
                        <input class="input" type="url" id="project_url" name="project_url" value="{{ old('project_url', $project?->project_url) }}" placeholder="https://">
                        @error('project_url') <span class="field__error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="field">
                    <label class="field__label" for="excerpt">Excerpt</label>
                    <input class="input" type="text" id="excerpt" name="excerpt" maxlength="255" value="{{ old('excerpt', $project?->excerpt) }}">
                    @error('excerpt') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="body">Body (HTML allowed)</label>
                    <textarea class="input !min-h-72 font-mono !text-sm" id="body" name="body" rows="12">{{ old('body', $project?->body) }}</textarea>
                    @error('body') <span class="field__error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="admin-card grid gap-6 sm:grid-cols-2">
                <div class="field">
                    <label class="field__label" for="stack">Tech stack (one per line)</label>
                    <textarea class="input" id="stack" name="stack" rows="6" placeholder="Laravel&#10;Vue&#10;PostgreSQL&#10;AWS">{{ old('stack') ?? implode("\n", (array) ($project?->stack ?? [])) }}</textarea>
                    @error('stack') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="outcomes">Outcomes (one per line)</label>
                    <textarea class="input" id="outcomes" name="outcomes" rows="6" placeholder="40% faster checkout&#10;3x order volume handled">{{ old('outcomes') ?? implode("\n", (array) ($project?->outcomes ?? [])) }}</textarea>
                    @error('outcomes') <span class="field__error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="admin-card space-y-6">
                <div class="field">
                    <label class="field__label" for="cover_image">Cover image</label>
                    <input class="input !border-line !text-sm" type="file" id="cover_image" name="cover_image" accept="image/*">
                    <span class="text-xs text-muted">JPG, PNG, WebP or AVIF — up to 4 MB.</span>
                    @error('cover_image') <span class="field__error">{{ $message }}</span> @enderror

                    @if ($project?->cover_image)
                        <img src="{{ Storage::url($project->cover_image) }}" alt="" class="mt-3 h-32 w-full border border-line object-cover">
                    @endif
                </div>

                <div class="field">
                    <label class="field__label" for="sort_order">Sort order</label>
                    <input class="input" type="number" min="0" id="sort_order" name="sort_order" value="{{ old('sort_order', $project?->sort_order ?? 0) }}">
                    @error('sort_order') <span class="field__error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="admin-card space-y-4">
                <label class="flex items-center gap-3 text-body">
                    <input type="checkbox" name="is_featured" value="1" class="h-4 w-4 accent-[#d90429]" @checked(old('is_featured', $project?->is_featured))>
                    Featured
                </label>

                <label class="flex items-center gap-3 text-body">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1" class="h-4 w-4 accent-[#d90429]" @checked(old('is_published', $project?->is_published ?? true))>
                    Published
                </label>
            </div>

            <div class="flex flex-col gap-3">
                <button type="submit" class="btn btn--accent w-full">
                    {{ $project ? 'Save Changes' : 'Create Case Study' }}
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>

                <a href="{{ route('admin.projects.index') }}" class="btn btn--ghost w-full text-center">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>
