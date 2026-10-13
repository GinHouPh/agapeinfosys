<?php

namespace Database\Factories;

use App\Enums\FormFieldType;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormField>
 */
class FormFieldFactory extends Factory
{
    protected $model = FormField::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $label = ucfirst($this->faker->unique()->words(2, true));

        return [
            'form_id' => Form::factory(),
            'label' => $label,
            'name' => FormField::nameFromLabel($label),
            'type' => FormFieldType::Text,
            'group' => null,
            'options' => null,
            'placeholder' => null,
            'help_text' => null,
            'is_required' => false,
            'sort_order' => 0,
        ];
    }

    /**
     * A dropdown question with predefined options.
     */
    public function withOptions(array $options, FormFieldType $type = FormFieldType::Select): static
    {
        return $this->state(fn (): array => [
            'type' => $type,
            'options' => $options,
        ]);
    }

    /**
     * A required question.
     */
    public function required(): static
    {
        return $this->state(fn (): array => [
            'is_required' => true,
        ]);
    }
}
