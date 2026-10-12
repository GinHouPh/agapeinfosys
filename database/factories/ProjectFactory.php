<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
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
            'client' => $this->faker->company(),
            'category' => $this->faker->randomElement(['Fintech', 'Healthcare', 'Retail', 'Logistics', 'Education']),
            'service_type' => $this->faker->randomElement(['End-to-end build', 'Modernisation', 'Consulting', 'Managed team']),
            'excerpt' => $this->faker->sentence(14),
            'body' => '<p>'.$this->faker->paragraph().'</p>',
            'cover_image' => null,
            'gallery' => null,
            'stack' => ['Laravel', 'PostgreSQL', 'Vue', 'AWS'],
            'outcomes' => [
                $this->faker->sentence(7),
                $this->faker->sentence(7),
            ],
            'project_url' => null,
            'year' => $this->faker->numberBetween(2019, 2026),
            'sort_order' => $this->faker->numberBetween(0, 50),
            'is_featured' => $this->faker->boolean(40),
            'is_published' => true,
        ];
    }

    /**
     * Mark the case study as an unpublished draft.
     */
    public function draft(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
