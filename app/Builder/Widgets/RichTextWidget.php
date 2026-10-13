<?php

namespace App\Builder\Widgets;

/**
 * A heading plus free paragraphs — the simplest content block.
 */
class RichTextWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'rich-text',
            'title' => 'Text',
            'category' => 'Content',
            'description' => 'A heading and body paragraphs with no imagery.',
            'icon' => 'M4 6h16 M4 10h16 M4 14h10 M4 18h7',
            'view' => 'builder.widgets.rich-text',
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
                    'key' => 'paragraphs',
                    'label' => 'Paragraphs',
                    'type' => 'repeater',
                    'fields' => [
                        ['key' => 'text', 'label' => 'Paragraph', 'type' => 'textarea', 'default' => ''],
                    ],
                    'default' => [
                        ['text' => ''],
                    ],
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
            ],
        ];
    }
}
