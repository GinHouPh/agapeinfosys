<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => null,
            'name' => ucfirst($this->faker->unique()->words(2, true)),
            'slug' => null,
            'description' => $this->faker->sentence(8),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 20),
        ];
    }

    /**
     * Nest the category under a parent.
     */
    public function childOf(Category $parent): static
    {
        return $this->state(fn (array $attributes): array => [
            'parent_id' => $parent->getKey(),
        ]);
    }

    /**
     * Category that is hidden from the storefront.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
