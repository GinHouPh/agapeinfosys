<?php

namespace Database\Factories;

use App\Models\PageTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PageTemplate>
 */
class PageTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst($this->faker->words(2, true)).' Layout';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower($this->faker->unique()->bothify('??##')),
            'description' => $this->faker->sentence(8),
            'schema' => [
                'blocks' => [
                    [
                        'id' => Str::lower(Str::random(10)),
                        'widget' => 'hero',
                        'settings' => [
                            'title_top' => 'Welcome',
                            'title_accent' => 'to Agape',
                        ],
                    ],
                ],
            ],
            'is_active' => true,
            'usage_count' => 0,
        ];
    }
}
