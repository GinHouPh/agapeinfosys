<?php

namespace App\Builder\Widgets;

/**
 * The "related items" strip that closes a detail page — related services,
 * more case studies, or further reading — chosen from the record type and
 * excluding the record itself.
 */
class RecordRelatedWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'record-related',
            'title' => 'Related Records',
            'category' => 'Record',
            'description' => 'Related services, case studies or articles, excluding the current record.',
            'icon' => 'M4 5h7v14H4z M13 5h7v14h-7z M7 9h0 M16 9h0',
            'view' => 'builder.widgets.record-related',
            'controls' => [
                [
                    'key' => 'limit',
                    'label' => 'Items to show',
                    'type' => 'number',
                    'default' => 3,
                ],
                [
                    'key' => 'title',
                    'label' => 'Heading',
                    'type' => 'text',
                    'default' => '',
                    'help' => 'Leave empty to use the record type’s own heading (e.g. "Related Services").',
                ],
                [
                    'key' => 'subtitle',
                    'label' => 'Subheading',
                    'type' => 'text',
                    'default' => '',
                    'help' => 'Leave empty to use the record type’s own subheading.',
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
                    'key' => 'empty_text',
                    'label' => 'Empty message',
                    'type' => 'text',
                    'default' => 'More will be published shortly.',
                ],
            ],
        ];
    }
}
