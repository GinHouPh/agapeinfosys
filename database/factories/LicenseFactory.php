<?php

namespace Database\Factories;

use App\Models\License;
use App\Models\SoftwareProduct;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<License>
 */
class LicenseFactory extends Factory
{
    protected $model = License::class;

    public function definition(): array
    {
        return [
            'product_id' => SoftwareProduct::factory(),
            'license_key' => 'AGAPE-SRS-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)),
            'channel' => 'agape_direct',
            'buyer_email' => fake()->safeEmail(),
            'buyer_username' => fake()->userName(),
            'tier' => 'regular',
            'max_domains' => 1,
            'support_until' => now()->addMonths(6),
            'expires_at' => null,
            'status' => 'active',
            'client_secret_salt' => Str::random(32),
        ];
    }

    public function envato(): static
    {
        return $this->state(fn (array $attributes) => [
            'channel' => 'envato',
            'license_key' => fake()->uuid(),
        ]);
    }

    public function extended(): static
    {
        return $this->state(fn (array $attributes) => [
            'tier' => 'extended',
            'max_domains' => 3,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }
}
