<?php

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slug = $this->faker->unique()->slug(2);

        return [
            'name' => ucfirst(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'group' => $this->faker->randomElement(['Content', 'Inbox', 'Commerce', 'Clients', 'System']),
            'description' => $this->faker->sentence(8),
        ];
    }
}
