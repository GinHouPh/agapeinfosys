<?php

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
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
            'subscriber_key' => $this->faker->unique()->uuid(),
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'status' => SubscriptionStatus::Active,
            'starts_at' => now(),
            'ends_at' => now()->addYear(),
        ];
    }

    /**
     * Subscription that has lapsed.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SubscriptionStatus::Expired,
            'ends_at' => now()->subDay(),
        ]);
    }
}
