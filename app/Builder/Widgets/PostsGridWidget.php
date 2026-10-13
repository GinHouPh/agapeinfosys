<?php

namespace App\Builder\Widgets;

/**
 * A grid of latest article cards pulled live from published posts.
 */
class PostsGridWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'posts-grid',
            'title' => 'Articles Grid',
            'category' => 'Dynamic',
            'description' => 'Blog article cards pulled live from the articles list.',
            'icon' => 'M5 4h14v16H5z M8 8h8 M8 12h8 M8 16h5',
            'view' => 'builder.widgets.posts-grid',
            'controls' => [
                [
                    'key' => 'subtitle',
                    'label' => 'Subtitle',
                    'type' => 'text',
                    'default' => 'Insights & inspiration',
                ],
                [
                    'key' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'default' => 'Read Our Latest Articles',
                ],
                [
                    'key' => 'mark',
                    'label' => 'Edge mark',
                    'type' => 'text',
                    'default' => 'blogs',
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
                    'key' => 'limit',
                    'label' => 'How many',
                    'type' => 'number',
                    'default' => 3,
                    'help' => 'Leave empty or 0 to show every published article.',
                ],
            ],
        ];
    }
}
