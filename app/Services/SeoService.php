<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;

/**
 * Resolves the metadata a page is published with.
 *
 * A value set on the record's own SEO panel wins. Failing that the record
 * falls back to what it already carries — a post's excerpt, a service's
 * summary. Failing that, nothing is emitted rather than something wrong.
 */
class SeoService
{
    /**
     * The resolved metadata for a record.
     *
     * @return array{title: ?string, description: ?string, keywords: ?string, og_title: ?string, og_description: ?string, og_image: ?string, twitter_card: string, canonical_url: ?string, noindex: bool, sitemap_priority: string, sitemap_changefreq: string}
     */
    public function for(Model $record): array
    {
        $meta = $record->relationLoaded('seoMeta')
            ? $record->getRelation('seoMeta')
            : $record->seoMeta()->first();

        $title = $meta?->meta_title ?: $this->fallbackTitle($record);
        $description = $meta?->meta_description ?: $this->fallbackDescription($record);

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $meta?->meta_keywords,
            'og_title' => $meta?->og_title ?: $title,
            'og_description' => $meta?->og_description ?: $description,
            'og_image' => $meta?->og_image,
            'twitter_card' => $meta?->twitter_card ?: 'summary_large_image',
            'canonical_url' => $meta?->canonical_url,
            'noindex' => (bool) ($meta?->noindex ?? false),
            'sitemap_priority' => $meta?->sitemap_priority ?: '0.7',
            'sitemap_changefreq' => $meta?->sitemap_changefreq ?: 'monthly',
        ];
    }

    /**
     * The resolved metadata for whichever record the current route shows.
     *
     * @return array{title: ?string, description: ?string, keywords: ?string, og_title: ?string, og_description: ?string, og_image: ?string, twitter_card: string, canonical_url: ?string, noindex: bool, sitemap_priority: string, sitemap_changefreq: string}
     */
    public function forCurrentRequest(): array
    {
        $record = $this->currentRecord();

        if ($record === null) {
            return [
                'title' => null,
                'description' => null,
                'keywords' => null,
                'og_title' => null,
                'og_description' => null,
                'og_image' => null,
                'twitter_card' => 'summary_large_image',
                'canonical_url' => null,
                'noindex' => false,
                'sitemap_priority' => '0.7',
                'sitemap_changefreq' => 'monthly',
            ];
        }

        return $this->for($record);
    }

    /**
     * The JSON-LD graph for whichever record the current route shows.
     *
     * @return array<int, array<string, mixed>>
     */
    public function structuredDataForCurrentRequest(): array
    {
        return $this->structuredData($this->currentRecord());
    }

    /**
     * The record bound to the current route, if there is one.
     */
    private function currentRecord(): ?Model
    {
        $route = request()->route();

        if ($route === null) {
            return null;
        }

        foreach (['service', 'project', 'post', 'page'] as $key) {
            $parameter = $route->parameter($key);

            if ($parameter instanceof Model) {
                return $parameter;
            }
        }

        return null;
    }

    /**
     * The records that belong in the sitemap.
     *
     * @return array<int, array{loc: string, lastmod: ?string, priority: string, changefreq: string}>
     */
    public function sitemapEntries(): array
    {
        $entries = [];

        foreach (Page::query()->published()->orderBy('id')->get() as $page) {
            $url = $page->targetUrl();

            if ($url === null) {
                continue;
            }

            $meta = $this->for($page);

            if ($meta['noindex']) {
                continue;
            }

            $entries[] = [
                'loc' => $url,
                'lastmod' => $page->updated_at?->toAtomString(),
                'priority' => $meta['sitemap_priority'],
                'changefreq' => $meta['sitemap_changefreq'],
            ];
        }

        foreach ([Service::class, Project::class, Post::class] as $model) {
            foreach ($model::query()->published()->orderBy('id')->get() as $record) {
                $meta = $this->for($record);

                if ($meta['noindex']) {
                    continue;
                }

                $entries[] = [
                    'loc' => $this->urlFor($record),
                    'lastmod' => $record->updated_at?->toAtomString(),
                    'priority' => $meta['sitemap_priority'],
                    'changefreq' => $meta['sitemap_changefreq'],
                ];
            }
        }

        return $entries;
    }

    /**
     * The JSON-LD graph for a page.
     *
     * @param  array<string, mixed>  $context
     * @return array<int, array<string, mixed>>
     */
    public function structuredData(?Model $record, array $context = []): array
    {
        $business = Setting::structured('business', []);

        $graph = [
            [
                '@type' => 'Organization',
                '@id' => url('/').'#organization',
                'name' => $business['brand_name'] ?? config('app.name'),
                'url' => config('app.url'),
            ],
            [
                '@type' => 'WebSite',
                '@id' => url('/').'#website',
                'url' => config('app.url'),
                'name' => $business['brand_name'] ?? config('app.name'),
                'publisher' => ['@id' => url('/').'#organization'],
            ],
        ];

        $entry = $record === null ? null : $this->structuredEntry($record, $context);

        if ($entry !== null) {
            $graph[] = $entry;
        }

        return $graph;
    }

    /**
     * The JSON-LD entry for one kind of record.
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>|null
     */
    private function structuredEntry(Model $record, array $context): ?array
    {
        $meta = $this->for($record);

        return match (true) {
            $record instanceof Post => [
                '@type' => 'Article',
                'headline' => $meta['title'],
                'description' => $meta['description'],
                'url' => $this->urlFor($record),
                'datePublished' => $record->published_at?->toAtomString(),
                'dateModified' => $record->updated_at?->toAtomString(),
            ],
            $record instanceof Service => [
                '@type' => 'Service',
                'name' => $meta['title'],
                'description' => $meta['description'],
                'url' => $this->urlFor($record),
                'provider' => ['@id' => url('/').'#organization'],
            ],
            $record instanceof Project => [
                '@type' => 'CreativeWork',
                'name' => $meta['title'],
                'description' => $meta['description'],
                'url' => $this->urlFor($record),
            ],
            $record instanceof Page => [
                '@type' => 'WebPage',
                'name' => $meta['title'],
                'description' => $meta['description'],
                'url' => $this->targetPath($record),
                'isPartOf' => ['@id' => url('/').'#website'],
            ],
            default => null,
        };
    }

    /**
     * The title a record falls back to.
     */
    private function fallbackTitle(Model $record): ?string
    {
        foreach (['title', 'name'] as $attribute) {
            $value = $record->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * The description a record falls back to.
     */
    private function fallbackDescription(Model $record): ?string
    {
        foreach (['meta_description', 'excerpt', 'summary', 'description'] as $attribute) {
            $value = $record->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * The public URL of a record.
     */
    private function urlFor(Model $record): string
    {
        return match (true) {
            $record instanceof Service => route('services.show', $record),
            $record instanceof Project => route('portfolio.show', $record),
            $record instanceof Post => route('blog.show', $record),
            $record instanceof Page => $record->targetUrl() ?? url('/pages/'.$record->path),
            default => url('/'),
        };
    }

    /**
     * The public path of a page.
     */
    private function targetPath(Page $page): string
    {
        $url = $page->targetUrl();

        if ($url === null) {
            return '/pages/'.$page->path;
        }

        return (string) parse_url($url, PHP_URL_PATH);
    }
}
