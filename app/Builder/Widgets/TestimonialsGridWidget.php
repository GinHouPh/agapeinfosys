<?php

namespace App\Builder\Widgets;

/**
 * A grid of testimonial quote cards pulled live from published testimonials.
 */
class TestimonialsGridWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'testimonials',
            'title' => 'Testimonials',
            'category' => 'Dynamic',
            'description' => 'Quote cards with avatar and rating, pulled live from the testimonials inbox.',
            'icon' => 'M7 7h10v6H9l-4 4V7z M17 10h3v6h-3z',
            'view' => 'builder.widgets.testimonials',
            'controls' => [
                [
                    'key' => 'subtitle',
                    'label' => 'Subtitle',
                    'type' => 'text',
                    'default' => 'Testimonials',
                ],
                [
                    'key' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'default' => 'What They Say',
                ],
                [
                    'key' => 'mark',
                    'label' => 'Edge mark',
                    'type' => 'text',
                    'default' => 'reviews',
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
                    'default' => 0,
                    'help' => 'Leave empty or 0 to show every published testimonial.',
                ],
            ],
        ];
    }
}
