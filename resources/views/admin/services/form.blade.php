@php
    $service = $service ?? null;
    $method = $service ? 'PUT' : 'POST';
    $action = $service ? route('admin.services.update', $service) : route('admin.services.store');
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-8">
    @csrf
    @method($method)

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="admin-card space-y-6">
                <div class="field">
                    <label class="field__label" for="title">Title *</label>
                    <input class="input" type="text" id="title" name="title" value="{{ old('title', $service?->title) }}" required>
                    @error('title') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="field">
                        <label class="field__label" for="slug">Slug</label>
                        <input class="input" type="text" id="slug" name="slug" value="{{ old('slug', $service?->slug) }}" placeholder="auto-generated">
                        @error('slug') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="eyebrow">Eyebrow label</label>
                        <input class="input" type="text" id="eyebrow" name="eyebrow" value="{{ old('eyebrow', $service?->eyebrow) }}" placeholder="e.g. Engineering">
                        @error('eyebrow') <span class="field__error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="field">
                    <label class="field__label" for="excerpt">Excerpt</label>
                    <input class="input" type="text" id="excerpt" name="excerpt" maxlength="255" value="{{ old('excerpt', $service?->excerpt) }}">
                    @error('excerpt') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="summary">Summary</label>
                    <textarea class="input" id="summary" name="summary" rows="3">{{ old('summary', $service?->summary) }}</textarea>
                    @error('summary') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="body">Body (HTML allowed)</label>
                    <textarea class="input !min-h-72 font-mono !text-sm" id="body" name="body" rows="12">{{ old('body', $service?->body) }}</textarea>
                    @error('body') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="highlights">Highlights (one per line)</label>
                    <textarea class="input" id="highlights" name="highlights" rows="5" placeholder="Discovery workshop&#10;Solution architecture&#10;Delivery roadmap">{{ old('highlights') ?? implode("\n", (array) ($service?->highlights ?? [])) }}</textarea>
                    @error('highlights') <span class="field__error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="admin-card space-y-6">
                <div class="field">
                    <label class="field__label" for="image">Image</label>
                    <input class="input !border-line !text-sm" type="file" id="image" name="image" accept="image/*">
                    <span class="text-xs text-muted">JPG, PNG, WebP or AVIF — up to 4 MB.</span>
                    @error('image') <span class="field__error">{{ $message }}</span> @enderror

                    @if ($service?->image)
                        <img src="{{ Storage::url($service->image) }}" alt="" class="mt-3 h-32 w-full border border-line object-cover">
                    @endif
                </div>

                <div class="field">
                    <label class="field__label" for="sort_order">Sort order</label>
                    <input class="input" type="number" min="0" id="sort_order" name="sort_order" value="{{ old('sort_order', $service?->sort_order ?? 0) }}">
                    @error('sort_order') <span class="field__error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="admin-card space-y-4">
                <label class="flex items-center gap-3 text-body">
                    <input type="checkbox" name="is_featured" value="1" class="h-4 w-4 accent-[#d90429]" @checked(old('is_featured', $service?->is_featured))>
                    Featured
                </label>

                <label class="flex items-center gap-3 text-body">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1" class="h-4 w-4 accent-[#d90429]" @checked(old('is_published', $service?->is_published ?? true))>
                    Published
                </label>
            </div>

            <div class="flex flex-col gap-3">
                <button type="submit" class="btn btn--accent w-full">
                    {{ $service ? 'Save Changes' : 'Create Service' }}
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>

                <a href="{{ route('admin.services.index') }}" class="btn btn--ghost w-full text-center">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>
