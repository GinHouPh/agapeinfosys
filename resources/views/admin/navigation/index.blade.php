@extends('layouts.admin')

@section('title', 'Navigation')
@section('heading', 'Navigation')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <p class="max-w-2xl text-sm text-muted">
            Menus replace the navigation the site ships with. A location renders the first
            active menu saved for it; leave a location empty and the built-in links stay in place.
        </p>

        <a href="{{ route('admin.navigation.index') }}" class="tag self-start">Refresh</a>
    </div>

    <div class="space-y-8">
        @foreach ($locations as $entry)
            <section class="admin-card">
                <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line pb-4">
                    <div>
                        <h2 class="font-display text-lg tracking-[0.12em] text-white uppercase">
                            {{ $entry['location']->label() }}
                        </h2>
                        <p class="text-sm text-muted">{{ $entry['location']->value }}</p>
                    </div>

                    <span class="badge {{ $entry['location']->badgeClass() }}">
                        {{ $entry['menus']->count() }} {{ Str::plural('menu', $entry['menus']->count()) }}
                    </span>
                </header>

                @if ($entry['menus']->isNotEmpty())
                    <div class="mt-4 space-y-2">
                        @foreach ($entry['menus'] as $menu)
                            <div class="flex flex-wrap items-center justify-between gap-3 border border-line px-4 py-3">
                                <div>
                                    <a href="{{ route('admin.navigation.edit', $menu) }}"
                                       class="text-white hover:text-accent">{{ $menu->name }}</a>
                                    <span class="block font-mono text-xs text-muted">{{ $menu->slug }}</span>
                                </div>

                                <div class="flex items-center gap-4">
                                    <span class="badge {{ $menu->is_active ? 'badge--on' : 'badge--off' }}">
                                        {{ $menu->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span class="text-xs text-muted">{{ $menu->items_count }} items</span>
                                    <a href="{{ route('admin.navigation.edit', $menu) }}"
                                       class="text-xs tracking-[0.16em] text-accent uppercase">Edit</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-sm text-muted">No menu saved here — the built-in navigation is rendered.</p>
                @endif

                <details class="mt-4">
                    <summary class="cursor-pointer text-xs tracking-[0.16em] text-accent uppercase">
                        Add a menu to {{ $entry['location']->label() }}
                    </summary>

                    <form action="{{ route('admin.navigation.store') }}" method="POST" class="mt-4 grid gap-4 md:grid-cols-2">
                        @csrf
                        <input type="hidden" name="location" value="{{ $entry['location']->value }}">

                        <div>
                            <label class="field__label" for="menu-name-{{ $entry['location']->value }}">Name *</label>
                            <input class="input" type="text" id="menu-name-{{ $entry['location']->value }}" name="name"
                                   value="{{ old('name') }}" placeholder="Main navigation" required>
                            @error('name') <span class="field__error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="field__label" for="menu-slug-{{ $entry['location']->value }}">Slug</label>
                            <input class="input" type="text" id="menu-slug-{{ $entry['location']->value }}" name="slug"
                                   value="{{ old('slug') }}" placeholder="auto from the name">
                            @error('slug') <span class="field__error">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="field__label" for="menu-description-{{ $entry['location']->value }}">Description</label>
                            <input class="input" type="text" id="menu-description-{{ $entry['location']->value }}" name="description"
                                   value="{{ old('description') }}" placeholder="What this menu is for">
                            @error('description') <span class="field__error">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2 flex items-center gap-3">
                            <input id="menu-active-{{ $entry['location']->value }}" type="checkbox" name="is_active" value="1"
                                   class="h-4 w-4" checked>
                            <label class="text-sm text-body" for="menu-active-{{ $entry['location']->value }}">
                                Active — render this menu at this location
                            </label>
                        </div>

                        <div class="md:col-span-2">
                            <button type="submit" class="btn btn--accent">
                                Create Menu
                                <span class="btn__strip" aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>
                </details>
            </section>
        @endforeach
    </div>
@endsection
