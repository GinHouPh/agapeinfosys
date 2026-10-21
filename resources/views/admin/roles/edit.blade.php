@extends('layouts.admin')

@section('title', $role->name)
@section('heading', $role->name)

@section('content')
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.roles.index') }}" class="tag hover:border-accent hover:text-accent">All roles</a>
        @if ($role->is_system)
            <span class="badge badge--on">Built-in</span>
        @endif
    </div>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <section class="admin-card">
            <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Permissions</h2>

            <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                @foreach ($catalogue as $group => $permissions)
                    <fieldset class="border border-line p-4">
                        <legend class="px-2 font-display text-sm tracking-[0.16em] text-accent uppercase">
                            {{ $group }}
                        </legend>

                        <div class="grid gap-2 md:grid-cols-2">
                            @foreach ($permissions as $slug => $label)
                                <label class="flex items-start gap-3 text-sm text-body" for="permission-{{ $slug }}">
                                    <input type="checkbox"
                                           class="mt-1 h-4 w-4"
                                           id="permission-{{ $slug }}"
                                           name="permissions[]"
                                           value="{{ $slug }}"
                                           @checked(in_array($slug, $granted, true))>
                                    <span>
                                        {{ $label }}
                                        <span class="block font-mono text-xs text-muted">{{ $slug }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach

                <button type="submit" class="btn btn--accent">
                    Save Role
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>
            </form>
        </section>

        <div class="space-y-8">
            <section class="admin-card">
                <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Details</h2>

                <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="field__label" for="role-name">Name *</label>
                        <input class="input" type="text" id="role-name" name="name" value="{{ old('name', $role->name) }}" required>
                        @error('name') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="field__label" for="role-description">Description</label>
                        <input class="input" type="text" id="role-description" name="description"
                               value="{{ old('description', $role->description) }}">
                        @error('description') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <p class="text-sm text-muted">Saving here updates the role's name and description.</p>

                    <button type="submit" class="btn btn--accent">
                        Save Details
                        <span class="btn__strip" aria-hidden="true"></span>
                    </button>
                </form>
            </section>

            @unless ($role->is_system)
                <section class="admin-card">
                    <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Danger zone</h2>

                    <p class="mb-4 text-sm text-muted">
                        Deleting this role removes it from every user who holds it.
                    </p>

                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                          onsubmit="return confirm('Delete this role?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs tracking-[0.16em] text-muted uppercase hover:text-accent">
                            Delete Role
                        </button>
                    </form>
                </section>
            @endunless
        </div>
    </div>
@endsection
