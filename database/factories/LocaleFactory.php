<?php

namespace Database\Factories;

use App\Models\Locale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Locale>
 */
class LocaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = $this->faker->unique()->languageCode();

        return [
            'code' => $code,
            'name' => $this->faker->languageCode(),
            'native_name' => ucfirst($code),
            'direction' => 'ltr',
            'is_default' => false,
            'is_active' => true,
        ];
    }

    /**
     * Make the locale the site's default.
     */
    public function default(): static
    {
        return $this->state(fn (): array => ['is_default' => true]);
    }
}
