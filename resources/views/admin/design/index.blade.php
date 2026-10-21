@extends('layouts.admin')

@section('title', 'Design System')
@section('heading', 'Design System')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <p class="max-w-2xl text-sm text-muted">
            A theme sets what each design token resolves to. The builder still stores token
            <span class="font-mono text-xs text-accent">names</span>, so switching a theme repaints
            every page without touching a single block.
        </p>

        <a href="{{ route('admin.pages.index') }}" class="tag self-start">Pages</a>
    </div>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <section class="admin-card !p-0">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Theme</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($themes as $theme)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.design.edit', $theme) }}" class="text-white hover:text-accent">
                                        {{ $theme->name }}
                                    </a>
                                    <span class="block text-xs text-muted">{{ $theme->description }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $theme->is_active ? 'badge--on' : 'badge--off' }}">
                                        {{ $theme->is_active ? 'Live' : 'Draft' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('admin.design.edit', $theme) }}"
                                           class="text-xs tracking-[0.16em] text-accent uppercase">Edit</a>

                                        @unless ($theme->is_active)
                                            <form action="{{ route('admin.design.activate', $theme) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-xs tracking-[0.16em] text-muted uppercase hover:text-accent">
                                                    Make live
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.design.destroy', $theme) }}" method="POST"
                                                  onsubmit="return confirm('Delete this theme?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs tracking-[0.16em] text-muted uppercase hover:text-accent">
                                                    Delete
                                                </button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-10 text-center text-muted">
                                    No themes yet — create one on the right.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card">
            <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">New theme</h2>

            <form action="{{ route('admin.design.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="field__label" for="theme-name">Name *</label>
                    <input class="input" type="text" id="theme-name" name="name" value="{{ old('name') }}"
                           placeholder="Midnight" required>
                    @error('name') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="field__label" for="theme-description">Description</label>
                    <input class="input" type="text" id="theme-description" name="description"
                           value="{{ old('description') }}" placeholder="What this theme is for">
                    @error('description') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <label class="flex items-center gap-3 text-body">
                    <input type="checkbox" name="is_active" value="1" class="h-4 w-4 accent-[#d90429]">
                    Make this theme live
                </label>

                <button type="submit" class="btn btn--accent">
                    Create Theme
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>
            </form>
        </section>
    </div>
@endsection
