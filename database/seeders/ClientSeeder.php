<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Seed the client logo marquee.
     */
    public function run(): void
    {
        $names = [
            'Meridian Logistics',
            'Northgate Health',
            'Brightside Retail',
            'Halden Financial',
            'Vantage Energy',
            'Corefield Systems',
            'Aurora Learning',
            'Ridgeway Foods',
        ];

        foreach ($names as $index => $name) {
            Client::query()->updateOrCreate(
                ['name' => $name],
                [
                    'logo' => null,
                    'website' => null,
                    'sort_order' => $index + 1,
                    'is_published' => true,
                ],
            );
        }
    }
}
