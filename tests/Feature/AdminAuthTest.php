<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SettingSeeder::class);
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_login_page_renders_for_guests(): void
    {
        $this->get(route('admin.login'))->assertOk();
    }

    public function test_an_administrator_can_sign_in(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => 'secret-password',
        ]);

        $this->post(route('admin.login.attempt'), [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_non_admins_cannot_sign_in(): void
    {
        $user = User::factory()->create([
            'email' => 'reader@example.com',
            'password' => 'secret-password',
        ]);

        $this->from(route('admin.login'))
            ->post(route('admin.login.attempt'), [
                'email' => $user->email,
                'password' => 'secret-password',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'admin@example.com']);

        $this->post(route('admin.login.attempt'), [
            'email' => $admin->email,
            'password' => 'not-the-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_an_authenticated_non_admin_gets_a_403(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_an_admin_can_view_the_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_an_admin_can_sign_out(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'admin@example.com']);

        for ($attempt = 0; $attempt < 7; $attempt++) {
            $response = $this->post(route('admin.login.attempt'), [
                'email' => $admin->email,
                'password' => 'wrong-password',
            ]);

            if ($attempt >= 6) {
                $response->assertStatus(429);
            }
        }
    }
}
