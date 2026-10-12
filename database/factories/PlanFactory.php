<?php

namespace Database\Factories;

use App\Enums\BillingType;
use App\Models\Plan;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => ucfirst($this->faker->unique()->word()).' Plan',
            'code' => $this->faker->unique()->bothify('plan_####'),
            'description' => $this->faker->sentence(10),
            'billing_type' => BillingType::OneTime,
            'price' => $this->faker->numberBetween(99, 9999) * 100,
            'currency' => 'INR',
            'validity_days' => 365,
            'interval_days' => null,
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 20),
        ];
    }

    /**
     * Recurring subscription plan.
     */
    public function recurring(): static
    {
        return $this->state(fn (array $attributes): array => [
            'billing_type' => BillingType::Recurring,
            'interval_days' => 30,
            'validity_days' => null,
        ]);
    }

    /**
     * Plan that is not offered at checkout.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
