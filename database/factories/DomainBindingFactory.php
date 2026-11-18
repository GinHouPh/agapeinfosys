<?php

namespace Database\Factories;

use App\Models\DomainBinding;
use App\Models\License;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DomainBinding>
 */
class DomainBindingFactory extends Factory
{
    protected $model = DomainBinding::class;

    public function definition(): array
    {
        return [
            'license_id' => License::factory(),
            'domain' => fake()->unique()->domainName(),
            'ip_address' => fake()->ipv4(),
            'installed_version' => '1.0.0',
            'status' => 'active',
            'activated_at' => now(),
            'last_heartbeat_at' => now(),
        ];
    }
}
