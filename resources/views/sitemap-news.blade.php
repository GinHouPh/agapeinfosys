@php
    /** @var array<int, array{loc: string, title: ?string, published: ?string, updated: ?string}> $entries */
@endphp
{{--
    The XML declaration is echoed rather than written out: a literal
    `<?xml` is a PHP open tag on a server with short_open_tag on, which
    would turn the declaration into a parse error.
--}}
{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
@foreach ($entries as $entry)
    <url>
        <loc>{{ $entry['loc'] }}</loc>
        @if ($entry['published'])
            <news:news>
                <news:publication>
                    <news:name>{{ config('app.name') }}</news:name>
                    <news:language>en</news:language>
                </news:publication>
                <news:publication_date>{{ $entry['published'] }}</news:publication_date>
                @if ($entry['title'])
                    <news:title>{{ $entry['title'] }}</news:title>
                @endif
            </news:news>
        @endif
    </url>
@endforeach
</urlset>
