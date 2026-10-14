<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SettingSeeder::class);
    }

    public function test_guests_are_redirected_to_the_client_login_page_from_the_dashboard(): void
    {
        $this->get(route('dashboard.index'))
            ->assertRedirect(route('login'));
    }

    public function test_the_login_page_renders_for_guests(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_the_register_page_renders_for_guests(): void
    {
        $this->get(route('register'))->assertOk();
    }

    public function test_a_visitor_can_register_a_client_account(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Priya Sharma',
            'email' => 'priya@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'terms' => '1',
        ])->assertRedirect(route('dashboard.index'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'priya@example.com',
            'is_admin' => false,
        ]);
    }

    public function test_registration_requires_accepting_the_terms(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Priya Sharma',
                'email' => 'priya@example.com',
                'password' => 'secret-password',
                'password_confirmation' => 'secret-password',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('terms');

        $this->assertGuest();
    }

    public function test_registration_requires_a_confirmed_eight_character_password(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Priya Sharma',
            'email' => 'priya@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
            'terms' => '1',
        ])->assertSessionHasErrors(['password']);

        $this->assertGuest();
    }

    public function test_registration_rejects_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->post(route('register.store'), [
            'name' => 'Someone Else',
            'email' => 'taken@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'terms' => '1',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_client_can_sign_in(): void
    {
        $client = User::factory()->create([
            'email' => 'client@example.com',
            'password' => 'secret-password',
        ]);

        $this->post(route('login.attempt'), [
            'email' => $client->email,
            'password' => 'secret-password',
        ])->assertRedirect(route('dashboard.index'));

        $this->assertAuthenticatedAs($client);
    }

    public function test_wrong_credentials_are_rejected(): void
    {
        $client = User::factory()->create(['email' => 'client@example.com']);

        $this->from(route('login'))
            ->post(route('login.attempt'), [
                'email' => $client->email,
                'password' => 'not-the-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_administrators_cannot_sign_in_through_the_client_login(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => 'secret-password',
        ]);

        $this->post(route('login.attempt'), [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_clients_are_redirected_away_from_the_login_page(): void
    {
        $client = User::factory()->create();

        $this->actingAs($client)
            ->get(route('login'))
            ->assertRedirect(route('dashboard.index'));
    }

    public function test_authenticated_administrators_are_redirected_to_the_control_panel(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('login'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_a_client_can_sign_out(): void
    {
        $client = User::factory()->create();

        $this->actingAs($client)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_client_login_attempts_are_rate_limited(): void
    {
        $client = User::factory()->create(['email' => 'client@example.com']);

        for ($attempt = 0; $attempt < 7; $attempt++) {
            $response = $this->post(route('login.attempt'), [
                'email' => $client->email,
                'password' => 'wrong-password',
            ]);

            if ($attempt >= 6) {
                $response->assertStatus(429);
            }
        }
    }
}
