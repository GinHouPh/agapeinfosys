<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\PageRevision;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PageRevision>
 */
class PageRevisionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'title' => $this->faker->sentence(3),
            'schema' => [
                'blocks' => [
                    [
                        'id' => 'abc123',
                        'widget' => 'statement',
                        'settings' => ['text' => 'An earlier version.'],
                    ],
                ],
            ],
            'note' => null,
            'created_by' => null,
        ];
    }
}
