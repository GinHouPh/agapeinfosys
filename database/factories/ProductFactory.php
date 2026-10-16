<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucwords($this->faker->unique()->words(2, true));

        return [
            'category_id' => null,
            'name' => $name,
            'slug' => Str::slug($name),
            'domain' => Str::slug($name).'.com',
            'description' => $this->faker->sentence(12),
            'callback_url' => 'https://'.Str::slug($name).'.com/webhooks/agape',
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 20),
        ];
    }

    /**
     * Place the product inside a category.
     */
    public function inCategory(Category $category): static
    {
        return $this->state(fn (array $attributes): array => [
            'category_id' => $category->getKey(),
        ]);
    }

    /**
     * Mark the product as hidden from checkout.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
