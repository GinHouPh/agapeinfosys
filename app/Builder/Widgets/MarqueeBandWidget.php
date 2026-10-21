<?php

namespace App\Builder\Widgets;

/**
 * Infinite horizontal marquee of capability words between sections.
 */
class MarqueeBandWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'marquee-band',
            // Version 2: every repeater row carries an id of its own, so a row can
            // be addressed on its own rather than by its position.
            'version' => 2,
            'migrations' => [
                1 => ['row_id' => ['words']],
            ],
            'title' => 'Marquee Band',
            'category' => 'Layout',
            'description' => 'A bordered band of scrolling words — great for listing capabilities or keywords.',
            'icon' => 'M3 12h18 M8 8l-4 4 4 4 M16 8l4 4-4 4',
            'view' => 'builder.widgets.marquee-band',
            'section' => false,
            'controls' => [
                [
                    'key' => 'words',
                    'label' => 'Words',
                    'type' => 'repeater',
                    'fields' => [
                        ['key' => 'text', 'label' => 'Word', 'type' => 'text', 'default' => 'Custom Software'],
                    ],
                    'default' => [
                        ['text' => 'Custom Software'],
                        ['text' => 'Cloud & DevOps'],
                        ['text' => 'Web Platforms'],
                        ['text' => 'Mobile Apps'],
                        ['text' => 'Data & AI'],
                        ['text' => 'IT Consulting'],
                    ],
                ],
                [
                    'key' => 'size',
                    'label' => 'Word size',
                    'type' => 'select',
                    'default' => 'lg',
                    'options' => [
                        'md' => 'Medium',
                        'lg' => 'Large',
                    ],
                ],
                [
                    'key' => 'reverse',
                    'label' => 'Scroll in reverse',
                    'type' => 'toggle',
                    'default' => false,
                ],
            ],
        ];
    }
}
