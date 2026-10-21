<?php

namespace Database\Factories;

use App\Models\CollectionField;
use App\Models\CollectionType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CollectionField>
 */
class CollectionFieldFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $label = ucfirst($this->faker->word());

        return [
            'collection_type_id' => CollectionType::factory(),
            'name' => Str::slug($label, '_'),
            'label' => $label,
            'type' => 'text',
            'options' => null,
            'validation' => null,
            'is_required' => false,
            'sort_order' => 0,
        ];
    }
}
