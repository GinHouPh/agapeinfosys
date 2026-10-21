<?php

namespace App\Builder\Widgets;

/**
 * A four-up grid of numbered values.
 */
class ValuesGridWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'values',
            // Version 2: every repeater row carries an id of its own, so a row can
            // be addressed on its own rather than by its position.
            'version' => 2,
            'migrations' => [
                1 => ['row_id' => ['values']],
            ],
            'title' => 'Values',
            'category' => 'Content',
            'description' => 'A row of numbered value cards describing what the business stands for.',
            'icon' => 'M12 3l3 6 6 1-4.5 4.3L18 21l-6-3.2L6 21l1.5-6.7L3 10l6-1z',
            'view' => 'builder.widgets.values',
            'controls' => [
                [
                    'key' => 'subtitle',
                    'label' => 'Subtitle',
                    'type' => 'text',
                    'default' => 'Our values',
                ],
                [
                    'key' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'default' => 'What We Stand For',
                ],
                [
                    'key' => 'mark',
                    'label' => 'Edge mark',
                    'type' => 'text',
                    'default' => 'values',
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
                    'key' => 'columns',
                    'label' => 'Columns',
                    'type' => 'select',
                    'default' => '4',
                    'options' => [
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                    ],
                ],
                [
                    'key' => 'values',
                    'label' => 'Values',
                    'type' => 'repeater',
                    'fields' => [
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Ownership'],
                        ['key' => 'body', 'label' => 'Body', 'type' => 'textarea', 'default' => ''],
                    ],
                    'default' => [
                        ['title' => 'Ownership', 'body' => 'We treat your product like our own — accountable for uptime, budgets and business results.'],
                        ['title' => 'Transparency', 'body' => 'Open estimates, visible backlogs and weekly demos. No surprises at the end of the sprint.'],
                        ['title' => 'Craft', 'body' => 'Clean architecture, automated tests and documentation that outlives the engagement.'],
                        ['title' => 'Partnership', 'body' => 'Senior consultants who stay with you from discovery through to long term support.'],
                    ],
                ],
            ],
        ];
    }
}
