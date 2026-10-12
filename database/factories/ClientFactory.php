<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'logo' => null,
            'website' => $this->faker->boolean(50) ? $this->faker->url() : null,
            'sort_order' => $this->faker->numberBetween(0, 50),
            'is_published' => true,
        ];
    }
}
