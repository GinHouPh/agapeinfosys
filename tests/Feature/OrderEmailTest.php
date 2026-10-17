<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Mail\OrderReceipt;
use App\Mail\ShopOrderReceipt;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ShopOrder;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Services\Payments\CheckoutService as OrderCheckoutService;
use App\Services\Shop\CheckoutService as ShopCheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Outbound product webhooks must never hit the network in tests.
        Queue::fake();
    }

    private function sandboxGateway(): PaymentGateway
    {
        return PaymentGateway::factory()->sandbox()->create();
    }

    /**
     * @return array{0: Product, 1: Plan}
     */
    private function productWithPlan(string $code = 'pro'): array
    {
        $product = Product::factory()->create(['is_active' => true]);

        $plan = Plan::factory()->create([
            'product_id' => $product->getKey(),
            'code' => $code,
            'name' => ucfirst($code).' plan',
            'price' => 49900,
            'currency' => 'INR',
        ]);

        return [$product, $plan];
    }

    private function paidSingleOrder(string $email = 'buyer@example.com'): array
    {
        [$product, $plan] = $this->productWithPlan();

        $order = Order::factory()->create([
            'product_id' => $product->getKey(),
            'plan_id' => $plan->getKey(),
            'payment_gateway_id' => PaymentGateway::query()->where('driver', 'sandbox')->sole()->getKey(),
            'subscriber_key' => 'user-42',
            'name' => 'Buyer',
            'email' => $email,
            'status' => OrderStatus::Pending,
        ]);

        return [$product, $plan, $order];
    }

    public function test_paying_a_single_order_emails_the_buyer_a_receipt(): void
    {
        Mail::fake();
        $this->sandboxGateway();
        [, , $order] = $this->paidSingleOrder('buyer@example.com');

        $this->assertTrue(app(OrderCheckoutService::class)->complete($order));

        Mail::assertQueued(
            OrderReceipt::class,
            fn (OrderReceipt $mail): bool => $mail->hasTo('buyer@example.com')
        );
    }

    public function test_paying_a_single_order_notifies_the_admins(): void
    {
        Notification::fake();
        $this->sandboxGateway();

        $admin = User::factory()->create(['is_admin' => true]);
        [, , $order] = $this->paidSingleOrder();

        app(OrderCheckoutService::class)->complete($order);

        Notification::assertSentTo($admin, NewOrderNotification::class);
    }

    public function test_an_order_without_an_email_is_completed_without_a_receipt(): void
    {
        Mail::fake();
        $this->sandboxGateway();
        [, , $order] = $this->paidSingleOrder();

        $order->forceFill(['email' => null])->save();

        $this->assertTrue(app(OrderCheckoutService::class)->complete($order));

        Mail::assertNothingQueued();
    }

    public function test_a_cart_order_emails_exactly_one_receipt(): void
    {
        Mail::fake();
        $this->sandboxGateway();

        [$productA, $planA] = $this->productWithPlan('pro');
        [$productB, $planB] = $this->productWithPlan('basic');

        $shopOrder = ShopOrder::factory()->create([
            'payment_gateway_id' => PaymentGateway::query()->where('driver', 'sandbox')->sole()->getKey(),
            'subscriber_key' => 'user-42',
            'name' => 'Buyer',
            'email' => 'buyer@example.com',
            'amount' => $planA->price + $planB->price,
            'currency' => 'INR',
            'status' => OrderStatus::Pending,
        ]);

        foreach ([[$productA, $planA], [$productB, $planB]] as [$product, $plan]) {
            $shopOrder->items()->create([
                'product_id' => $product->getKey(),
                'plan_id' => $plan->getKey(),
                'product_name' => $product->name,
                'plan_name' => $plan->name,
                'unit_price' => $plan->price,
                'total' => $plan->price,
                'currency' => $plan->currency,
            ]);
        }

        $this->assertTrue(app(ShopCheckoutService::class)->complete($shopOrder->fresh('items')));

        // One cart receipt for the buyer, never one per fanned-out line.
        Mail::assertQueued(ShopOrderReceipt::class, 1);
        Mail::assertNotQueued(OrderReceipt::class);
    }

    public function test_the_receipt_renders_the_order_details(): void
    {
        $this->sandboxGateway();
        [, , $order] = $this->paidSingleOrder();

        $order->forceFill(['status' => OrderStatus::Paid, 'paid_at' => now()])->save();

        $mailable = new OrderReceipt($order->fresh());

        $mailable->assertSeeInHtml('Payment received');
        $mailable->assertSeeInHtml($order->reference);
    }

    public function test_the_cart_receipt_lists_every_line(): void
    {
        $this->sandboxGateway();
        [$product, $plan] = $this->productWithPlan('pro');

        $shopOrder = ShopOrder::factory()->paid()->create([
            'payment_gateway_id' => PaymentGateway::query()->where('driver', 'sandbox')->sole()->getKey(),
            'email' => 'buyer@example.com',
        ]);

        $shopOrder->items()->create([
            'product_id' => $product->getKey(),
            'plan_id' => $plan->getKey(),
            'product_name' => 'Tongdon',
            'plan_name' => 'Pro plan',
            'unit_price' => 49900,
            'total' => 49900,
            'currency' => 'INR',
        ]);

        $mailable = new ShopOrderReceipt($shopOrder->fresh('items'));

        $mailable->assertSeeInHtml('Tongdon');
        $mailable->assertSeeInHtml('Pro plan');
        $mailable->assertSeeInHtml($shopOrder->reference);
    }
}
