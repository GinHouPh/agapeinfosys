<?php

namespace App\Builder\Widgets;

/**
 * The portfolio index: published case studies with optional category tabs.
 *
 * Filtering reads ?category= off the request, so the block behaves exactly
 * like the hand-built /portfolio route it replaces.
 */
class PortfolioGridWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'portfolio-grid',
            'title' => 'Portfolio Grid',
            'category' => 'Dynamic',
            'description' => 'Case study grid with category filter tabs, pulled live from the portfolio.',
            'icon' => 'M3 5h8v6H3z M13 5h8v6h-8z M3 13h8v6H3z M13 13h8v6h-8z',
            'view' => 'builder.widgets.portfolio-grid',
            'controls' => [
                [
                    'key' => 'show_filters',
                    'label' => 'Show category tabs',
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
                    'default' => 'No case studies published yet.',
                ],
            ],
        ];
    }
}
