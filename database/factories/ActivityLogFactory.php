<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->admin(),
            'subject_type' => null,
            'subject_id' => null,
            'action' => $this->faker->randomElement(['created', 'updated', 'deleted']),
            'description' => $this->faker->sentence(8),
            'properties' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Factory',
        ];
    }
}
