<?php

namespace App\Builder\Widgets;

/**
 * A grid of bordered feature cards, each with a title and body.
 */
class FeatureCardsWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'feature-cards',
            // Version 2: every repeater row carries an id of its own, so a row can
            // be addressed on its own rather than by its position.
            'version' => 2,
            'migrations' => [
                1 => ['row_id' => ['cards']],
            ],
            'title' => 'Feature Cards',
            'category' => 'Content',
            'description' => 'A responsive grid of bordered cards, each with a title and short body.',
            'icon' => 'M4 4h7v7H4z M13 4h7v7h-7z M4 13h7v7H4z M13 13h7v7h-7z',
            'view' => 'builder.widgets.feature-cards',
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
                    'key' => 'align',
                    'label' => 'Heading alignment',
                    'type' => 'select',
                    'default' => 'left',
                    'options' => [
                        'left' => 'Left',
                        'center' => 'Centre',
                    ],
                ],
                [
                    'key' => 'columns',
                    'label' => 'Columns',
                    'type' => 'select',
                    'default' => '3',
                    'options' => [
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                    ],
                ],
                [
                    'key' => 'cards',
                    'label' => 'Cards',
                    'type' => 'repeater',
                    'fields' => [
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Feature'],
                        ['key' => 'body', 'label' => 'Body', 'type' => 'textarea', 'default' => ''],
                    ],
                    'default' => [
                        ['title' => 'Senior only', 'body' => 'No junior hand-offs. The people who scope your work build it.'],
                        ['title' => 'Fixed cadence', 'body' => 'Weekly demos, visible backlog and a demo you can click.'],
                        ['title' => 'Documented', 'body' => 'Handover documentation and runbooks you can actually maintain.'],
                    ],
                ],
            ],
        ];
    }
}
