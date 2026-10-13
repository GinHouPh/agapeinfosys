<?php

namespace App\Builder\Widgets;

/**
 * Animated number counters with labels.
 */
class StatsGridWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'stats',
            'title' => 'Stats',
            'category' => 'Content',
            'description' => 'A grid of animated counters with labels — proof points for the business.',
            'icon' => 'M4 20V10 M10 20V4 M16 20v-7 M22 20H2',
            'view' => 'builder.widgets.stats',
            'controls' => [
                [
                    'key' => 'subtitle',
                    'label' => 'Subtitle',
                    'type' => 'text',
                    'default' => '',
                ],
                [
                    'key' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'default' => '',
                ],
                [
                    'key' => 'mark',
                    'label' => 'Edge mark',
                    'type' => 'text',
                    'default' => '',
                ],
                [
                    'key' => 'columns',
                    'label' => 'Columns',
                    'type' => 'select',
                    'default' => '2',
                    'options' => [
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                    ],
                ],
                [
                    'key' => 'stats',
                    'label' => 'Stats',
                    'type' => 'repeater',
                    'fields' => [
                        ['key' => 'value', 'label' => 'Value', 'type' => 'number', 'default' => 100],
                        ['key' => 'label', 'label' => 'Label', 'type' => 'text', 'default' => 'Happy clients'],
                    ],
                    'default' => [
                        ['value' => 12, 'label' => 'years of Experience'],
                        ['value' => 250, 'label' => 'projects Delivered'],
                        ['value' => 90, 'label' => 'happy Clients'],
                        ['value' => 15, 'label' => 'Industry Awards'],
                    ],
                ],
            ],
        ];
    }
}
