<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Sync the permission catalogue into the database.
     */
    public function run(): void
    {
        foreach (Permission::catalogue() as $group => $permissions) {
            foreach ($permissions as $slug => $label) {
                Permission::query()->updateOrCreate(
                    ['slug' => $slug],
                    ['name' => $label, 'group' => $group],
                );
            }
        }
    }
}
