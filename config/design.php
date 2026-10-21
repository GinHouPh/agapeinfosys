<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default token values
    |--------------------------------------------------------------------------
    |
    | These mirror the `@theme` block and the `html.light` overrides in
    | resources/css/app.css. They are the fallback: with no active theme the
    | stylesheet stands on its own, and the admin editor shows these values.
    |
    | Token names are the ones in App\Builder\DesignTokens::COLORS. Only the
    | VALUES are editable — the builder stores names, never colours.
    |
    */

    'tokens' => [
        'dark' => [
            'ink' => '#0c0c0c',
            'surface' => '#141414',
            'box' => '#2c2c2c',
            'line' => '#383838',
            'muted' => '#828282',
            'body' => '#d8d8d8',
            'headline' => '#ffffff',
            'accent' => '#d90429',
            'accent-soft' => 'rgba(217, 4, 41, 0.14)',
            'on-accent' => '#ffffff',
        ],

        'light' => [
            'ink' => '#f6f6f3',
            'surface' => '#ecebe7',
            'box' => '#e1e0db',
            'line' => '#cdccc6',
            'muted' => '#64635e',
            'body' => '#2b2a28',
            'headline' => '#0c0c0c',
            'accent' => '#d90429',
            'accent-soft' => 'rgba(217, 4, 41, 0.09)',
            'on-accent' => '#ffffff',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fonts
    |--------------------------------------------------------------------------
    |
    | Only fonts bundled with the build are offered — a font that is not
    | downloaded cannot be used. The display font drives headings and the
    | body font drives everything else.
    |
    */

    'fonts' => [
        'display' => [
            'Akshar' => 'Akshar',
            'Outfit' => 'Outfit',
        ],
        'body' => [
            'Roboto' => 'Roboto',
            'Inter' => 'Inter',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Scales
    |--------------------------------------------------------------------------
    |
    | Named steps an editor can pick from, compiled into the
    | `--radius-*` and `--shadow-*` custom properties.
    |
    */

    'scales' => [
        'radius' => [
            'none' => '0px',
            'sm' => '2px',
            'md' => '6px',
            'lg' => '14px',
            'full' => '9999px',
        ],

        'shadow' => [
            'none' => 'none',
            'sm' => '0 1px 2px rgb(0 0 0 / 0.15)',
            'md' => '0 10px 30px -12px rgb(0 0 0 / 0.45)',
            'lg' => '0 24px 60px -20px rgb(0 0 0 / 0.60)',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Schemes
    |--------------------------------------------------------------------------
    |
    | The colour schemes a theme carries. "dark" is the root; the others are
    | emitted under their own selector.
    |
    */

    'schemes' => [
        'dark' => ':root',
        'light' => 'html.light',
    ],
];
