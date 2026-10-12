<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'role' => $this->faker->jobTitle(),
            'company' => $this->faker->company(),
            'avatar' => null,
            'quote' => $this->faker->paragraph(2),
            'rating' => $this->faker->numberBetween(4, 5),
            'linkedin_url' => null,
            'sort_order' => $this->faker->numberBetween(0, 50),
            'is_published' => true,
        ];
    }

    /**
     * Hide the testimonial from the public site.
     */
    public function hidden(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
