<?php

namespace App\Builder\Widgets;

/**
 * A single oversized statement line with an accent lead phrase.
 */
class StatementWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'statement',
            'title' => 'Statement',
            'category' => 'Layout',
            'description' => 'One oversized line with an accent lead phrase — a visual pause between sections.',
            'icon' => 'M4 6h16 M4 12h10 M4 18h16',
            'view' => 'builder.widgets.statement',
            'controls' => [
                [
                    'key' => 'accent',
                    'label' => 'Accent lead',
                    'type' => 'text',
                    'default' => 'The technology partner',
                    'help' => 'Rendered in the accent colour at the start of the line.',
                ],
                [
                    'key' => 'accent_color',
                    'label' => 'Lead colour',
                    'type' => 'color',
                    'default' => 'accent',
                    'help' => 'A design token, so the lead repaints with the theme instead of being frozen into the page.',
                ],
                [
                    'key' => 'text',
                    'label' => 'Statement',
                    'type' => 'textarea',
                    'default' => 'enterprises trust to turn complex problems into working software.',
                ],
            ],
        ];
    }
}
