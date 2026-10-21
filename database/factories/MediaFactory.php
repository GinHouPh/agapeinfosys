<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::uuid()->toString().'.jpg';

        return [
            'disk' => 'public',
            'directory' => 'uploads/library',
            'filename' => $name,
            'original_name' => 'photo-'.$this->faker->word().'.jpg',
            'mime_type' => 'image/jpeg',
            'size' => $this->faker->numberBetween(10_000, 500_000),
            'width' => 1600,
            'height' => 900,
            'alt_text' => $this->faker->sentence(4),
            'title' => $this->faker->words(2, true),
            'focal_x' => 50,
            'focal_y' => 50,
            'is_public' => true,
            'created_by' => null,
        ];
    }
}
