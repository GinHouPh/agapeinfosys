<?php

use App\Models\Post;
use App\Models\Project;
use App\Models\Service;

return [
    /*
    |--------------------------------------------------------------------------
    | Fallback navigation
    |--------------------------------------------------------------------------
    |
    | These are the hardcoded links the site shipped with. They are rendered
    | whenever a location has no active menu saved in the admin, so the site
    | keeps working before anyone opens the navigation editor.
    |
    */

    'fallback' => [
        'header' => [
            ['label' => 'Home', 'icon' => 'home', 'route' => 'home'],
            ['label' => 'About', 'icon' => 'about', 'route' => 'about'],
            ['label' => 'Services', 'icon' => 'services', 'route' => 'services.index'],
            ['label' => 'Shop', 'icon' => 'shop', 'route' => 'shop.index'],
            ['label' => 'Projects', 'icon' => 'projects', 'route' => 'projects.index'],
            ['label' => 'Portfolio', 'icon' => 'portfolio', 'route' => 'portfolio.index'],
            ['label' => 'Blog', 'icon' => 'blog', 'route' => 'blog.index'],
            ['label' => 'Contact', 'icon' => 'contact', 'route' => 'contact.create'],
        ],

        'footer' => [
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'About Us', 'route' => 'about'],
            ['label' => 'Services', 'route' => 'services.index'],
            ['label' => 'Projects', 'route' => 'projects.index'],
            ['label' => 'Case Studies', 'route' => 'portfolio.index'],
            ['label' => 'Blog', 'route' => 'blog.index'],
            ['label' => 'Requirement Brief', 'route' => 'requirements.create'],
        ],

        'legal' => [
            ['label' => 'Privacy Policy', 'route' => 'legal.privacy'],
            ['label' => 'Terms & Conditions', 'route' => 'legal.terms'],
            ['label' => 'Cancellation & Refunds', 'route' => 'legal.refunds'],
            ['label' => 'Delivery Policy', 'route' => 'legal.delivery'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Record link registry
    |--------------------------------------------------------------------------
    |
    | A menu item of type "record" stores "alias:slug" in its link value.
    | This map turns the alias back into a model and the route that renders
    | it, so admins can point at any published record without a migration.
    |
    */

    'record_models' => [
        'service' => [
            'model' => Service::class,
            'route' => 'services.show',
        ],
        'project' => [
            'model' => Project::class,
            'route' => 'portfolio.show',
        ],
        'post' => [
            'model' => Post::class,
            'route' => 'blog.show',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation icons
    |--------------------------------------------------------------------------
    |
    | Stroke glyphs for the floating bottom menu. All drawn on a 20×20 grid
    | with the same weight as the bar's search/cart/account icons. The keys
    | double as the allow-list the admin menu editor validates against.
    |
    */

    'icons' => [
        'home' => '<path d="M3 9.2 10 3.5l7 5.7V16a1 1 0 0 1-1 1h-3.5v-4.5h-5V17H4a1 1 0 0 1-1-1V9.2Z"/>',
        'about' => '<path d="M4.5 17V3.5h8V17"/><path d="M12.5 8H16v9"/><path d="M2.5 17h15"/><path d="M7 6h3M7 9h3M7 12h3"/>',
        'services' => '<path d="m10 3 7 3.5-7 3.5-7-3.5L10 3Z"/><path d="m3 10.5 7 3.5 7-3.5"/><path d="m3 14.2 7 3.5 7-3.5"/>',
        'shop' => '<path d="M4.5 6.5h11l-.9 10h-9.2l-.9-10Z"/><path d="M7.5 6.5V5a2.5 2.5 0 0 1 5 0v1.5"/>',
        'projects' => '<rect x="3" y="3" width="6" height="6" rx="1"/><rect x="11" y="3" width="6" height="6" rx="1"/><rect x="3" y="11" width="6" height="6" rx="1"/><rect x="11" y="11" width="6" height="6" rx="1"/>',
        'portfolio' => '<rect x="3" y="4" width="14" height="12" rx="1.5"/><circle cx="7.3" cy="8.3" r="1.3"/><path d="m4 14.5 3.8-3.8 2.7 2.7 2.5-2.5 3 3"/>',
        'blog' => '<path d="M5 3.5h6.5L15 7v9.5H5V3.5Z"/><path d="M11.5 3.5V7H15"/><path d="M7.5 10.5h5M7.5 13h5"/>',
        'contact' => '<rect x="3" y="5" width="14" height="10" rx="1.5"/><path d="m3.8 5.8 6.2 4.7 6.2-4.7"/>',
    ],
];
