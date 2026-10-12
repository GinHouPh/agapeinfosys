<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = ucfirst($this->faker->sentence(6));

        return [
            'author_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower($this->faker->unique()->bothify('??##')),
            'category' => $this->faker->randomElement(['Engineering', 'Cloud', 'Delivery', 'Strategy']),
            'excerpt' => $this->faker->sentence(16),
            'body' => implode("\n\n", $this->faker->paragraphs(4)),
            'cover_image' => null,
            'reading_time' => $this->faker->numberBetween(3, 9).' min read',
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'sort_order' => 0,
            'is_published' => true,
        ];
    }

    /**
     * Keep the article as an unpublished draft.
     */
    public function draft(): static
    {
        return $this->state(fn (): array => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }
}
