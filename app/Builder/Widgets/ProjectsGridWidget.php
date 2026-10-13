<?php

namespace App\Builder\Widgets;

/**
 * A grid of case study cards pulled live from published projects.
 */
class ProjectsGridWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'projects-grid',
            'title' => 'Projects Grid',
            'category' => 'Dynamic',
            'description' => 'Case study cards pulled live from the portfolio.',
            'icon' => 'M3 7h18v13H3z M8 7V4h8v3',
            'view' => 'builder.widgets.projects-grid',
            'controls' => [
                [
                    'key' => 'subtitle',
                    'label' => 'Subtitle',
                    'type' => 'text',
                    'default' => 'Recent case studies',
                ],
                [
                    'key' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'default' => 'Selected Work',
                ],
                [
                    'key' => 'mark',
                    'label' => 'Edge mark',
                    'type' => 'text',
                    'default' => 'projects',
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
                    'key' => 'limit',
                    'label' => 'How many',
                    'type' => 'number',
                    'default' => 4,
                    'help' => 'Leave empty or 0 to show every published project.',
                ],
                [
                    'key' => 'featured_only',
                    'label' => 'Featured only',
                    'type' => 'toggle',
                    'default' => false,
                ],
                [
                    'key' => 'show_button',
                    'label' => 'Show "View All Work" button',
                    'type' => 'toggle',
                    'default' => true,
                ],
                [
                    'key' => 'button_label',
                    'label' => 'Button label',
                    'type' => 'text',
                    'default' => 'View All Work',
                    'condition' => ['show_button' => true],
                ],
                [
                    'key' => 'button_url',
                    'label' => 'Button link',
                    'type' => 'url',
                    'default' => '/portfolio',
                    'condition' => ['show_button' => true],
                ],
            ],
        ];
    }
}
