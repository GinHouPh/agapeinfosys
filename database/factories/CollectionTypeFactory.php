<?php

namespace Database\Factories;

use App\Models\CollectionType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CollectionType>
 */
class CollectionTypeFactory extends Factory
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
            'icon' => null,
            'route_pattern' => null,
            'is_active' => true,
            'is_record_template' => false,
            'created_by' => null,
        ];
    }
}
