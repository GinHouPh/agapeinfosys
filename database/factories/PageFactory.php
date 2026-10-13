<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = ucfirst($this->faker->words(3, true));

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower($this->faker->unique()->bothify('??##')),
            'status' => 'draft',
            'meta_description' => $this->faker->sentence(12),
            'schema' => [
                'blocks' => [
                    [
                        'id' => Str::lower(Str::random(10)),
                        'widget' => 'statement',
                        'settings' => [
                            'accent' => 'Build faster',
                            'text' => 'with a page you can edit yourself.',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Mark the page as published.
     */
    public function published(): static
    {
        return $this->state(fn (): array => ['status' => 'published']);
    }
}
