<?php

namespace App\Builder\Widgets;

/**
 * A centred closing call to action with buttons.
 */
class CtaBannerWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'cta-banner',
            'title' => 'CTA Banner',
            'category' => 'Layout',
            'description' => 'A centred closing call to action with one or two buttons.',
            'icon' => 'M12 3v18 M5 10l7-7 7 7',
            'view' => 'builder.widgets.cta-banner',
            'controls' => [
                [
                    'key' => 'accent',
                    'label' => 'Accent lead',
                    'type' => 'text',
                    'default' => 'Ready',
                    'help' => 'Rendered in the accent colour at the start of the heading.',
                ],
                [
                    'key' => 'title',
                    'label' => 'Heading',
                    'type' => 'text',
                    'default' => 'to scope your next build?',
                ],
                [
                    'key' => 'lead',
                    'label' => 'Lead paragraph',
                    'type' => 'textarea',
                    'default' => '',
                ],
                [
                    'key' => 'buttons',
                    'label' => 'Buttons',
                    'type' => 'repeater',
                    'fields' => [
                        ['key' => 'label', 'label' => 'Label', 'type' => 'text', 'default' => 'Start A Project'],
                        ['key' => 'url', 'label' => 'Link', 'type' => 'url', 'default' => '/contact'],
                        [
                            'key' => 'variant',
                            'label' => 'Style',
                            'type' => 'select',
                            'default' => 'accent',
                            'options' => [
                                'accent' => 'Accent',
                                'ghost' => 'Ghost',
                                'solid' => 'Solid',
                            ],
                        ],
                    ],
                    'default' => [
                        ['label' => 'Start A Project', 'url' => '/contact', 'variant' => 'accent'],
                        ['label' => 'Browse Services', 'url' => '/services', 'variant' => 'ghost'],
                    ],
                ],
            ],
        ];
    }
}
