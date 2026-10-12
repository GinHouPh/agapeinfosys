<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\PaymentGateway;
use App\Models\ShopOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShopOrder>
 */
class ShopOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => ShopOrder::generateReference(),
            'payment_gateway_id' => PaymentGateway::query()->first()?->getKey()
                ?? PaymentGateway::factory()->create()->getKey(),
            'subscriber_key' => $this->faker->unique()->uuid(),
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'amount' => 49900,
            'currency' => 'INR',
            'status' => OrderStatus::Pending,
            'expires_at' => now()->addHour(),
        ];
    }

    /**
     * Shop order that has been paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => OrderStatus::Paid,
            'paid_at' => now(),
            'gateway_payment_id' => 'pay_'.bin2hex(random_bytes(6)),
        ]);
    }
}
