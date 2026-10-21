<?php

namespace Database\Factories;

use App\Enums\MenuLocation;
use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst($this->faker->words(2, true)).' Menu';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower($this->faker->unique()->bothify('??##')),
            'location' => MenuLocation::Header,
            'description' => $this->faker->sentence(10),
            'is_active' => true,
        ];
    }

    /**
     * Park the menu in a specific location.
     */
    public function location(MenuLocation $location): static
    {
        return $this->state(fn (): array => ['location' => $location]);
    }

    /**
     * Switch the menu off.
     */
    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
