<?php

namespace App\Builder\Widgets;

/**
 * A stacked list of services, each with a media tile beside its summary.
 */
class ServicesListWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'services-list',
            'title' => 'Services List',
            'category' => 'Dynamic',
            'description' => 'A vertical list of services with an image, summary and read-more link.',
            'icon' => 'M4 6h16 M4 12h16 M4 18h16',
            'view' => 'builder.widgets.services-list',
            'controls' => [
                [
                    'key' => 'limit',
                    'label' => 'How many',
                    'type' => 'number',
                    'default' => 0,
                    'help' => 'Leave empty or 0 to show every published service.',
                ],
                [
                    'key' => 'show_button',
                    'label' => 'Show "Read More" button',
                    'type' => 'toggle',
                    'default' => true,
                ],
            ],
        ];
    }
}
