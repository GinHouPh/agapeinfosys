<?php

use App\Models\Form;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;

return [
    /*
    |--------------------------------------------------------------------------
    | Flagship routes
    |--------------------------------------------------------------------------
    |
    | A published builder page whose slug matches a key here takes over that
    | route, serving its blocks instead of the hand-built Blade view. The
    | value is the named route that renders the public URL.
    |
    */

    'flagship' => [
        'home' => 'home',
        'about' => 'about',
        'services' => 'services.index',
        'portfolio' => 'portfolio.index',
        'projects' => 'projects.index',
        'blog' => 'blog.index',
        'requirements' => 'requirements.create',
        'contact' => 'contact.create',
    ],

    /*
    |--------------------------------------------------------------------------
    | Single record templates
    |--------------------------------------------------------------------------
    |
    | A published builder page whose slug matches a key here takes over every
    | detail route of that record type, rendering its blocks with the current
    | record injected. The model/scope pair also drives the sample record the
    | canvas previews while editing.
    |
    */

    'record_templates' => [
        'service-detail' => [
            'label' => 'Service detail',
            'model' => Service::class,
            'scope' => 'published',
            'route' => 'services.index',
            'pattern' => '/services/{slug}',
        ],
        'case-study-detail' => [
            'label' => 'Case study detail',
            'model' => Project::class,
            'scope' => 'published',
            'route' => 'portfolio.index',
            'pattern' => '/portfolio/{slug}',
        ],
        'article-detail' => [
            'label' => 'Article detail',
            'model' => Post::class,
            'scope' => 'published',
            'route' => 'blog.index',
            'pattern' => '/blog/{slug}',
        ],
        'form-detail' => [
            'label' => 'Form detail',
            'model' => Form::class,
            'scope' => 'active',
            'route' => null,
            'pattern' => '/forms/{slug}',
        ],
    ],
];
