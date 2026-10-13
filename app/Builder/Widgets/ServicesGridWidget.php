<?php

namespace App\Builder\Widgets;

/**
 * A grid of service cards pulled live from the Service catalogue.
 */
class ServicesGridWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'services-grid',
            'title' => 'Services Grid',
            'category' => 'Dynamic',
            'description' => 'Cards for published services, pulled live from the Services catalogue.',
            'icon' => 'M4 4h7v7H4z M13 4h7v7h-7z M4 13h7v7H4z M13 13h7v7h-7z',
            'view' => 'builder.widgets.services-grid',
            'controls' => [
                [
                    'key' => 'subtitle',
                    'label' => 'Subtitle',
                    'type' => 'text',
                    'default' => 'What we offer',
                ],
                [
                    'key' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'default' => 'Services We Provide',
                ],
                [
                    'key' => 'mark',
                    'label' => 'Edge mark',
                    'type' => 'text',
                    'default' => 'services',
                ],
                [
                    'key' => 'align',
                    'label' => 'Heading alignment',
                    'type' => 'select',
                    'default' => 'center',
                    'options' => [
                        'left' => 'Left',
                        'center' => 'Centre',
                    ],
                ],
                [
                    'key' => 'limit',
                    'label' => 'How many',
                    'type' => 'number',
                    'default' => 6,
                    'help' => 'Leave empty or 0 to show every published service.',
                ],
                [
                    'key' => 'featured_only',
                    'label' => 'Featured only',
                    'type' => 'toggle',
                    'default' => false,
                ],
                [
                    'key' => 'show_button',
                    'label' => 'Show "All Services" button',
                    'type' => 'toggle',
                    'default' => true,
                ],
                [
                    'key' => 'button_label',
                    'label' => 'Button label',
                    'type' => 'text',
                    'default' => 'All Services',
                    'condition' => ['show_button' => true],
                ],
                [
                    'key' => 'button_url',
                    'label' => 'Button link',
                    'type' => 'url',
                    'default' => '/services',
                    'condition' => ['show_button' => true],
                ],
            ],
        ];
    }
}
