<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminClientsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_guest_cannot_manage_clients(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_cannot_manage_clients(): void
    {
        $client = User::factory()->create();

        $this->actingAs($client)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_the_client_list_can_be_searched(): void
    {
        User::factory()->create(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);
        User::factory()->create(['name' => 'Grace Hopper', 'email' => 'grace@example.com']);

        $this->actingAs($this->admin())
            ->get(route('admin.users.index', ['q' => 'Ada']))
            ->assertOk()
            ->assertSee('Ada Lovelace')
            ->assertDontSee('Grace Hopper');
    }

    public function test_the_client_list_can_be_filtered_by_role(): void
    {
        User::factory()->create(['is_admin' => false]);
        User::factory()->admin()->create(['email' => 'boss@example.com']);

        $this->actingAs($this->admin())
            ->get(route('admin.users.index', ['role' => 'admins']))
            ->assertOk()
            ->assertSee('boss@example.com');
    }

    public function test_a_client_account_can_be_created(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Add client');

        $this->actingAs($this->admin())
            ->post(route('admin.users.store'), [
                'name' => 'New Client',
                'email' => 'new-client@example.com',
                'phone' => '+91 90000 00000',
                'password' => 'secret-password',
                'password_confirmation' => 'secret-password',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'new-client@example.com',
            'phone' => '+91 90000 00000',
            'is_admin' => false,
        ]);
    }

    public function test_a_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($this->admin())
            ->from(route('admin.users.create'))
            ->post(route('admin.users.store'), [
                'name' => 'Duplicate',
                'email' => 'taken@example.com',
                'password' => 'secret-password',
                'password_confirmation' => 'secret-password',
            ])
            ->assertRedirect(route('admin.users.create'))
            ->assertSessionHasErrors('email');
    }

    public function test_a_client_account_can_be_updated(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);

        $this->actingAs($this->admin())
            ->put(route('admin.users.update', $user), [
                'name' => 'Renamed Client',
                'email' => 'renamed@example.com',
                'phone' => '',
                'password' => '',
                'password_confirmation' => '',
                'is_admin' => 1,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Renamed Client',
            'email' => 'renamed@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_updating_can_reset_the_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '',
                'password' => 'brand-new-password',
                'password_confirmation' => 'brand-new-password',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertTrue(Hash::check('brand-new-password', $user->fresh()->password));
    }

    public function test_an_admin_cannot_remove_their_own_administrator_access(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'phone' => '',
                'password' => '',
                'password_confirmation' => '',
                'is_admin' => 0,
            ])
            ->assertSessionHasErrors('is_admin');

        $this->assertTrue($admin->fresh()->is_admin);
    }

    public function test_an_admin_cannot_delete_their_own_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_a_client_account_can_be_deleted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_the_detail_screen_shows_the_clients_orders(): void
    {
        $client = User::factory()->create(['email' => 'buyer@example.com']);
        $order = Order::factory()->paid()->create([
            'email' => $client->email,
            'name' => $client->name,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.users.show', $client))
            ->assertOk()
            ->assertSee($client->email)
            ->assertSee($order->reference);
    }
}
