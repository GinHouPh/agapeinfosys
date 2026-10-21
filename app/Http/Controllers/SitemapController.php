<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\SeoService;
use App\Support\Xml;
use Illuminate\Http\Response;

/**
 * The sitemap and the robots policy.
 */
class SitemapController extends Controller
{
    /**
     * The XML sitemap of everything worth indexing.
     */
    public function index(SeoService $seo): Response
    {
        $xml = Xml::urlset($seo->sitemapEntries());

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    /**
     * The news sitemap of the published articles.
     */
    public function news(SeoService $seo): Response
    {
        $entries = [];

        foreach (Post::query()->published()->orderByDesc('published_at')->get() as $post) {
            $meta = $seo->for($post);

            if ($meta['noindex']) {
                continue;
            }

            $entries[] = [
                'loc' => route('blog.show', $post),
                'title' => $meta['title'],
                'published' => $post->published_at?->toAtomString(),
                'updated' => $post->updated_at?->toAtomString(),
            ];
        }

        return response(Xml::news($entries), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    /**
     * The robots policy, driven by the platform's own settings.
     */
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /dashboard',
            'Disallow: /checkout',
            'Disallow: /shop/checkout',
        ];

        $sitemap = route('sitemap.xml');

        $lines[] = '';
        $lines[] = 'Sitemap: '.$sitemap;

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
