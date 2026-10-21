<?php

namespace App\Models;

use Database\Factories\SeoMetaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * The search metadata for one record — a page, an article, a service.
 */
class SeoMeta extends Model
{
    /** @use HasFactory<SeoMetaFactory> */
    use HasFactory;

    protected $fillable = [
        'metaable_type',
        'metaable_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
        'twitter_card',
        'canonical_url',
        'noindex',
        'sitemap_priority',
        'sitemap_changefreq',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'noindex' => 'boolean',
        ];
    }

    /**
     * The record this metadata belongs to.
     */
    public function metaable(): MorphTo
    {
        return $this->morphTo();
    }
}
