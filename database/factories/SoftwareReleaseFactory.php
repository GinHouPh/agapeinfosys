<?php

namespace Database\Factories;

use App\Models\SoftwareProduct;
use App\Models\SoftwareRelease;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SoftwareRelease>
 */
class SoftwareReleaseFactory extends Factory
{
    protected $model = SoftwareRelease::class;

    public function definition(): array
    {
        return [
            'product_id' => SoftwareProduct::factory(),
            'version' => '2.5.'.fake()->unique()->numberBetween(1, 99),
            'min_php_version' => '8.2.0',
            'changelog' => fake()->paragraph(),
            'file_path' => 'releases/test-release.zip',
            'file_size_bytes' => 1024000,
            'sha256_checksum' => hash('sha256', fake()->text()),
            'is_prerelease' => false,
            'is_active' => true,
        ];
    }
}
