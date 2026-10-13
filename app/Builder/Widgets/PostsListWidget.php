<?php

namespace App\Builder\Widgets;

/**
 * The blog index: published articles, paginated, with category tabs.
 *
 * Pagination and category filtering both read the query string, so the block
 * behaves exactly like the hand-built /blog route it replaces.
 */
class PostsListWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'posts-list',
            'title' => 'Article List',
            'category' => 'Dynamic',
            'description' => 'Paginated article cards with category tabs, pulled live from the blog.',
            'icon' => 'M4 5h16 M4 10h16 M4 15h10 M4 20h10',
            'view' => 'builder.widgets.posts-list',
            'controls' => [
                [
                    'key' => 'per_page',
                    'label' => 'Articles per page',
                    'type' => 'number',
                    'default' => 6,
                ],
                [
                    'key' => 'show_filters',
                    'label' => 'Show category tabs',
                    'type' => 'toggle',
                    'default' => true,
                ],
                [
                    'key' => 'show_pagination',
                    'label' => 'Show pagination',
                    'type' => 'toggle',
                    'default' => true,
                ],
                [
                    'key' => 'title',
                    'label' => 'Heading',
                    'type' => 'text',
                    'default' => '',
                    'help' => 'Optional section heading above the tabs. Leave empty to omit it.',
                ],
                [
                    'key' => 'subtitle',
                    'label' => 'Subheading',
                    'type' => 'text',
                    'default' => '',
                ],
                [
                    'key' => 'mark',
                    'label' => 'Corner mark',
                    'type' => 'text',
                    'default' => '',
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
                    'key' => 'empty_text',
                    'label' => 'Empty message',
                    'type' => 'text',
                    'default' => 'No articles published yet.',
                ],
            ],
        ];
    }
}
