<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SettingSeeder::class,
            ServiceSeeder::class,
            ProjectSeeder::class,
            PostSeeder::class,
            TestimonialSeeder::class,
            ClientSeeder::class,
            CommerceSeeder::class,
            ShowcaseProjectSeeder::class,
        ]);
    }
}
