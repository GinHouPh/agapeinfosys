@php
    $iconOptions = array_keys(config('navigation.icons', []));
    $value = old('link_value', $item?->link_value);
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="field__label" for="label-{{ $item?->id ?? 'new' }}">Label *</label>
        <input class="input" type="text" id="label-{{ $item?->id ?? 'new' }}" name="label"
               value="{{ old('label', $item?->label) }}" required>
        @error('label') <span class="field__error">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="field__label" for="link-type-{{ $item?->id ?? 'new' }}">Link type *</label>
        <select class="input" id="link-type-{{ $item?->id ?? 'new' }}" name="link_type" required>
            @foreach (\App\Enums\MenuItemType::cases() as $option)
                <option value="{{ $option->value }}"
                    @selected(old('link_type', $item?->link_type?->value) === $option->value)>
                    {{ $option->label() }}
                </option>
            @endforeach
        </select>
        @error('link_type') <span class="field__error">{{ $message }}</span> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="field__label" for="link-value-{{ $item?->id ?? 'new' }}">Destination *</label>
        <input class="input" type="text" id="link-value-{{ $item?->id ?? 'new' }}" name="link_value"
               value="{{ $value }}" required>
        <p class="text-sm text-muted">
            Route: a route name like <span class="font-mono text-xs">services.index</span> ·
            URL: a full address ·
            Page: a page slug ·
            Record: <span class="font-mono text-xs">service:my-service</span> ·
            Category: a category slug
        </p>
        @error('link_value') <span class="field__error">{{ $message }}</span> @enderror
    </div>

    @if (count($parentOptions) > 0)
        <div>
            <label class="field__label" for="parent-{{ $item?->id ?? 'new' }}">Nest under</label>
            <select class="input" id="parent-{{ $item?->id ?? 'new' }}" name="parent_id">
                <option value="">Top level</option>
                @foreach ($parentOptions as $parentId => $parentLabel)
                    <option value="{{ $parentId }}"
                        @selected((string) old('parent_id', $item?->parent_id) === (string) $parentId)>
                        {{ $parentLabel }}
                    </option>
                @endforeach
            </select>
            @error('parent_id') <span class="field__error">{{ $message }}</span> @enderror
        </div>
    @endif

    <div>
        <label class="field__label" for="icon-{{ $item?->id ?? 'new' }}">Icon</label>
        <select class="input" id="icon-{{ $item?->id ?? 'new' }}" name="icon">
            <option value="">None</option>
            @foreach ($iconOptions as $icon)
                <option value="{{ $icon }}" @selected(old('icon', $item?->icon) === $icon)>
                    {{ ucfirst($icon) }}
                </option>
            @endforeach
        </select>
        @error('icon') <span class="field__error">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="field__label" for="badge-{{ $item?->id ?? 'new' }}">Badge</label>
        <input class="input" type="text" id="badge-{{ $item?->id ?? 'new' }}" name="badge"
               value="{{ old('badge', $item?->badge) }}" placeholder="New">
        @error('badge') <span class="field__error">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="field__label" for="target-{{ $item?->id ?? 'new' }}">Opens in</label>
        <select class="input" id="target-{{ $item?->id ?? 'new' }}" name="target">
            <option value="_self" @selected(old('target', $item?->target) === '_self')>Same tab</option>
            <option value="_blank" @selected(old('target', $item?->target) === '_blank')>New tab</option>
        </select>
        @error('target') <span class="field__error">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="field__label" for="sort-order-{{ $item?->id ?? 'new' }}">Order</label>
        <input class="input" type="number" id="sort-order-{{ $item?->id ?? 'new' }}" name="sort_order"
               value="{{ old('sort_order', $item?->sort_order ?? 0) }}" min="0" max="9999">
        @error('sort_order') <span class="field__error">{{ $message }}</span> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="field__label" for="css-class-{{ $item?->id ?? 'new' }}">Extra CSS class</label>
        <input class="input" type="text" id="css-class-{{ $item?->id ?? 'new' }}" name="css_class"
               value="{{ old('css_class', $item?->css_class) }}" placeholder="text-accent">
        @error('css_class') <span class="field__error">{{ $message }}</span> @enderror
    </div>

    <div class="md:col-span-2 flex items-center gap-3">
        <input id="is-active-{{ $item?->id ?? 'new' }}" type="checkbox" name="is_active" value="1" class="h-4 w-4"
               @checked(old('is_active', $item?->is_active ?? true))>
        <label class="text-sm text-body" for="is-active-{{ $item?->id ?? 'new' }}">Visible</label>
    </div>
</div>
