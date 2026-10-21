@extends('layouts.admin')

@section('title', 'Roles')
@section('heading', 'Roles & Permissions')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <p class="max-w-2xl text-sm text-muted">
            Roles bundle the permissions an admin needs. A user with
            <span class="text-white">superuser</span> access bypasses every check — everyone else
            reaches only what their roles grant.
        </p>
    </div>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <section class="admin-card !p-0">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Permissions</th>
                            <th>Users</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $role)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="text-white hover:text-accent">
                                        {{ $role->name }}
                                    </a>
                                    <span class="block text-xs text-muted">{{ $role->description }}</span>
                                </td>
                                <td class="text-muted">{{ $role->permissions_count }}</td>
                                <td class="text-muted">{{ $role->users_count }}</td>
                                <td>
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('admin.roles.edit', $role) }}"
                                           class="text-xs tracking-[0.16em] text-accent uppercase">Edit</a>
                                        @unless ($role->is_system)
                                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                                  onsubmit="return confirm('Delete this role?');">
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
                                <td colspan="4" class="py-10 text-center text-muted">
                                    No roles yet — create one on the right.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card">
            <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">New role</h2>

            <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="field__label" for="role-name">Name *</label>
                    <input class="input" type="text" id="role-name" name="name" value="{{ old('name') }}"
                           placeholder="Content Editor" required>
                    @error('name') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="field__label" for="role-description">Description</label>
                    <input class="input" type="text" id="role-description" name="description"
                           value="{{ old('description') }}" placeholder="What this role is for">
                    @error('description') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="field__label" for="role-permissions">Permissions</label>
                    <select class="input" id="role-permissions" name="permissions[]" multiple size="8">
                        @foreach (\App\Models\Permission::catalogue() as $group => $permissions)
                            <optgroup label="{{ $group }}">
                                @foreach ($permissions as $slug => $label)
                                    <option value="{{ $slug }}">{{ $label }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <p class="text-sm text-muted">Hold Ctrl or Cmd to pick several.</p>
                    @error('permissions') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn btn--accent">
                    Create Role
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>
            </form>
        </section>
    </div>
@endsection
