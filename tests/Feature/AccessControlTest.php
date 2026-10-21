<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    /**
     * A role holding the given permission slugs.
     *
     * @param  array<int, string>  $slugs
     */
    private function roleWith(array $slugs, string $name = 'Test Role'): Role
    {
        $role = Role::factory()->create(['name' => $name]);

        $role->permissions()->sync(
            Permission::query()->whereIn('slug', $slugs)->pluck('id')->all(),
        );

        return $role->fresh('permissions');
    }

    public function test_a_user_without_roles_cannot_reach_the_control_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_a_superuser_bypasses_every_permission_check(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertTrue($admin->isSuperAdmin());
        $this->assertTrue($admin->hasPermission('anything.at.all'));
        $this->assertTrue($admin->hasAnyPermission());

        $this->actingAs($admin)->get(route('admin.roles.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.activity.index'))->assertOk();
    }

    public function test_a_permission_grants_access_to_its_route(): void
    {
        $role = $this->roleWith(['dashboard.view', 'pages.manage']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('admin.pages.index'))
            ->assertOk();
    }

    public function test_a_permission_does_not_grant_access_to_other_routes(): void
    {
        $role = $this->roleWith(['dashboard.view']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        $this->actingAs($user)
            ->get(route('admin.pages.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.gateways.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.roles.index'))
            ->assertForbidden();
    }

    public function test_a_role_cannot_be_assigned_a_permission_outside_the_catalogue(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.roles.store'), [
                'name' => 'Sneaky',
                'permissions' => ['not.a.real.permission'],
            ])
            ->assertSessionHasErrors('permissions.0');
    }

    public function test_a_non_superuser_with_a_role_can_sign_in(): void
    {
        $role = $this->roleWith(['dashboard.view']);
        $user = User::factory()->create([
            'email' => 'editor@example.com',
            'password' => 'secret-password',
        ]);
        $user->roles()->attach($role);

        $this->post(route('admin.login.attempt'), [
            'email' => $user->email,
            'password' => 'secret-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_user_without_roles_cannot_sign_in(): void
    {
        $user = User::factory()->create([
            'email' => 'client@example.com',
            'password' => 'secret-password',
        ]);

        $this->post(route('admin.login.attempt'), [
            'email' => $user->email,
            'password' => 'secret-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_role_can_be_created_with_permissions(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.roles.store'), [
                'name' => 'Content Editor',
                'description' => 'Writes content.',
                'permissions' => ['pages.manage', 'posts.manage'],
            ])
            ->assertRedirect();

        $role = Role::query()->where('name', 'Content Editor')->firstOrFail();

        $this->assertTrue($role->grants('pages.manage'));
        $this->assertTrue($role->grants('posts.manage'));
        $this->assertFalse($role->grants('gateways.manage'));
    }

    public function test_a_role_can_be_updated(): void
    {
        $admin = User::factory()->admin()->create();
        $role = $this->roleWith(['pages.manage']);

        $this->actingAs($admin)
            ->put(route('admin.roles.update', $role), [
                'name' => 'Renamed Role',
                'description' => 'Changed.',
                'permissions' => ['posts.manage'],
            ])
            ->assertRedirect();

        $role->refresh()->load('permissions');

        $this->assertSame('Renamed Role', $role->name);
        $this->assertTrue($role->grants('posts.manage'));
        $this->assertFalse($role->grants('pages.manage'));
    }

    public function test_a_role_can_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $role = $this->roleWith(['pages.manage']);

        $this->actingAs($admin)
            ->delete(route('admin.roles.destroy', $role))
            ->assertRedirect(route('admin.roles.index'));

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_a_built_in_role_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $role = Role::factory()->system()->create();

        $this->actingAs($admin)
            ->delete(route('admin.roles.destroy', $role))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_role_changes_are_written_to_the_activity_log(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.roles.store'), ['name' => 'Audited Role'])
            ->assertRedirect();

        $this->assertDatabaseHas('activity_log', [
            'user_id' => $admin->id,
            'action' => 'created',
            'subject_type' => Role::class,
        ]);
    }

    public function test_the_activity_log_lists_what_was_recorded(): void
    {
        $admin = User::factory()->admin()->create();
        ActivityLog::factory()->create(['user_id' => $admin->id, 'action' => 'updated']);

        $this->actingAs($admin)
            ->get(route('admin.activity.index'))
            ->assertOk();
    }

    public function test_the_sidebar_hides_links_the_admin_cannot_reach(): void
    {
        $role = $this->roleWith(['dashboard.view']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertDontSee(route('admin.gateways.index'), false)
            ->assertDontSee(route('admin.roles.index'), false);
    }

    public function test_roles_can_be_assigned_to_a_user(): void
    {
        $admin = User::factory()->admin()->create();
        $role = $this->roleWith(['pages.manage']);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => [$role->id],
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertTrue($user->fresh()->hasPermission('pages.manage'));
    }

    public function test_the_seeded_roles_cover_the_catalogue(): void
    {
        $this->seed(RoleSeeder::class);

        $superadmin = Role::query()->where('slug', 'superadmin')->firstOrFail();

        $this->assertSame(
            count(Permission::slugs()),
            $superadmin->permissions()->count(),
            'The Superadmin role must hold every permission.',
        );
    }
}
