<?php

namespace Database\Factories;

use App\Models\DesignTheme;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DesignTheme>
 */
class DesignThemeFactory extends Factory
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
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower($this->faker->unique()->bothify('??##')),
            'description' => $this->faker->sentence(8),
            'is_default' => false,
            'is_active' => false,
            'tokens' => config('design.tokens', []),
        ];
    }

    /**
     * Switch the theme on.
     */
    public function active(): static
    {
        return $this->state(fn (): array => ['is_active' => true, 'is_default' => true]);
    }
}
