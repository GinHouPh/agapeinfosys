<?php

namespace App\Builder\Widgets;

/**
 * Full-viewport hero: eyebrow, stacked heading with an accent line, lead,
 * buttons and an optional stat card.
 *
 * Unlike other section widgets the hero always sits on the ink background
 * with its own glow, so it only opts into the shared spacing control.
 */
class HeroWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'hero',
            'title' => 'Hero',
            'category' => 'Layout',
            'description' => 'Full-screen opening section with a heading, lead text, buttons and a stat card.',
            'icon' => 'M4 5h16v10H4z M4 19h16 M9 9l3 3 3-4',
            'view' => 'builder.widgets.hero',
            'section' => false,
            'controls' => [
                [
                    'type' => 'heading',
                    'label' => 'Heading copy',
                ],
                [
                    'key' => 'eyebrow',
                    'label' => 'Eyebrow',
                    'type' => 'text',
                    'default' => 'Software solutions & IT consultancy',
                ],
                [
                    'key' => 'title_top',
                    'label' => 'Heading — white lines',
                    'type' => 'textarea',
                    'default' => "Design.\nEngineer.",
                    'help' => 'One line of the heading per row. These render in white.',
                ],
                [
                    'key' => 'title_accent',
                    'label' => 'Heading — accent line',
                    'type' => 'text',
                    'default' => 'Deliver.',
                    'help' => 'The final heading line, rendered in the accent colour.',
                ],
                [
                    'key' => 'lead',
                    'label' => 'Lead paragraph',
                    'type' => 'textarea',
                    'default' => 'We turn complex business problems into dependable software — from discovery workshops and product design through to cloud operations and long term support.',
                ],
                [
                    'type' => 'heading',
                    'label' => 'Buttons',
                ],
                [
                    'key' => 'buttons',
                    'label' => 'Buttons',
                    'type' => 'repeater',
                    'fields' => [
                        ['key' => 'label', 'label' => 'Label', 'type' => 'text', 'default' => 'Explore Services'],
                        ['key' => 'url', 'label' => 'Link', 'type' => 'url', 'default' => '/services'],
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
                        ['label' => 'Explore Services', 'url' => '/services', 'variant' => 'accent'],
                        ['label' => 'See Our Work', 'url' => '/portfolio', 'variant' => 'ghost'],
                    ],
                ],
                [
                    'type' => 'heading',
                    'label' => 'Stat card',
                ],
                [
                    'key' => 'show_card',
                    'label' => 'Show stat card',
                    'type' => 'toggle',
                    'default' => true,
                ],
                [
                    'key' => 'card_badge',
                    'label' => 'Card badge',
                    'type' => 'text',
                    'default' => 'Since 2013',
                    'condition' => ['show_card' => true],
                ],
                [
                    'key' => 'card_quote',
                    'label' => 'Card quote',
                    'type' => 'textarea',
                    'default' => 'A senior engineering team you can plug into your roadmap.',
                    'condition' => ['show_card' => true],
                ],
                [
                    'key' => 'card_stats',
                    'label' => 'Card stats',
                    'type' => 'repeater',
                    'condition' => ['show_card' => true],
                    'fields' => [
                        ['key' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 250],
                        ['key' => 'suffix', 'label' => 'Suffix', 'type' => 'text', 'default' => '+'],
                        ['key' => 'label', 'label' => 'Label', 'type' => 'text', 'default' => 'Products shipped'],
                    ],
                    'default' => [
                        ['value' => 250, 'suffix' => '+', 'label' => 'Products shipped'],
                        ['value' => 98, 'suffix' => '%', 'label' => 'Client retention'],
                    ],
                ],
                [
                    'type' => 'heading',
                    'label' => 'Appearance',
                ],
                [
                    'key' => 'gradient',
                    'label' => 'Glow position',
                    'type' => 'select',
                    'default' => 'right',
                    'options' => [
                        'right' => 'Right',
                        'left' => 'Left',
                        'center' => 'Centre',
                    ],
                ],
                [
                    'key' => 'show_scroll',
                    'label' => 'Show scroll hint',
                    'type' => 'toggle',
                    'default' => true,
                ],
                [
                    'key' => 'spacing',
                    'label' => 'Vertical spacing',
                    'type' => 'select',
                    'default' => 'default',
                    'options' => [
                        'default' => 'Default',
                        'tight' => 'Tight',
                        'none' => 'None',
                    ],
                ],
            ],
        ];
    }
}
