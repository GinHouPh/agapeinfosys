<?php

namespace App\Builder\Widgets;

use App\Enums\ProjectStatus;

/**
 * The projects poster wall: active showcase projects with stage and industry
 * filters read off the request, exactly as /projects filters them.
 */
class ShowcaseGridWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'showcase-grid',
            'title' => 'Showcase Poster Wall',
            'category' => 'Dynamic',
            'description' => 'Poster wall of ongoing and upcoming projects with stage and industry filters.',
            'icon' => 'M4 4h7v7H4z M13 4h7v7h-7z M4 13h7v7H4z M13 13h7v7h-7z',
            'view' => 'builder.widgets.showcase-grid',
            'controls' => [
                [
                    'key' => 'show_stage_filter',
                    'label' => 'Show stage tabs',
                    'type' => 'toggle',
                    'default' => true,
                ],
                [
                    'key' => 'show_industry_filter',
                    'label' => 'Show industry tabs',
                    'type' => 'toggle',
                    'default' => true,
                ],
                [
                    'key' => 'title',
                    'label' => 'Heading',
                    'type' => 'text',
                    'default' => '',
                    'help' => 'Optional section heading above the filters. Leave empty to omit it.',
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
                    'default' => 'No projects match this filter — try another stage or industry.',
                ],
            ],
        ];
    }

    /**
     * Stage tab labels, derived from the status enum.
     *
     * @return array<string, string>
     */
    public static function stages(): array
    {
        $stages = ['' => 'All'];

        foreach (ProjectStatus::cases() as $status) {
            $stages[$status->value] = $status->label();
        }

        return $stages;
    }
}
