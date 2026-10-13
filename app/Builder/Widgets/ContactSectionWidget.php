<?php

namespace App\Builder\Widgets;

/**
 * Contact details pulled from business settings beside the enquiry form.
 */
class ContactSectionWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'contact-section',
            'title' => 'Contact Section',
            'category' => 'Content',
            'description' => 'Contact details (from Business Details) beside the enquiry form.',
            'icon' => 'M4 5h16v14H4z M4 7l8 6 8-6',
            'view' => 'builder.widgets.contact-section',
            'controls' => [
                [
                    'key' => 'subtitle',
                    'label' => 'Subtitle',
                    'type' => 'text',
                    'default' => "Let's talk business",
                ],
                [
                    'key' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'default' => "Let's Build Something Great Together",
                ],
                [
                    'key' => 'mark',
                    'label' => 'Edge mark',
                    'type' => 'text',
                    'default' => 'contact',
                ],
                [
                    'key' => 'lead',
                    'label' => 'Lead paragraph',
                    'type' => 'textarea',
                    'default' => 'Tell us what you are trying to achieve. We will come back within one business day with a point of view, a rough shape of the work and the next sensible step.',
                ],
                [
                    'key' => 'show_form',
                    'label' => 'Show enquiry form',
                    'type' => 'toggle',
                    'default' => true,
                ],
            ],
        ];
    }
}
