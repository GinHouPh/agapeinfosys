@php
    $category = $category ?? null;
    $isEdit = $category !== null;
    $method = $isEdit ? 'PUT' : 'POST';
    $action = $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store');
@endphp

<form action="{{ $action }}" method="POST" class="space-y-8">
    @csrf
    @method($method)

    <div class="admin-card space-y-6">
        <div class="grid gap-6 sm:grid-cols-2">
            <div class="field">
                <label class="field__label" for="name">Name *</label>
                <input class="input" type="text" id="name" name="name" value="{{ old('name', $category?->name) }}" required placeholder="PHP Scripts">
                @error('name') <span class="field__error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label class="field__label" for="slug">Slug</label>
                <input class="input" type="text" id="slug" name="slug" value="{{ old('slug', $category?->slug) }}" placeholder="php-scripts">
                @error('slug') <span class="field__error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="field">
            <label class="field__label" for="parent_id">Parent category</label>
            <select class="input" id="parent_id" name="parent_id">
                <option value="">— None (top-level market) —</option>
                @foreach ($categories as $id => $label)
                    <option value="{{ $id }}" @selected((string) old('parent_id', $category?->parent_id) === (string) $id)>{{ $label }}</option>
                @endforeach
            </select>
            @error('parent_id') <span class="field__error">{{ $message }}</span> @enderror
            <span class="mt-1 block text-xs text-muted">Markets sit at the top; categories and subcategories nest beneath them.</span>
        </div>

        <div class="field">
            <label class="field__label" for="description">Description</label>
            <textarea class="input" id="description" name="description" rows="3">{{ old('description', $category?->description) }}</textarea>
            @error('description') <span class="field__error">{{ $message }}</span> @enderror
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div class="field">
                <label class="field__label" for="sort_order">Sort order</label>
                <input class="input" type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $category?->sort_order ?? 0) }}" min="0">
                @error('sort_order') <span class="field__error">{{ $message }}</span> @enderror
            </div>

            <div class="field flex items-end pb-2">
                <label class="flex items-center gap-3 text-sm text-white">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category?->is_active ?? true))>
                    Visible on the storefront
                </label>
            </div>
        </div>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="btn btn--accent">
            {{ $isEdit ? 'Save changes' : 'Create category' }}
            <span class="btn__strip" aria-hidden="true"></span>
        </button>

        <a href="{{ route('admin.categories.index') }}" class="tag self-center hover:border-accent hover:text-accent">Cancel</a>
    </div>
</form>
