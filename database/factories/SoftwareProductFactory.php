<?php

namespace Database\Factories;

use App\Models\SoftwareProduct;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SoftwareProduct>
 */
class SoftwareProductFactory extends Factory
{
    protected $model = SoftwareProduct::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->randomNumber(4),
            'envato_item_id' => (string) fake()->numberBetween(10000000, 99999999),
            'is_active' => true,
            'metadata' => [
                'category' => 'software',
                'currency' => 'USD',
            ],
        ];
    }
}
