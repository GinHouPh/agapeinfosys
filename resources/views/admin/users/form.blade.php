@php
    $user = $user ?? null;
    $method = $user ? 'PUT' : 'POST';
    $action = $user ? route('admin.users.update', $user) : route('admin.users.store');
    $roles = $roles ?? collect();
    $selectedRoles = old('roles', $user?->roles?->pluck('id')->all() ?? []);
@endphp

<form action="{{ $action }}" method="POST" class="space-y-8">
    @csrf
    @method($method)

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="admin-card space-y-6">
                <h3 class="font-display text-sm tracking-[0.2em] text-white uppercase">Account details</h3>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="field">
                        <label class="field__label" for="name">Name *</label>
                        <input class="input" type="text" id="name" name="name" value="{{ old('name', $user?->name) }}" required>
                        @error('name') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="phone">Phone</label>
                        <input class="input" type="text" id="phone" name="phone" value="{{ old('phone', $user?->phone) }}">
                        @error('phone') <span class="field__error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="field">
                    <label class="field__label" for="email">Email *</label>
                    <input class="input" type="email" id="email" name="email" value="{{ old('email', $user?->email) }}" required>
                    <span class="text-xs text-muted">Orders are matched to this address — it is the client's identity in the dashboard.</span>
                    @error('email') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="password">{{ $user ? 'New password (leave blank to keep current)' : 'Password *' }}</label>
                    <input class="input" type="password" id="password" name="password" {{ $user ? '' : 'required' }} autocomplete="new-password">
                    @error('password') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="password_confirmation">Confirm password {{ $user ? '(optional)' : '*' }}</label>
                    <input class="input" type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="admin-card space-y-6">
                <h3 class="font-display text-sm tracking-[0.2em] text-white uppercase">Access</h3>

                <label class="flex items-center gap-3 text-body">
                    <input type="hidden" name="is_admin" value="0">
                    <input type="checkbox" name="is_admin" value="1" class="h-4 w-4 accent-[#d90429]" @checked(old('is_admin', $user?->is_admin))>
                    Superuser
                </label>
                <p class="text-xs text-muted">
                    A superuser bypasses every permission check. Leave unticked for a standard client with the dashboard only.
                </p>
                @error('is_admin') <span class="field__error">{{ $message }}</span> @enderror

                <div class="field">
                    <label class="field__label" for="roles">Roles</label>
                    <select class="input" id="roles" name="roles[]" multiple size="6">
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected(in_array($role->id, array_map('intval', (array) $selectedRoles), true))>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-muted">
                        Hold Ctrl or Cmd to pick several. A user needs at least one role to reach the control panel.
                    </p>
                    @error('roles') <span class="field__error">{{ $message }}</span> @enderror
                    @error('roles.*') <span class="field__error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex flex-col gap-3">
                <button type="submit" class="btn btn--accent w-full">
                    {{ $user ? 'Save Changes' : 'Create Client' }}
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>

                <a href="{{ $user ? route('admin.users.show', $user) : route('admin.users.index') }}" class="btn btn--ghost w-full text-center">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>
