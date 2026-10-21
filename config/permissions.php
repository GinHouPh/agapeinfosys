<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Permission catalogue
    |--------------------------------------------------------------------------
    |
    | Every capability the admin area is split into. The keys are the
    | permission slugs used by the "can:" middleware; the values are the
    | labels shown in the role editor.
    |
    | A user with "is_admin" set is a superuser and bypasses this catalogue
    | entirely. Everyone else reaches only what their roles grant.
    |
    */

    'groups' => [
        'Dashboard' => [
            'dashboard.view' => 'View the dashboard',
        ],

        'Content' => [
            'pages.manage' => 'Create, edit and publish pages',
            'navigation.manage' => 'Manage menus and navigation items',
            'services.manage' => 'Manage services',
            'projects.manage' => 'Manage case studies',
            'showcase.manage' => 'Manage showcase projects',
            'posts.manage' => 'Manage articles',
            'testimonials.manage' => 'Manage testimonials',
            'forms.manage' => 'Build forms and questions',
            'media.manage' => 'Manage the media library',
            'templates.manage' => 'Manage templates and components',
            'design.manage' => 'Manage themes and design tokens',
            'seo.manage' => 'Manage SEO metadata',
            'locales.manage' => 'Manage languages and translations',
            'collections.manage' => 'Manage collections and their records',
        ],

        'Inbox' => [
            'messages.manage' => 'Read and reply to contact messages',
            'submissions.manage' => 'Read and triage form submissions',
        ],

        'Commerce' => [
            'products.manage' => 'Manage products',
            'categories.manage' => 'Manage shop categories',
            'plans.manage' => 'Manage plans',
            'orders.view' => 'View orders and shop orders',
            'subscriptions.view' => 'View subscriptions',
            'gateways.manage' => 'Manage payment gateways',
        ],

        'Clients' => [
            'users.manage' => 'Manage client accounts',
            'roles.manage' => 'Manage roles and permissions',
            'activity.view' => 'View the activity log',
        ],

        'System' => [
            'business.manage' => 'Manage business details and branding',
            'system.manage' => 'Manage platform settings, flags and maintenance',
        ],
    ],
];
