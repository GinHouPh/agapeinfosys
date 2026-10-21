<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SeoMeta>
 */
class SeoMetaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'metaable_type' => Page::class,
            'metaable_id' => Page::factory(),
            'meta_title' => $this->faker->sentence(4),
            'meta_description' => $this->faker->sentence(12),
            'meta_keywords' => implode(', ', $this->faker->words(4)),
            'og_title' => $this->faker->sentence(4),
            'og_description' => $this->faker->sentence(12),
            'og_image' => '/uploads/pages/og.jpg',
            'twitter_card' => 'summary_large_image',
            'canonical_url' => null,
            'noindex' => false,
            'sitemap_priority' => '0.7',
            'sitemap_changefreq' => 'monthly',
        ];
    }
}
