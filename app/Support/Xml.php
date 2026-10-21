<?php

namespace App\Support;

/**
 * Builds the XML the site serves.
 *
 * The documents are built in PHP rather than in a Blade template on
 * purpose. A template that contains a literal `<?xml` is a PHP open tag on
 * a server with short_open_tag on — and the Zend lexer reads the template
 * that way too, so the declaration quietly takes the rest of the file with
 * it. Keeping the markup here means the view never holds a bare `<?`.
 */
class Xml
{
    /**
     * The XML declaration every document starts with.
     */
    public static function declaration(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>';
    }

    /**
     * The sitemap of everything worth indexing.
     *
     * @param  array<int, array{loc: string, lastmod: ?string, priority: string, changefreq: string}>  $entries
     */
    public static function urlset(array $entries): string
    {
        $xml = self::declaration()."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($entries as $entry) {
            $xml .= "    <url>\n";
            $xml .= '        <loc>'.self::escape($entry['loc'])."</loc>\n";

            if (! empty($entry['lastmod'])) {
                $xml .= '        <lastmod>'.self::escape($entry['lastmod'])."</lastmod>\n";
            }

            $xml .= '        <changefreq>'.self::escape($entry['changefreq'])."</changefreq>\n";
            $xml .= '        <priority>'.self::escape($entry['priority'])."</priority>\n";
            $xml .= "    </url>\n";
        }

        return $xml."</urlset>\n";
    }

    /**
     * The news sitemap of the published articles.
     *
     * @param  array<int, array{loc: string, title: ?string, published: ?string, updated: ?string}>  $entries
     */
    public static function news(array $entries): string
    {
        $xml = self::declaration()."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'."\n";
        $xml .= '        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">'."\n";

        foreach ($entries as $entry) {
            $xml .= "    <url>\n";
            $xml .= '        <loc>'.self::escape($entry['loc'])."</loc>\n";

            if (! empty($entry['published'])) {
                $xml .= "        <news:news>\n";
                $xml .= "            <news:publication>\n";
                $xml .= '                <news:name>'.self::escape((string) config('app.name'))."</news:name>\n";
                $xml .= "                <news:language>en</news:language>\n";
                $xml .= "            </news:publication>\n";
                $xml .= '            <news:publication_date>'.self::escape($entry['published'])."</news:publication_date>\n";

                if (! empty($entry['title'])) {
                    $xml .= '            <news:title>'.self::escape($entry['title'])."</news:title>\n";
                }

                $xml .= "        </news:news>\n";
            }

            $xml .= "    </url>\n";
        }

        return $xml."</urlset>\n";
    }

    /**
     * Escape a value for an XML text node.
     */
    public static function escape(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
