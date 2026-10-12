<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\ShowcaseProject;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ShowcaseProject>
 */
class ShowcaseProjectFactory extends Factory
{
    protected $model = ShowcaseProject::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => Str::title($this->faker->unique()->words(3, true)),
            'industry' => $this->faker->randomElement([
                'OTT & Media',
                'Appointment Booking',
                'Hospital Management',
                'POS & Retail',
                'Education & E-learning',
                'Logistics & Fleet',
                'FinTech & Banking',
                'E-commerce',
            ]),
            'status' => ProjectStatus::Ongoing,
            'tagline' => $this->faker->sentence(9),
            'description' => $this->faker->paragraph(),
            'poster' => null,
            'stack' => $this->faker->randomElements(
                ['Laravel', 'Vue', 'React Native', 'Flutter', 'Node.js', 'Tailwind', 'MySQL', 'AWS'],
                3,
            ),
            'expected_launch' => null,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    /**
     * Projects currently being built launch with an expected date.
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProjectStatus::Upcoming,
            'expected_launch' => $this->faker->dateTimeBetween('+2 months', '+9 months'),
        ]);
    }

    /**
     * Draft projects stay off the public wall.
     */
    public function hidden(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }
}
