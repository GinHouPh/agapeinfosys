<?php

namespace Database\Factories;

use App\Models\FeatureFlag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<FeatureFlag>
 */
class FeatureFlagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst($this->faker->words(2, true));

        return [
            'key' => Str::slug($name).'-'.Str::lower($this->faker->unique()->bothify('??##')),
            'name' => $name,
            'description' => $this->faker->sentence(8),
            'is_enabled' => false,
            'rollout_percentage' => 100,
            'starts_at' => null,
            'ends_at' => null,
        ];
    }

    /**
     * Switch the flag on.
     */
    public function on(): static
    {
        return $this->state(fn (): array => ['is_enabled' => true]);
    }
}
