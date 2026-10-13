<?php

namespace Database\Factories;

use App\Enums\SubmissionStatus;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormSubmission>
 */
class FormSubmissionFactory extends Factory
{
    protected $model = FormSubmission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'answers' => [
                'name' => $this->faker->name(),
                'email' => $this->faker->safeEmail(),
            ],
            'status' => SubmissionStatus::New,
        ];
    }

    /**
     * Submissions still waiting for a first look.
     */
    public function reviewed(): static
    {
        return $this->state(fn (): array => [
            'status' => SubmissionStatus::Reviewing,
        ]);
    }
}
