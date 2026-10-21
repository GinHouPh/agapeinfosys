<?php

namespace App\Builder\Widgets;

/**
 * Two-column split: a media stack beside a section heading, body and button.
 */
class MediaTextWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'media-text',
            // Version 2: every repeater row carries an id of its own, so a row can
            // be addressed on its own rather than by its position.
            'version' => 2,
            'migrations' => [
                1 => ['row_id' => ['paragraphs', 'images']],
            ],
            'title' => 'Media & Text',
            'category' => 'Content',
            'description' => 'A two-column block pairing images with a heading, paragraphs and a button.',
            'icon' => 'M4 5h7v14H4z M13 5h7v14h-7z',
            'view' => 'builder.widgets.media-text',
            'controls' => [
                [
                    'key' => 'media_side',
                    'label' => 'Media side',
                    'type' => 'select',
                    'default' => 'left',
                    'options' => [
                        'left' => 'Left',
                        'right' => 'Right',
                    ],
                ],
                [
                    'key' => 'subtitle',
                    'label' => 'Subtitle',
                    'type' => 'text',
                    'default' => 'Who we are',
                ],
                [
                    'key' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'default' => 'Agape Infosys runs on engineering excellence',
                ],
                [
                    'key' => 'mark',
                    'label' => 'Edge mark',
                    'type' => 'text',
                    'default' => 'about',
                    'help' => 'Optional vertical word pinned to the section edge.',
                ],
                [
                    'key' => 'paragraphs',
                    'label' => 'Paragraphs',
                    'type' => 'repeater',
                    'fields' => [
                        ['key' => 'text', 'label' => 'Paragraph', 'type' => 'textarea', 'default' => ''],
                    ],
                    'default' => [
                        ['text' => 'Agape Infosys is a software solutions and IT consultancy practice. We plan, build and operate the systems that businesses depend on — web platforms, mobile products, integrations and cloud infrastructure.'],
                        ['text' => 'Our consultants stay close to your team, so strategy never drifts away from delivery. Every engagement is run by senior engineers who write the code, own the outcome and hand over documentation you can actually maintain.'],
                    ],
                ],
                [
                    'key' => 'button_label',
                    'label' => 'Button label',
                    'type' => 'text',
                    'default' => 'More About Us',
                ],
                [
                    'key' => 'button_url',
                    'label' => 'Button link',
                    'type' => 'url',
                    'default' => '/about',
                ],
                [
                    'key' => 'images',
                    'label' => 'Images',
                    'type' => 'repeater',
                    'fields' => [
                        ['key' => 'image', 'label' => 'Image', 'type' => 'image', 'default' => ''],
                        ['key' => 'label', 'label' => 'Label', 'type' => 'text', 'default' => 'Studio'],
                    ],
                    'default' => [
                        ['image' => '', 'label' => 'Studio'],
                    ],
                ],
            ],
        ];
    }
}
