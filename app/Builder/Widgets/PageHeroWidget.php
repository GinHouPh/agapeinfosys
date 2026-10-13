<?php

namespace App\Builder\Widgets;

/**
 * The compact page header that opens every listing and detail page.
 *
 * In "static" mode it renders authored copy (eyebrow, a heading split around
 * an accent phrase, a lead) — the listing pages. In "record" mode the heading
 * and lead are read from the record the page is a template for, so one layout
 * serves service, case study, article and form detail pages.
 */
class PageHeroWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'page-hero',
            'title' => 'Page Header',
            'category' => 'Layout',
            'description' => 'Compact page opening with eyebrow, split heading, lead and optional breadcrumb.',
            'icon' => 'M4 6h16 M4 11h10 M4 16h7',
            'view' => 'builder.widgets.page-hero',
            // The header owns its own glow and vertical rhythm, like the hero.
            'section' => false,
            'controls' => [
                [
                    'type' => 'heading',
                    'label' => 'Heading copy',
                ],
                [
                    'key' => 'source',
                    'label' => 'Heading source',
                    'type' => 'select',
                    'default' => 'static',
                    'options' => [
                        'static' => 'Written here',
                        'record' => 'Current record',
                    ],
                    'help' => 'Use "Current record" on a detail template so each page shows its own title.',
                ],
                [
                    'key' => 'eyebrow',
                    'label' => 'Eyebrow',
                    'type' => 'text',
                    'default' => 'Portfolio',
                    'help' => 'Used as written; on a record it falls back to the record\'s category when left empty.',
                ],
                [
                    'key' => 'title_before',
                    'label' => 'Heading — before accent',
                    'type' => 'text',
                    'default' => '',
                    'help' => 'White text preceding the accent phrase. Leave empty for a leading accent.',
                ],
                [
                    'key' => 'accent',
                    'label' => 'Heading — accent phrase',
                    'type' => 'text',
                    'default' => 'Work',
                ],
                [
                    'key' => 'title_after',
                    'label' => 'Heading — after accent',
                    'type' => 'textarea',
                    'default' => 'that shipped, scaled and stayed shipped.',
                ],
                [
                    'key' => 'lead',
                    'label' => 'Lead paragraph',
                    'type' => 'textarea',
                    'default' => 'A selection of the platforms, products and systems we have designed, built and operated for our clients.',
                    'help' => 'On a record heading this falls back to the record\'s summary or excerpt when left empty.',
                ],
                [
                    'type' => 'heading',
                    'label' => 'Breadcrumb',
                ],
                [
                    'key' => 'show_breadcrumb',
                    'label' => 'Show breadcrumb',
                    'type' => 'toggle',
                    'default' => false,
                ],
                [
                    'key' => 'breadcrumb_parent_label',
                    'label' => 'Breadcrumb parent label',
                    'type' => 'text',
                    'default' => 'Services',
                    'condition' => ['show_breadcrumb' => true],
                ],
                [
                    'key' => 'breadcrumb_parent_url',
                    'label' => 'Breadcrumb parent URL',
                    'type' => 'url',
                    'default' => '/services',
                    'condition' => ['show_breadcrumb' => true],
                ],
                [
                    'type' => 'heading',
                    'label' => 'Record details',
                ],
                [
                    'key' => 'show_meta',
                    'label' => 'Show record meta line',
                    'type' => 'toggle',
                    'default' => false,
                    'help' => 'Category, publish date and reading time beneath the heading.',
                    'condition' => ['source' => 'record'],
                ],
                [
                    'key' => 'show_author',
                    'label' => 'Show record author',
                    'type' => 'toggle',
                    'default' => false,
                    'condition' => ['source' => 'record'],
                ],
                [
                    'type' => 'heading',
                    'label' => 'Appearance',
                ],
                [
                    'key' => 'gradient',
                    'label' => 'Glow position',
                    'type' => 'select',
                    'default' => 'left',
                    'options' => [
                        'left' => 'Left',
                        'center' => 'Centre',
                        'right' => 'Right',
                    ],
                ],
                [
                    'key' => 'spacing',
                    'label' => 'Vertical spacing',
                    'type' => 'select',
                    'default' => 'default',
                    'options' => [
                        'default' => 'Default',
                        'tight' => 'Tight',
                        'none' => 'None',
                    ],
                ],
            ],
        ];
    }
}
