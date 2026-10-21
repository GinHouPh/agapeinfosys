<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Seed the built-in roles and the permissions each one grants.
     */
    public function run(): void
    {
        $this->call(PermissionSeeder::class);

        $this->seedRole(
            'Superadmin',
            'Every capability on the platform. Equivalent to the superuser flag.',
            Permission::slugs(),
        );

        $this->seedRole(
            'Content Editor',
            'Writes and edits content, but cannot publish it or touch commerce.',
            [
                'dashboard.view',
                'pages.manage',
                'navigation.manage',
                'services.manage',
                'projects.manage',
                'showcase.manage',
                'posts.manage',
                'testimonials.manage',
                'forms.manage',
                'media.manage',
                'templates.manage',
            ],
        );

        $this->seedRole(
            'Support',
            'Reads the inboxes and the client list. Changes nothing.',
            [
                'dashboard.view',
                'messages.manage',
                'submissions.manage',
                'users.manage',
                'orders.view',
            ],
        );

        $this->seedRole(
            'Commerce',
            'Runs the shop: products, plans, orders and gateways.',
            [
                'dashboard.view',
                'products.manage',
                'categories.manage',
                'plans.manage',
                'orders.view',
                'subscriptions.view',
                'gateways.manage',
            ],
        );

        $this->seedRole(
            'Designer',
            'Owns the design system, templates and the page builder.',
            [
                'dashboard.view',
                'pages.manage',
                'navigation.manage',
                'design.manage',
                'templates.manage',
                'media.manage',
                'seo.manage',
            ],
        );
    }

    /**
     * Create — or refresh — one system role.
     *
     * @param  array<int, string>  $slugs
     */
    private function seedRole(string $name, string $description, array $slugs): void
    {
        $role = Role::query()->updateOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'description' => $description, 'is_system' => true],
        );

        $role->permissions()->sync(
            Permission::query()->whereIn('slug', $slugs)->pluck('id')->all(),
        );
    }
}
