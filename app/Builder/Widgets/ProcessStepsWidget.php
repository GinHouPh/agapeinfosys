<?php

namespace App\Builder\Widgets;

/**
 * Numbered process steps in a row.
 */
class ProcessStepsWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'process-steps',
            'title' => 'Process Steps',
            'category' => 'Content',
            'description' => 'A numbered sequence of steps describing how you work.',
            'icon' => 'M4 6h4 M4 12h4 M4 18h4 M12 6h8 M12 12h8 M12 18h8',
            'view' => 'builder.widgets.process-steps',
            'controls' => [
                [
                    'key' => 'subtitle',
                    'label' => 'Subtitle',
                    'type' => 'text',
                    'default' => 'How we work',
                ],
                [
                    'key' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'default' => 'Process We Follow',
                ],
                [
                    'key' => 'mark',
                    'label' => 'Edge mark',
                    'type' => 'text',
                    'default' => 'process',
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
                    'key' => 'steps',
                    'label' => 'Steps',
                    'type' => 'repeater',
                    'fields' => [
                        ['key' => 'number', 'label' => 'Number', 'type' => 'text', 'default' => '01'],
                        ['key' => 'title', 'label' => 'Title', 'type' => 'text', 'default' => 'Discover'],
                        ['key' => 'body', 'label' => 'Body', 'type' => 'textarea', 'default' => ''],
                    ],
                    'default' => [
                        ['number' => '01', 'title' => 'Discover, Strategy', 'body' => 'We map your business goals, audit the existing systems and define a delivery roadmap with measurable outcomes.'],
                        ['number' => '02', 'title' => 'Design, Build', 'body' => 'Our engineers and designers ship in short iterations with continuous review, so you see working software every sprint.'],
                        ['number' => '03', 'title' => 'Launch, Scale', 'body' => 'We deploy, monitor and harden the product, then stay on as your long term technology partner.'],
                    ],
                ],
            ],
        ];
    }
}
