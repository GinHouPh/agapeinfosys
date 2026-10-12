<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\PaymentEvent;
use App\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentEvent>
 */
class PaymentEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_gateway_id' => PaymentGateway::query()->first()?->getKey()
                ?? PaymentGateway::factory()->create()->getKey(),
            'order_id' => Order::factory(),
            'type' => 'payment.completed',
            'direction' => 'inbound',
            'payload' => ['event' => 'payment.completed'],
            'is_verified' => true,
            'is_processed' => true,
            'received_at' => now(),
        ];
    }
}
