@extends('layouts.admin')

@section('title', 'Collections')
@section('heading', 'Collections')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <p class="max-w-2xl text-sm text-muted">
            A collection is a content type you define: a set of fields and the records that
            use them. The builder's record widgets can list any collection.
        </p>
    </div>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <section class="admin-card !p-0">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Collection</th>
                            <th>Fields</th>
                            <th>Records</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($types as $type)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.collections.records', $type) }}" class="text-white hover:text-accent">
                                        {{ $type->name }}
                                    </a>
                                    <span class="block text-xs text-muted">{{ $type->description }}</span>
                                </td>
                                <td class="text-muted">{{ $type->fields_count }}</td>
                                <td class="text-muted">{{ $type->records_count }}</td>
                                <td>
                                    <span class="badge {{ $type->is_active ? 'badge--on' : 'badge--off' }}">
                                        {{ $type->is_active ? 'Active' : 'Off' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('admin.collections.records', $type) }}"
                                           class="text-xs tracking-[0.16em] text-accent uppercase">Open</a>
                                        <form action="{{ route('admin.collections.destroy', $type) }}" method="POST"
                                              onsubmit="return confirm('Delete this collection and every record in it?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs tracking-[0.16em] text-muted uppercase hover:text-accent">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-muted">
                                    No collections yet — create one on the right.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card h-fit">
            <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">New collection</h2>

            <form action="{{ route('admin.collections.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="field__label" for="collection-name">Name *</label>
                    <input class="input" type="text" id="collection-name" name="name" value="{{ old('name') }}"
                           placeholder="Case studies" required>
                    @error('name') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="field__label" for="collection-description">Description</label>
                    <input class="input" type="text" id="collection-description" name="description"
                           value="{{ old('description') }}" placeholder="What this collection holds">
                    @error('description') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="field__label" for="collection-route">Public path pattern</label>
                    <input class="input font-mono text-sm" type="text" id="collection-route" name="route_pattern"
                           value="{{ old('route_pattern') }}" placeholder="/work/{slug}">
                    <p class="text-xs text-muted">Leave blank to use /{collection}/{slug}.</p>
                    @error('route_pattern') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn btn--accent w-full">
                    Create Collection
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>
            </form>
        </section>
    </div>
@endsection
