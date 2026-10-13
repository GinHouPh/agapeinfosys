<?php

namespace Database\Factories;

use App\Models\Form;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Form>
 */
class FormFactory extends Factory
{
    protected $model = Form::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => Str::title($this->faker->unique()->words(3, true)).' Form',
            'description' => $this->faker->sentence(),
            'submit_label' => 'Submit',
            'success_message' => null,
            'reference_prefix' => 'REQ',
            'is_active' => true,
        ];
    }

    /**
     * Draft forms stay off the public site.
     */
    public function draft(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }
}
