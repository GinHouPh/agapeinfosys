<?php

namespace Database\Factories;

use App\Enums\RequirementStatus;
use App\Models\RequirementSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RequirementSubmission>
 */
class RequirementSubmissionFactory extends Factory
{
    protected $model = RequirementSubmission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'company' => $this->faker->company(),
            'industry' => $this->faker->randomElement(RequirementSubmission::INDUSTRY_OPTIONS),
            'project_name' => $this->faker->words(3, true),
            'brief' => $this->faker->paragraph(),
            'solutions' => $this->faker->randomElements(RequirementSubmission::SOLUTION_OPTIONS, 2),
            'features' => $this->faker->paragraph(),
            'integrations' => $this->faker->randomElements(RequirementSubmission::INTEGRATION_OPTIONS, 2),
            'user_scale' => $this->faker->randomElement(RequirementSubmission::USER_SCALE_OPTIONS),
            'budget' => $this->faker->randomElement(RequirementSubmission::BUDGET_OPTIONS),
            'timeline' => $this->faker->randomElement(RequirementSubmission::TIMELINE_OPTIONS),
            'notes' => $this->faker->sentence(),
            'status' => RequirementStatus::New,
        ];
    }

    /**
     * Submissions still waiting for a first look.
     */
    public function reviewed(): static
    {
        return $this->state(fn (): array => [
            'status' => RequirementStatus::Reviewing,
        ]);
    }
}
