<?php

namespace App\Builder\Widgets;

/**
 * The full-width cover image that sits between a detail page's header and its
 * body. Reads the record's cover (or image) field and hides itself when the
 * record has none, exactly as the hand-built views do.
 */
class RecordCoverWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'record-cover',
            'title' => 'Record Cover',
            'category' => 'Record',
            'description' => "The current record's cover image, hidden when it has none.",
            'icon' => 'M3 6h18v12H3z M6 14l4-4 3 3 2-2 3 3',
            'view' => 'builder.widgets.record-cover',
            'controls' => [
                [
                    'key' => 'aspect',
                    'label' => 'Aspect ratio',
                    'type' => 'text',
                    'default' => '1300/560',
                    'help' => 'CSS aspect ratio, e.g. 1300/560 or 475/560.',
                ],
                [
                    'key' => 'decorative',
                    'label' => 'Decorative image',
                    'type' => 'toggle',
                    'default' => true,
                    'help' => 'Marks the image as decorative so screen readers skip it.',
                ],
            ],
        ];
    }
}
