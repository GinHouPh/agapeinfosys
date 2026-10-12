@php
    $post = $post ?? null;
    $method = $post ? 'PUT' : 'POST';
    $action = $post ? route('admin.posts.update', $post) : route('admin.posts.store');
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-8">
    @csrf
    @method($method)

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="admin-card space-y-6">
                <div class="field">
                    <label class="field__label" for="title">Title *</label>
                    <input class="input" type="text" id="title" name="title" value="{{ old('title', $post?->title) }}" required>
                    @error('title') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="field">
                        <label class="field__label" for="slug">Slug</label>
                        <input class="input" type="text" id="slug" name="slug" value="{{ old('slug', $post?->slug) }}" placeholder="auto-generated">
                        @error('slug') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="category">Category</label>
                        <input class="input" type="text" id="category" name="category" value="{{ old('category', $post?->category) }}" placeholder="e.g. Engineering">
                        @error('category') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="published_at">Publish date</label>
                        <input class="input" type="date" id="published_at" name="published_at" value="{{ old('published_at', $post?->published_at?->format('Y-m-d')) }}">
                        @error('published_at') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="reading_time">Reading time</label>
                        <input class="input" type="text" id="reading_time" name="reading_time" value="{{ old('reading_time', $post?->reading_time) }}" placeholder="e.g. 6 min read">
                        @error('reading_time') <span class="field__error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="field">
                    <label class="field__label" for="excerpt">Excerpt</label>
                    <textarea class="input" id="excerpt" name="excerpt" rows="3" maxlength="300">{{ old('excerpt', $post?->excerpt) }}</textarea>
                    @error('excerpt') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="body">Body — separate paragraphs with a blank line</label>
                    <textarea class="input !min-h-96 font-mono !text-sm" id="body" name="body" rows="16">{{ old('body', $post?->body) }}</textarea>
                    @error('body') <span class="field__error">{{ $message }}</span> @enderror
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

                    @if ($post?->cover_image)
                        <img src="{{ Storage::url($post->cover_image) }}" alt="" class="mt-3 h-32 w-full border border-line object-cover">
                    @endif
                </div>

                <div class="field">
                    <label class="field__label" for="sort_order">Sort order</label>
                    <input class="input" type="number" min="0" id="sort_order" name="sort_order" value="{{ old('sort_order', $post?->sort_order ?? 0) }}">
                    @error('sort_order') <span class="field__error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="admin-card">
                <label class="flex items-center gap-3 text-body">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1" class="h-4 w-4 accent-[#d90429]" @checked(old('is_published', $post?->is_published ?? true))>
                    Published
                </label>
            </div>

            <div class="flex flex-col gap-3">
                <button type="submit" class="btn btn--accent w-full">
                    {{ $post ? 'Save Changes' : 'Create Article' }}
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>

                <a href="{{ route('admin.posts.index') }}" class="btn btn--ghost w-full text-center">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>
