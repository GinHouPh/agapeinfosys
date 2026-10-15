<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SettingSeeder::class);
    }

    public function test_guests_are_redirected_to_the_admin_login(): void
    {
        $this->get(route('admin.profile.edit'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_non_admins_get_a_403(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.profile.edit'))
            ->assertForbidden();
    }

    public function test_an_admin_can_view_their_profile(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.profile.edit'))
            ->assertOk()
            ->assertSee($admin->name)
            ->assertSee($admin->email)
            ->assertSee('My Profile');
    }

    public function test_an_admin_can_update_their_profile(): void
    {
        $admin = User::factory()->admin()->create(['phone' => null]);

        $this->actingAs($admin)
            ->put(route('admin.profile.update'), [
                'name' => 'Updated Admin',
                'email' => $admin->email,
                'phone' => '+91 98765 43210',
            ])
            ->assertRedirect(route('admin.profile.edit'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Updated Admin',
            'phone' => '+91 98765 43210',
        ]);
    }

    public function test_the_profile_email_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($admin)
            ->from(route('admin.profile.edit'))
            ->put(route('admin.profile.update'), [
                'name' => $admin->name,
                'email' => 'taken@example.com',
            ])
            ->assertRedirect(route('admin.profile.edit'))
            ->assertSessionHasErrors('email');
    }

    public function test_the_profile_update_cannot_change_administrator_access(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.profile.update'), [
                'name' => $admin->name,
                'email' => $admin->email,
                'is_admin' => 0,
            ])
            ->assertRedirect(route('admin.profile.edit'));

        $this->assertTrue($admin->fresh()->isAdmin());
    }

    public function test_an_admin_can_change_their_password(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => 'current-password',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.password.update'), [
                'current_password' => 'current-password',
                'password' => 'brand-new-password',
                'password_confirmation' => 'brand-new-password',
            ])
            ->assertRedirect(route('admin.profile.edit'))
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('brand-new-password', $admin->fresh()->password));
    }

    public function test_changing_the_password_requires_the_current_one(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'current-password']);

        $this->actingAs($admin)
            ->from(route('admin.profile.edit'))
            ->put(route('admin.password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'brand-new-password',
                'password_confirmation' => 'brand-new-password',
            ])
            ->assertRedirect(route('admin.profile.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('current-password', $admin->fresh()->password));
    }

    public function test_an_admin_can_delete_their_own_account(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => 'current-password',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.profile.destroy'), [
                'current_password' => 'current-password',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHas('status');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
    }

    public function test_deleting_the_account_requires_the_current_password(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'current-password']);

        $this->actingAs($admin)
            ->from(route('admin.profile.edit'))
            ->delete(route('admin.profile.destroy'), [
                'current_password' => 'wrong-password',
            ])
            ->assertRedirect(route('admin.profile.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_the_profile_link_appears_in_the_admin_sidebar(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.profile.edit'), false)
            ->assertSee('My Profile');
    }

    public function test_the_dashboard_links_to_the_profile_from_the_sidebar_footer_and_header(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Jane Admin']);

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.profile.edit'), false);

        $this->assertSame(3, substr_count($response->getContent(), route('admin.profile.edit')));
        $this->assertStringContainsString('Jane Admin', $response->getContent());
    }

    public function test_the_sidebar_includes_a_collapse_toggle(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-admin-sidebar-toggle', false)
            ->assertSee('data-admin-sidebar', false);
    }
}
