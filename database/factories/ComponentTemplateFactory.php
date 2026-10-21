<?php

namespace Database\Factories;

use App\Models\ComponentTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ComponentTemplate>
 */
class ComponentTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst($this->faker->words(2, true));

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower($this->faker->unique()->bothify('??##')),
            'category' => 'General',
            'widget' => 'section',
            'description' => $this->faker->sentence(8),
            'icon' => null,
            'schema' => [
                'blocks' => [
                    [
                        'id' => Str::lower(Str::random(10)),
                        'widget' => 'statement',
                        'settings' => [
                            'accent' => '{{heading}}',
                            'text' => '{{body}}',
                        ],
                    ],
                ],
            ],
            'slots' => [
                ['name' => 'heading', 'label' => 'Heading', 'type' => 'text', 'default' => 'Section title'],
                ['name' => 'body', 'label' => 'Body', 'type' => 'textarea', 'default' => ''],
            ],
            'is_active' => true,
            'usage_count' => 0,
        ];
    }
}
