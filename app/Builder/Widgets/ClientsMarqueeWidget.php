<?php

namespace App\Builder\Widgets;

/**
 * A scrolling logo band of published clients.
 */
class ClientsMarqueeWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'clients-marquee',
            'title' => 'Clients Marquee',
            'category' => 'Dynamic',
            'description' => 'A scrolling band of client logos, pulled live from the clients list.',
            'icon' => 'M3 12h18 M7 9l-3 3 3 3 M17 9l3 3-3 3',
            'view' => 'builder.widgets.clients-marquee',
            'controls' => [
                [
                    'key' => 'subtitle',
                    'label' => 'Subtitle',
                    'type' => 'text',
                    'default' => 'Who we work with',
                ],
                [
                    'key' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'default' => 'Valuable Clients',
                ],
                [
                    'key' => 'mark',
                    'label' => 'Edge mark',
                    'type' => 'text',
                    'default' => 'clients',
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
            ],
        ];
    }
}
