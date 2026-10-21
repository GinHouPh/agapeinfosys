@extends('layouts.admin')

@section('title', $menu->name)
@section('heading', $menu->name)

@section('content')
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.navigation.index') }}" class="tag hover:border-accent hover:text-accent">
            All menus
        </a>
        <span class="badge {{ $menu->location->badgeClass() }}">{{ $menu->location->label() }}</span>
        <span class="badge {{ $menu->is_active ? 'badge--on' : 'badge--off' }}">
            {{ $menu->is_active ? 'Active' : 'Inactive' }}
        </span>
    </div>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <div class="space-y-8">
            <section class="admin-card">
                <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Menu details</h2>

                <form action="{{ route('admin.navigation.update', $menu) }}" method="POST" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="field__label" for="menu-name">Name *</label>
                        <input class="input" type="text" id="menu-name" name="name"
                               value="{{ old('name', $menu->name) }}" required>
                        @error('name') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="field__label" for="menu-slug">Slug</label>
                        <input class="input" type="text" id="menu-slug" name="slug"
                               value="{{ old('slug', $menu->slug) }}">
                        @error('slug') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="field__label" for="menu-location">Location *</label>
                        <select class="input" id="menu-location" name="location" required>
                            @foreach (\App\Enums\MenuLocation::cases() as $option)
                                <option value="{{ $option->value }}" @selected(old('location', $menu->location->value) === $option->value)>
                                    {{ $option->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('location') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="field__label" for="menu-description">Description</label>
                        <input class="input" type="text" id="menu-description" name="description"
                               value="{{ old('description', $menu->description) }}">
                        @error('description') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="md:col-span-2 flex items-center gap-3">
                        <input id="menu-is-active" type="checkbox" name="is_active" value="1" class="h-4 w-4"
                               @checked(old('is_active', $menu->is_active))>
                        <label class="text-sm text-body" for="menu-is-active">Active — render this menu</label>
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit" class="btn btn--accent">
                            Save Menu
                            <span class="btn__strip" aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
            </section>

            <section class="admin-card">
                <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Items</h2>

                @forelse ($rootItems as $item)
                    <div class="border border-line">
                        @include('admin.navigation.partials.item', ['item' => $item, 'menu' => $menu, 'parentOptions' => $parentOptions, 'depth' => 0])

                        @foreach ($item->children as $child)
                            <div class="border-t border-line bg-ink/40 pl-6">
                                @include('admin.navigation.partials.item', ['item' => $child, 'menu' => $menu, 'parentOptions' => $parentOptions, 'depth' => 1])
                            </div>
                        @endforeach
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-muted">No items yet — add the first one below.</p>
                @endforelse
            </section>
        </div>

        <div class="space-y-8">
            <section class="admin-card">
                <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Add an item</h2>

                <form action="{{ route('admin.navigation.items.store', $menu) }}" method="POST" class="space-y-4">
                    @csrf
                    @include('admin.navigation.partials.item-fields', ['parentOptions' => $parentOptions, 'item' => null])

                    <button type="submit" class="btn btn--accent">
                        Add Item
                        <span class="btn__strip" aria-hidden="true"></span>
                    </button>
                </form>
            </section>

            <section class="admin-card">
                <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Danger zone</h2>

                <p class="mb-4 text-sm text-muted">
                    Deleting this menu removes every item in it. The location falls back to the
                    built-in navigation.
                </p>

                <form action="{{ route('admin.navigation.destroy', $menu) }}" method="POST"
                      onsubmit="return confirm('Delete this menu and all its items?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs tracking-[0.16em] text-muted uppercase hover:text-accent">
                        Delete Menu
                    </button>
                </form>
            </section>
        </div>
    </div>
@endsection
