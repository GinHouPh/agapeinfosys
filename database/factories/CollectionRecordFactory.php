<?php

namespace Database\Factories;

use App\Models\CollectionRecord;
use App\Models\CollectionType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CollectionRecord>
 */
class CollectionRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = ucfirst($this->faker->words(2, true));

        return [
            'collection_type_id' => CollectionType::factory(),
            'slug' => Str::slug($title).'-'.Str::lower($this->faker->unique()->bothify('??##')),
            'title' => $title,
            'data' => [],
            'is_published' => false,
            'published_at' => null,
            'sort_order' => 0,
        ];
    }
}
