<?php

namespace Database\Seeders;

use App\Enums\MenuItemType;
use App\Enums\MenuLocation;
use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Seed the navigation menus with the links the site shipped with.
     */
    public function run(): void
    {
        $this->seedMenu(
            MenuLocation::Header,
            'Main Navigation',
            'primary-header',
            'The links shown in the site header and the mobile panel.',
            [
                ['label' => 'Home', 'route' => 'home', 'icon' => 'home'],
                ['label' => 'About', 'route' => 'about', 'icon' => 'about'],
                ['label' => 'Services', 'route' => 'services.index', 'icon' => 'services'],
                ['label' => 'Shop', 'route' => 'shop.index', 'icon' => 'shop'],
                ['label' => 'Projects', 'route' => 'projects.index', 'icon' => 'projects'],
                ['label' => 'Portfolio', 'route' => 'portfolio.index', 'icon' => 'portfolio'],
                ['label' => 'Blog', 'route' => 'blog.index', 'icon' => 'blog'],
                ['label' => 'Contact', 'route' => 'contact.create', 'icon' => 'contact'],
            ],
        );

        $this->seedMenu(
            MenuLocation::Footer,
            'Footer Pages',
            'footer-pages',
            'The pages column shown in the site footer.',
            [
                ['label' => 'Home', 'route' => 'home'],
                ['label' => 'About Us', 'route' => 'about'],
                ['label' => 'Services', 'route' => 'services.index'],
                ['label' => 'Projects', 'route' => 'projects.index'],
                ['label' => 'Case Studies', 'route' => 'portfolio.index'],
                ['label' => 'Blog', 'route' => 'blog.index'],
                ['label' => 'Requirement Brief', 'route' => 'requirements.create'],
            ],
        );

        $this->seedMenu(
            MenuLocation::Legal,
            'Legal Links',
            'legal-links',
            'The policy links in the footer bar.',
            [
                ['label' => 'Privacy Policy', 'route' => 'legal.privacy'],
                ['label' => 'Terms & Conditions', 'route' => 'legal.terms'],
                ['label' => 'Cancellation & Refunds', 'route' => 'legal.refunds'],
                ['label' => 'Delivery Policy', 'route' => 'legal.delivery'],
            ],
        );
    }

    /**
     * Create — or refresh — one menu and its items.
     *
     * @param  array<int, array{label: string, route: string, icon?: string}>  $items
     */
    private function seedMenu(MenuLocation $location, string $name, string $slug, string $description, array $items): void
    {
        $menu = Menu::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'location' => $location,
                'description' => $description,
                'is_active' => true,
            ],
        );

        $menu->items()->delete();

        foreach ($items as $index => $item) {
            $menu->items()->create([
                'label' => $item['label'],
                'link_type' => MenuItemType::Route,
                'link_value' => $item['route'],
                'icon' => $item['icon'] ?? null,
                'target' => '_self',
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }
}
