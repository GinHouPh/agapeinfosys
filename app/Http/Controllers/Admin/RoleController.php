<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Concerns\RecordsActivity;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    use RecordsActivity;

    /**
     * Every role, with how many permissions and users it carries.
     */
    public function index(): View
    {
        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->orderBy('name')
            ->get();

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Create a role.
     */
    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:300'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(Permission::slugs())],
        ]);

        $role = Role::query()->create([
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
        ]);

        $role->permissions()->sync($this->permissionIds($attributes['permissions'] ?? []));

        $this->recordActivity('created', $role, 'Created the role "'.$role->name.'".');

        return redirect()
            ->route('admin.roles.edit', $role)
            ->with('status', 'Role created.');
    }

    /**
     * Edit a role and the permissions it grants.
     */
    public function edit(Role $role): View
    {
        $role->load('permissions');

        $granted = $role->permissions->pluck('slug')->all();

        return view('admin.roles.edit', [
            'role' => $role,
            'catalogue' => Permission::catalogue(),
            'granted' => $granted,
        ]);
    }

    /**
     * Update a role.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:300'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(Permission::slugs())],
        ]);

        $role->update([
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
        ]);

        $role->permissions()->sync($this->permissionIds($attributes['permissions'] ?? []));

        $this->recordActivity('updated', $role, 'Updated the role "'.$role->name.'".');

        return redirect()
            ->route('admin.roles.edit', $role)
            ->with('status', 'Role updated.');
    }

    /**
     * Delete a role. System roles are protected.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return back()->withErrors(['role' => 'Built-in roles cannot be deleted.']);
        }

        $name = $role->name;

        $role->delete();

        $this->recordActivity('deleted', null, 'Deleted the role "'.$name.'".');

        return redirect()
            ->route('admin.roles.index')
            ->with('status', 'Role deleted.');
    }

    /**
     * Resolve permission slugs into their identifiers.
     *
     * @param  array<int, string>  $slugs
     * @return list<int>
     */
    private function permissionIds(array $slugs): array
    {
        if ($slugs === []) {
            return [];
        }

        return Permission::query()
            ->whereIn('slug', $slugs)
            ->pluck('id')
            ->all();
    }
}
