<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed the storefront category tree.
     *
     * Mirrors the Envato market switcher (Web Themes & Templates, Code,
     * Video, Audio, Graphics, Photos, 3D Files, AI Tools & Unlimited Stock
     * Assets). The "Code" branch reproduces CodeCanyon's categories and
     * subcategories exactly.
     */
    public function run(): void
    {
        foreach ($this->tree() as $index => $node) {
            $this->createNode($node, null, $index);
        }
    }

    /**
     * @param  array{name: string, description?: string, children?: array<int, array<string, mixed>>}  $node
     */
    private function createNode(array $node, ?int $parentId, int $sortOrder): void
    {
        $category = Category::updateOrCreate(
            ['parent_id' => $parentId, 'name' => $node['name']],
            [
                'description' => $node['description'] ?? null,
                'is_active' => true,
                'sort_order' => $sortOrder,
            ]
        );

        foreach ($node['children'] ?? [] as $index => $child) {
            $this->createNode($child, $category->getKey(), $index);
        }
    }

    /**
     * @return array<int, array{name: string, description?: string, children?: array<int, array<string, mixed>>}>
     */
    private function tree(): array
    {
        return [
            [
                'name' => 'Web Themes & Templates',
                'description' => 'Ready-made website themes and templates.',
                'children' => $this->names([
                    'WordPress', 'HTML', 'Marketing', 'CMS Templates', 'Site Templates',
                    'Jamstack', 'Blogging', 'Retail', 'Technology', 'Entertainment', 'Miscellaneous',
                ]),
            ],
            [
                'name' => 'Code',
                'description' => 'Scripts, plugins, and code from CodeCanyon.',
                'children' => [
                    ['name' => 'All Items', 'children' => $this->names([
                        'Popular Files', 'Featured Files', 'Top New Files', 'Follow Feed',
                        'Top Authors', 'Top New Authors', 'Public Collections', 'View All Categories',
                    ])],
                    ['name' => 'PHP Scripts', 'children' => $this->names([
                        'Add-ons', 'Calendars', 'Countdowns', 'Database Abstractions', 'Forms',
                        'Help and Support Tools', 'Images and Media', 'Loaders and Uploaders',
                        'Navigation', 'News Tickers', 'Polls', 'Project Management Tools',
                        'Ratings and Charts', 'Search', 'Shopping Carts', 'Social Networking',
                        'Miscellaneous',
                    ])],
                    ['name' => 'WordPress', 'children' => $this->names([
                        'Add-ons', 'Advertising', 'Calendars', 'eCommerce', 'Elementor', 'Forms',
                        'Forums', 'Galleries', 'Interface Elements', 'Media', 'Membership',
                        'Newsletters', 'SEO', 'Social Networking', 'Utilities', 'Widgets',
                        'Miscellaneous',
                    ])],
                    ['name' => 'eCommerce', 'children' => $this->names([
                        'Easy Digital Downloads', 'Jigoshop', 'Magento Extensions', 'OpenCart',
                        'osCommerce', 'Prestashop', 'UberCart', 'VirtueMart', 'WooCommerce',
                        'WP e-Commerce', 'WP Standalone', 'Zen Cart', 'Miscellaneous',
                    ])],
                    ['name' => 'JavaScript', 'children' => $this->names([
                        'Animated SVGs', 'Calendars', 'Countdowns', 'Database Abstractions', 'Forms',
                        'Images and Media', 'Loaders and Uploaders', 'Media', 'Navigation',
                        'News Tickers', 'Project Management Tools', 'Ratings and Charts',
                        'Shopping Carts', 'Sliders', 'Social Networks', 'Miscellaneous',
                    ])],
                    ['name' => 'CSS', 'children' => $this->names([
                        'Animations and Effects', 'Buttons', 'Charts and Graphs', 'Forms', 'Layouts',
                        'Navigation and Menus', 'Pricing Tables', 'Tabs and Sliders', 'Miscellaneous',
                    ])],
                    ['name' => 'Mobile', 'children' => $this->names([
                        'Android', 'Flutter', 'iOS', 'Native Web', 'Titanium',
                    ])],
                    ['name' => 'HTML5', 'children' => $this->names([
                        '3D', 'Ad Templates', 'Canvas', 'Charts and Graphs', 'Forms', 'Games',
                        'Libraries', 'Media', 'Presentations', 'Sliders', 'Storage', 'Templates',
                        'Miscellaneous',
                    ])],
                    ['name' => 'AI Tools', 'children' => $this->names([
                        'AI Writers and Content Generators',
                        'AI Image and Video Generators',
                        'AI Chatbots',
                    ])],
                    ['name' => 'WP Themes'],
                    ['name' => 'Plugins', 'children' => $this->names([
                        'Concrete5', 'Drupal', 'ExpressionEngine', 'Joomla', 'Magento Extensions',
                        'Muse Widgets', 'OpenCart', 'osCommerce', 'Prestashop', 'Ubercart',
                        'VirtueMart', 'Zen Cart', 'Miscellaneous',
                    ])],
                    ['name' => 'Client Workspace'],
                    ['name' => 'More', 'children' => $this->names([
                        '.NET', 'Apps', 'Facebook',
                    ])],
                ],
            ],
            [
                'name' => 'Video',
                'description' => 'Stock footage, motion graphics, and video templates.',
                'children' => $this->names([
                    'Stock Footage', 'After Effects', 'Premiere Pro', 'Motion Graphics',
                    'Apple Motion', 'Cinema 4D', 'Video Displays', 'Add-ons', 'Miscellaneous',
                ]),
            ],
            [
                'name' => 'Audio',
                'description' => 'Music, sound effects, and audio tools.',
                'children' => $this->names([
                    'Music', 'Sound Effects', 'Audio Tools', 'Logos & Idents', 'Miscellaneous',
                ]),
            ],
            [
                'name' => 'Graphics',
                'description' => 'Web graphics, print templates, and design assets.',
                'children' => $this->names([
                    'Web Graphics', 'Print Templates', 'Icons', 'Infographics', 'Add-ons',
                    'Miscellaneous',
                ]),
            ],
            [
                'name' => 'Photos',
                'description' => 'Royalty-free photography.',
                'children' => $this->names([
                    'Nature', 'Business', 'People', 'Technology', 'Abstract', 'Lifestyle',
                    'Miscellaneous',
                ]),
            ],
            [
                'name' => '3D Files',
                'description' => '3D models, materials, and textures.',
                'children' => $this->names([
                    '3D Models', 'Materials & Textures', 'Motion Graphics', 'Add-ons',
                    'Miscellaneous',
                ]),
            ],
            [
                'name' => 'AI Tools & Unlimited Stock Assets',
                'description' => 'All-in-one creative subscription with AI tools and unlimited assets.',
                'children' => $this->names([
                    'AI Tools', 'Stock Assets',
                ]),
            ],
        ];
    }

    /**
     * Turn a list of names into child nodes.
     *
     * @param  array<int, string>  $names
     * @return array<int, array{name: string}>
     */
    private function names(array $names): array
    {
        return array_map(fn (string $name): array => ['name' => $name], $names);
    }
}
