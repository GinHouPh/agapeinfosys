<?php

namespace App\Builder\Widgets;

/**
 * A bordered closing call to action: copy on the left, one button on the right.
 */
class CtaCardWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'cta-card',
            'title' => 'CTA Card',
            'category' => 'Layout',
            'description' => 'A bordered call-to-action row with copy on one side and a button on the other.',
            'icon' => 'M3 7h18v10H3z M14 10l3 2-3 2',
            'view' => 'builder.widgets.cta-card',
            'controls' => [
                [
                    'key' => 'heading',
                    'label' => 'Heading',
                    'type' => 'text',
                    'default' => 'Have a project in mind?',
                ],
                [
                    'key' => 'lead',
                    'label' => 'Lead paragraph',
                    'type' => 'textarea',
                    'default' => 'Share your requirement specification and we will come back with scope, timelines and a quotation.',
                ],
                [
                    'key' => 'button_label',
                    'label' => 'Button label',
                    'type' => 'text',
                    'default' => 'Start a Requirement Brief',
                ],
                [
                    'key' => 'button_url',
                    'label' => 'Button URL',
                    'type' => 'url',
                    'default' => '/requirements',
                ],
                [
                    'key' => 'variant',
                    'label' => 'Button style',
                    'type' => 'select',
                    'default' => 'accent',
                    'options' => [
                        'accent' => 'Accent',
                        'ghost' => 'Ghost',
                        'solid' => 'Solid',
                    ],
                ],
            ],
        ];
    }
}
