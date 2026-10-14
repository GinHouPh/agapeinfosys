<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClientDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SettingSeeder::class);
    }

    public function test_the_overview_summarises_the_clients_purchases(): void
    {
        $client = User::factory()->create(['email' => 'client@example.com']);

        Order::factory()->paid()->create(['email' => $client->email]);
        Subscription::factory()->create([
            'email' => $client->email,
            'status' => SubscriptionStatus::Active,
        ]);

        $this->actingAs($client)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('Paid orders')
            ->assertSee('Active subscriptions')
            ->assertSee($client->name);
    }

    public function test_the_orders_page_only_lists_the_clients_own_orders(): void
    {
        $client = User::factory()->create(['email' => 'client@example.com']);

        $mine = Order::factory()->paid()->create([
            'email' => $client->email,
            'name' => $client->name,
        ]);
        $theirs = Order::factory()->paid()->create(['email' => 'someone-else@example.com']);

        $this->actingAs($client)
            ->get(route('dashboard.orders.index'))
            ->assertOk()
            ->assertSee($mine->reference)
            ->assertDontSee($theirs->reference);
    }

    public function test_an_order_receipt_is_hidden_from_other_clients(): void
    {
        $client = User::factory()->create(['email' => 'client@example.com']);
        $order = Order::factory()->paid()->create(['email' => 'someone-else@example.com']);

        $this->actingAs($client)
            ->get(route('dashboard.orders.show', $order))
            ->assertNotFound();
    }

    public function test_a_client_can_open_their_own_receipt(): void
    {
        $client = User::factory()->create(['email' => 'client@example.com']);
        $order = Order::factory()->paid()->create(['email' => $client->email]);

        $this->actingAs($client)
            ->get(route('dashboard.orders.show', $order))
            ->assertOk()
            ->assertSee($order->reference)
            ->assertSee('Paid');
    }

    public function test_a_client_can_cancel_an_active_subscription(): void
    {
        $client = User::factory()->create(['email' => 'client@example.com']);
        $subscription = Subscription::factory()->create([
            'email' => $client->email,
            'status' => SubscriptionStatus::Active,
        ]);

        $this->actingAs($client)
            ->from(route('dashboard.subscriptions.index'))
            ->post(route('dashboard.subscriptions.cancel', $subscription))
            ->assertRedirect(route('dashboard.subscriptions.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => SubscriptionStatus::Cancelled->value,
        ]);

        $this->assertNotNull($subscription->fresh()->cancelled_at);
    }

    public function test_cancelling_an_inactive_subscription_changes_nothing(): void
    {
        $client = User::factory()->create(['email' => 'client@example.com']);
        $subscription = Subscription::factory()->expired()->create(['email' => $client->email]);

        $this->actingAs($client)
            ->post(route('dashboard.subscriptions.cancel', $subscription))
            ->assertRedirect();

        $this->assertSame(SubscriptionStatus::Expired, $subscription->fresh()->status);
        $this->assertNull($subscription->fresh()->cancelled_at);
    }

    public function test_a_client_cannot_cancel_someone_elses_subscription(): void
    {
        $client = User::factory()->create();
        $subscription = Subscription::factory()->create(['email' => 'someone-else@example.com']);

        $this->actingAs($client)
            ->post(route('dashboard.subscriptions.cancel', $subscription))
            ->assertNotFound();
    }

    public function test_the_access_page_shows_entitlements_from_both_purchase_types(): void
    {
        $client = User::factory()->create(['email' => 'client@example.com']);

        $subscribedProduct = Subscription::factory()->create([
            'email' => $client->email,
            'status' => SubscriptionStatus::Active,
        ])->product;

        $oneTimeOrder = Order::factory()->paid()->create(['email' => $client->email]);

        $response = $this->actingAs($client)
            ->get(route('dashboard.access.index'))
            ->assertOk()
            ->assertSee('Access active')
            ->assertSee($subscribedProduct->name, false)
            ->assertSee($oneTimeOrder->product->name, false);

        $this->assertStringContainsString('From subscriptions', $response->getContent());
        $this->assertStringContainsString('From one-off purchases', $response->getContent());
    }

    public function test_the_access_page_explains_the_delivery_timeline_when_empty(): void
    {
        $client = User::factory()->create();

        $this->actingAs($client)
            ->get(route('dashboard.access.index'))
            ->assertOk()
            ->assertSee('No access yet', false);
    }

    public function test_a_client_can_update_their_profile(): void
    {
        $client = User::factory()->create(['phone' => null]);

        $this->actingAs($client)
            ->put(route('dashboard.profile.update'), [
                'name' => 'Updated Name',
                'email' => $client->email,
                'phone' => '+91 98765 43210',
            ])
            ->assertRedirect(route('dashboard.profile.edit'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'id' => $client->id,
            'name' => 'Updated Name',
            'phone' => '+91 98765 43210',
        ]);
    }

    public function test_the_profile_email_must_be_unique(): void
    {
        $client = User::factory()->create();
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($client)
            ->from(route('dashboard.profile.edit'))
            ->put(route('dashboard.profile.update'), [
                'name' => $client->name,
                'email' => 'taken@example.com',
            ])
            ->assertRedirect(route('dashboard.profile.edit'))
            ->assertSessionHasErrors('email');
    }

    public function test_a_client_can_change_their_password(): void
    {
        $client = User::factory()->create([
            'email' => 'client@example.com',
            'password' => 'current-password',
        ]);

        $this->actingAs($client)
            ->put(route('dashboard.password.update'), [
                'current_password' => 'current-password',
                'password' => 'brand-new-password',
                'password_confirmation' => 'brand-new-password',
            ])
            ->assertRedirect(route('dashboard.profile.edit'))
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('brand-new-password', $client->fresh()->password));
    }

    public function test_changing_the_password_requires_the_current_one(): void
    {
        $client = User::factory()->create(['password' => 'current-password']);

        $this->actingAs($client)
            ->from(route('dashboard.profile.edit'))
            ->put(route('dashboard.password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'brand-new-password',
                'password_confirmation' => 'brand-new-password',
            ])
            ->assertRedirect(route('dashboard.profile.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('current-password', $client->fresh()->password));
    }

    public function test_the_dashboard_navigation_signs_out(): void
    {
        $client = User::factory()->create();

        $this->actingAs($client)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee(route('logout'), false)
            ->assertSee('Subscriptions', false);
    }

    public function test_an_expired_subscription_stops_granting_access(): void
    {
        $client = User::factory()->create(['email' => 'client@example.com']);

        $subscription = Subscription::factory()->expired()->create(['email' => $client->email]);

        $this->actingAs($client)
            ->get(route('dashboard.subscriptions.index'))
            ->assertOk()
            ->assertSee(SubscriptionStatus::Expired->label(), false)
            ->assertDontSee(route('dashboard.subscriptions.cancel', $subscription), false);
    }

    public function test_a_pending_order_is_reported_as_awaiting_payment(): void
    {
        $client = User::factory()->create(['email' => 'client@example.com']);

        Order::factory()->create([
            'email' => $client->email,
            'status' => OrderStatus::Pending,
        ]);

        $this->actingAs($client)
            ->get(route('dashboard.orders.index'))
            ->assertOk()
            ->assertSee('Pending', false);
    }
}
