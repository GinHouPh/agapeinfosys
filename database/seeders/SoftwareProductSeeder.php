<?php

namespace Database\Seeders;

use App\Models\SoftwareProduct;
use App\Models\SoftwareRelease;
use Illuminate\Database\Seeder;

class SoftwareProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $srsGenie = SoftwareProduct::firstOrCreate(
            ['slug' => 'srs-genie'],
            [
                'name' => 'SRS Genie - AI Software Requirements Specification Generator',
                'envato_item_id' => '55234981',
                'is_active' => true,
                'metadata' => [
                    'category' => 'ai-saas',
                    'currency' => 'USD',
                    'support_url' => 'https://agapeinfosys.com/support',
                ],
            ]
        );

        SoftwareRelease::firstOrCreate(
            [
                'product_id' => $srsGenie->id,
                'version' => '2.5.0',
            ],
            [
                'min_php_version' => '8.2.0',
                'changelog' => "### Version 2.5.0\n- Initial Production Release\n- 22-tier dynamic architecture specification builder\n- MCP Gateway multi-agent connection layer\n- 1-Click Update System",
                'file_path' => 'releases/srs-genie-v2.5.0.zip',
                'file_size_bytes' => 15728640,
                'sha256_checksum' => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
                'is_prerelease' => false,
                'is_active' => true,
            ]
        );
    }
}
