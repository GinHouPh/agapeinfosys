<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Seed the homepage testimonial carousel.
     */
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'Dellon Thomas',
                'role' => 'Head of Operations',
                'company' => 'Meridian Logistics',
                'quote' => 'Agape Infosys took a dispatch process nobody could explain on paper and turned it into a platform our team trains new hires on in a day. The migration was staged so carefully that our drivers never noticed the switch.',
                'rating' => 5,
                'sort_order' => 1,
            ],
            [
                'name' => 'Anna Shvets',
                'role' => 'Chief Technology Officer',
                'company' => 'Northgate Health Group',
                'quote' => 'What stood out was the honesty. They talked us out of the big rewrite we asked for and proposed a staged migration instead, then delivered exactly what they scoped. That is rarer than it should be.',
                'rating' => 5,
                'sort_order' => 2,
            ],
            [
                'name' => 'Zora Akim',
                'role' => 'Product Director',
                'company' => 'Brightside Retail',
                'quote' => 'They behave like an in-house team rather than a vendor. Weekly demos, a visible backlog, and engineers who will tell you when a request is going to cost more than it is worth.',
                'rating' => 5,
                'sort_order' => 3,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::query()->updateOrCreate(
                ['name' => $testimonial['name']],
                $testimonial + [
                    'avatar' => null,
                    'linkedin_url' => null,
                    'is_published' => true,
                ],
            );
        }
    }
}
