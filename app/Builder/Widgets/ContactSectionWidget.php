<?php

namespace App\Builder\Widgets;

/**
 * Contact details pulled from business settings beside the enquiry form.
 *
 * Two arrangements are supported: the home page's details-left / form-right
 * split, and the contact page's mirrored split with office, mailing, working
 * hours and business disclosure blocks.
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
                    'type' => 'heading',
                    'label' => 'Heading',
                ],
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
                    'type' => 'heading',
                    'label' => 'Enquiry form',
                ],
                [
                    'key' => 'show_form',
                    'label' => 'Show enquiry form',
                    'type' => 'toggle',
                    'default' => true,
                ],
                [
                    'key' => 'swap_sides',
                    'label' => 'Form on the left',
                    'type' => 'toggle',
                    'default' => false,
                    'help' => 'Mirrors the split so the form sits beside the details rather than under them.',
                ],
                [
                    'key' => 'form_heading',
                    'label' => 'Form card heading',
                    'type' => 'text',
                    'default' => '',
                    'help' => 'Optional heading printed above the form inside its card.',
                    'condition' => ['show_form' => true],
                ],
                [
                    'key' => 'form_compact',
                    'label' => 'Compact form',
                    'type' => 'toggle',
                    'default' => true,
                    'help' => 'Hides the company and subject fields.',
                    'condition' => ['show_form' => true],
                ],
                [
                    'type' => 'heading',
                    'label' => 'Detail blocks',
                ],
                [
                    'key' => 'show_office',
                    'label' => 'Show office address',
                    'type' => 'toggle',
                    'default' => false,
                ],
                [
                    'key' => 'show_mailing',
                    'label' => 'Show mailing address',
                    'type' => 'toggle',
                    'default' => false,
                ],
                [
                    'key' => 'show_studio',
                    'label' => 'Show studio address',
                    'type' => 'toggle',
                    'default' => true,
                ],
                [
                    'type' => 'heading',
                    'label' => 'Working hours',
                ],
                [
                    'key' => 'show_hours',
                    'label' => 'Show working hours',
                    'type' => 'toggle',
                    'default' => false,
                ],
                [
                    'key' => 'hours_from_business',
                    'label' => 'Read hours from Business Details',
                    'type' => 'toggle',
                    'default' => true,
                    'condition' => ['show_hours' => true],
                ],
                [
                    'key' => 'hours_label',
                    'label' => 'Working hours heading',
                    'type' => 'text',
                    'default' => 'Working Hours',
                    'condition' => ['show_hours' => true],
                ],
                [
                    'key' => 'hours_body',
                    'label' => 'Working hours',
                    'type' => 'textarea',
                    'default' => "Monday – Friday, 9:30 – 18:30 IST.\nCritical support is staffed around the clock.",
                    'condition' => ['show_hours' => true],
                ],
                [
                    'type' => 'heading',
                    'label' => 'Business details',
                ],
                [
                    'key' => 'show_business',
                    'label' => 'Show business details',
                    'type' => 'toggle',
                    'default' => false,
                    'help' => 'Legal entity, registration, GSTIN and the rest from Business Details.',
                ],
                [
                    'key' => 'business_heading',
                    'label' => 'Business details heading',
                    'type' => 'text',
                    'default' => 'Business Details',
                    'condition' => ['show_business' => true],
                ],
            ],
        ];
    }
}
