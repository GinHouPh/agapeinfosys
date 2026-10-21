<?php

namespace Database\Factories;

use App\Models\Redirect;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Redirect>
 */
class RedirectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slug = Str::slug($this->faker->words(2, true));

        return [
            'from_path' => '/'.$slug,
            'to_path' => '/'.$slug.'-new',
            'status_code' => 301,
            'is_active' => true,
            'hits' => 0,
        ];
    }
}
