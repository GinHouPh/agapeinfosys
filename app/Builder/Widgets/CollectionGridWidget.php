<?php

namespace App\Builder\Widgets;

/**
 * A grid of the records in one of the author's collections.
 */
class CollectionGridWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'collection-grid',
            'title' => 'Collection Grid',
            'category' => 'Record',
            'description' => 'A grid of the records in one of your collections.',
            'icon' => 'M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z',
            'view' => 'builder.widgets.collection-grid',
            'controls' => [
                [
                    'key' => 'collection',
                    'label' => 'Collection',
                    'type' => 'select',
                    'default' => '',
                    'dynamic' => 'collections',
                    'options' => [],
                    'help' => 'The collection whose records are listed here.',
                ],
                [
                    'key' => 'limit',
                    'label' => 'How many',
                    'type' => 'number',
                    'default' => 6,
                    'help' => 'How many records to show at most.',
                ],
                [
                    'key' => 'empty_text',
                    'label' => 'Empty text',
                    'type' => 'text',
                    'default' => 'Nothing here yet.',
                    'help' => 'Shown when the collection has no published records.',
                ],
                [
                    'key' => 'show_button',
                    'label' => 'Show a button',
                    'type' => 'toggle',
                    'default' => false,
                ],
                [
                    'key' => 'button_label',
                    'label' => 'Button label',
                    'type' => 'text',
                    'default' => 'See all',
                ],
                [
                    'key' => 'button_url',
                    'label' => 'Button URL',
                    'type' => 'url',
                    'default' => '',
                ],
            ],
        ];
    }
}
