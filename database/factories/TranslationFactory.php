<?php

namespace Database\Factories;

use App\Models\Locale;
use App\Models\Page;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Translation>
 */
class TranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'locale_id' => Locale::factory(),
            'translatable_type' => Page::class,
            'translatable_id' => Page::factory(),
            'field' => 'title',
            'value' => $this->faker->sentence(4),
        ];
    }
}
