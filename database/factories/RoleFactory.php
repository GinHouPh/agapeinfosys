<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst($this->faker->words(2, true)).' Role';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower($this->faker->unique()->bothify('??##')),
            'description' => $this->faker->sentence(8),
            'is_system' => false,
        ];
    }

    /**
     * Mark the role as built in.
     */
    public function system(): static
    {
        return $this->state(fn (): array => ['is_system' => true]);
    }
}
