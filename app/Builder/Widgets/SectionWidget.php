<?php

namespace App\Builder\Widgets;

/**
 * A band that holds other blocks in one or more columns.
 *
 * The only container in the builder: everything else stays a leaf, so a page
 * is still a flat list at the top level and nests only where a section is
 * deliberately placed. Children render inside the section's column grid — the
 * editor draws empty drop zones where they will land.
 */
class SectionWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'section',
            'title' => 'Section',
            'category' => 'Layout',
            'description' => 'A band that holds other sections in one or more columns.',
            'icon' => 'M3 4h18v16H3z M3 12h18 M12 4v16',
            'view' => 'builder.widgets.section',
            'container' => true,
            'controls' => [
                [
                    'key' => 'columns',
                    'label' => 'Columns',
                    'type' => 'select',
                    'default' => '1',
                    'options' => [
                        '1' => 'One',
                        '2' => 'Two',
                        '3' => 'Three',
                        '4' => 'Four',
                    ],
                    'help' => 'Each column is a drop zone on the canvas.',
                ],
                [
                    'key' => 'stack',
                    'label' => 'Stack on mobile',
                    'type' => 'toggle',
                    'default' => true,
                    'help' => 'Collapse to a single column below the md breakpoint.',
                ],
                [
                    'key' => 'gap',
                    'label' => 'Column gap',
                    'type' => 'select',
                    'default' => 'lg',
                    'options' => [
                        'none' => 'None',
                        'sm' => 'Small',
                        'md' => 'Medium',
                        'lg' => 'Large',
                        'xl' => 'Extra large',
                    ],
                ],
                [
                    'key' => 'align',
                    'label' => 'Vertical alignment',
                    'type' => 'select',
                    'default' => 'stretch',
                    'options' => [
                        'stretch' => 'Stretch to the tallest column',
                        'start' => 'Top',
                        'center' => 'Centre',
                        'end' => 'Bottom',
                    ],
                ],
            ],
        ];
    }
}
