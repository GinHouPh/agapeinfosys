<?php

namespace App\Builder\Widgets;

/**
 * The body of a single-record detail page, with three layout variants matching
 * the hand-built views:
 *
 *  - single:     centred prose column — articles
 *  - media-cta:  sticky image + "engage us" card beside prose — services
 *  - facts:      prose beside a sticky facts aside — case studies
 *
 * Everything it renders (body, highlights, facts, CTA) comes from the record
 * injected by the detail route, so one block serves every service, case study
 * and article page.
 */
class RecordBodyWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'record-body',
            'title' => 'Record Body',
            'category' => 'Record',
            'description' => "The current record's article body with optional list, aside and call to action.",
            'icon' => 'M4 5h16 M4 10h16 M4 15h16 M4 20h9',
            'view' => 'builder.widgets.record-body',
            'controls' => [
                [
                    'type' => 'heading',
                    'label' => 'Structure',
                ],
                [
                    'key' => 'layout',
                    'label' => 'Layout',
                    'type' => 'select',
                    'default' => 'single',
                    'options' => [
                        'single' => 'Article — centred column',
                        'media-cta' => 'Service — image and card beside body',
                        'facts' => 'Case study — body with facts aside',
                    ],
                ],
                [
                    'type' => 'heading',
                    'label' => 'Article',
                ],
                [
                    'key' => 'show_pullquote',
                    'label' => 'Lead with the excerpt',
                    'type' => 'toggle',
                    'default' => true,
                    'help' => 'Prints the record summary in an accent-bordered block before the body.',
                    'condition' => ['layout' => 'single'],
                ],
                [
                    'key' => 'show_back',
                    'label' => 'Show back button',
                    'type' => 'toggle',
                    'default' => true,
                    'condition' => ['layout' => 'single'],
                ],
                [
                    'key' => 'back_label',
                    'label' => 'Back button label',
                    'type' => 'text',
                    'default' => 'Back To Articles',
                    'condition' => ['layout' => 'single', 'show_back' => true],
                ],
                [
                    'key' => 'back_url',
                    'label' => 'Back button URL',
                    'type' => 'url',
                    'default' => '/blog',
                    'condition' => ['layout' => 'single', 'show_back' => true],
                ],
                [
                    'type' => 'heading',
                    'label' => 'Highlights',
                ],
                [
                    'key' => 'show_list',
                    'label' => 'Show highlights list',
                    'type' => 'toggle',
                    'default' => true,
                    'help' => 'Service highlights and case study outcomes render as a bulleted list.',
                ],
                [
                    'key' => 'list_title',
                    'label' => 'List heading',
                    'type' => 'text',
                    'default' => '',
                    'help' => 'Leave empty to use "What you get" or "Outcomes" as the record dictates.',
                    'condition' => ['show_list' => true],
                ],
                [
                    'type' => 'heading',
                    'label' => 'Call to action card',
                ],
                [
                    'key' => 'cta_heading',
                    'label' => 'Card heading',
                    'type' => 'text',
                    'default' => 'Engage us',
                    'condition' => ['layout' => 'media-cta'],
                ],
                [
                    'key' => 'cta_body',
                    'label' => 'Card body',
                    'type' => 'textarea',
                    'default' => 'Need this capability embedded in your team? We can start with a two week discovery sprint.',
                    'condition' => ['layout' => 'media-cta'],
                ],
                [
                    'key' => 'cta_label',
                    'label' => 'Card button label',
                    'type' => 'text',
                    'default' => 'Request A Quote',
                    'condition' => ['layout' => 'media-cta'],
                ],
                [
                    'key' => 'cta_url',
                    'label' => 'Call to action URL',
                    'type' => 'url',
                    'default' => '/contact',
                ],
                [
                    'type' => 'heading',
                    'label' => 'Facts aside',
                ],
                [
                    'key' => 'facts_cta_label',
                    'label' => 'Aside button label',
                    'type' => 'text',
                    'default' => 'Build Something Like This',
                    'condition' => ['layout' => 'facts'],
                ],
                [
                    'key' => 'facts_visit_label',
                    'label' => 'Aside visit link label',
                    'type' => 'text',
                    'default' => 'Visit project →',
                    'condition' => ['layout' => 'facts'],
                ],
            ],
        ];
    }
}
