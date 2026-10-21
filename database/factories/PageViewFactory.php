<?php

namespace Database\Factories;

use App\Models\PageView;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PageView>
 */
class PageViewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'path' => '/'.ltrim($this->faker->word(), '-'),
            'method' => 'GET',
            'status' => 200,
            'referrer' => null,
            'visitor' => hash('sha256', $this->faker->unique()->uuid()),
            'ip_hash' => hash('sha256', $this->faker->unique()->uuid()),
            'user_agent' => 'Factory',
            'user_id' => null,
            'created_at' => now(),
        ];
    }
}
