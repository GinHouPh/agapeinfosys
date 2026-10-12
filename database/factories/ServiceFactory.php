<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
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
            'eyebrow' => $this->faker->randomElement(['Engineering', 'Consulting', 'Design', 'Platform']),
            'icon' => null,
            'excerpt' => $this->faker->sentence(12),
            'summary' => $this->faker->paragraph(),
            'body' => '<p>'.$this->faker->paragraph().'</p>',
            'highlights' => [
                $this->faker->sentence(6),
                $this->faker->sentence(6),
                $this->faker->sentence(6),
            ],
            'image' => null,
            'sort_order' => $this->faker->numberBetween(0, 50),
            'is_featured' => $this->faker->boolean(40),
            'is_published' => true,
        ];
    }

    /**
     * Mark the service as an unpublished draft.
     */
    public function draft(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
