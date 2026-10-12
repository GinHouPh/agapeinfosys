<?php

namespace Database\Factories;

use App\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentGateway>
 */
class PaymentGatewayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'driver' => 'razorpay',
            'name' => 'Razorpay',
            'is_enabled' => false,
            'is_default' => false,
            'mode' => 'test',
            'test_config' => null,
            'live_config' => null,
        ];
    }

    /**
     * Configured sandbox gateway, enabled and default.
     */
    public function sandbox(): static
    {
        return $this->state(fn (array $attributes): array => [
            'driver' => 'sandbox',
            'name' => 'Sandbox (simulator)',
            'is_enabled' => true,
            'is_default' => true,
            'mode' => 'test',
        ]);
    }

    /**
     * Enabled with test-mode credentials.
     */
    public function configured(array $config): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_enabled' => true,
            'test_config' => $config,
        ]);
    }
}
