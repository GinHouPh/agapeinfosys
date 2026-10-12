<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plan = Plan::factory()->create();

        return [
            'product_id' => $plan->product_id,
            'plan_id' => $plan->getKey(),
            'payment_gateway_id' => PaymentGateway::query()->first()?->getKey()
                ?? PaymentGateway::factory()->create()->getKey(),
            'subscriber_key' => $this->faker->unique()->uuid(),
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'amount' => $plan->price,
            'currency' => $plan->currency,
            'status' => OrderStatus::Pending,
            'expires_at' => now()->addHour(),
        ];
    }

    /**
     * Order that has been paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => OrderStatus::Paid,
            'paid_at' => now(),
            'gateway_payment_id' => 'pay_'.bin2hex(random_bytes(6)),
        ]);
    }

    /**
     * Order with a set amount in minor units.
     */
    public function amount(int $minorUnits): static
    {
        return $this->state(fn (array $attributes): array => [
            'amount' => $minorUnits,
        ]);
    }
}
