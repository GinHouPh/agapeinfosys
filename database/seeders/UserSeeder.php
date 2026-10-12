<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the control panel administrator.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@agapeinfosys.com');
        $password = env('ADMIN_PASSWORD', 'change-me-please');

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Agape Admin'),
                'password' => $password,
                'is_admin' => true,
            ],
        );
    }
}
