<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <title>@yield('title', config('app.name').' — Software Solutions & IT Consultancy')</title>
    <meta name="description" content="@yield('description', 'Agape Infosys builds custom software, web and mobile products, and provides IT consultancy for enterprises.')">
    <meta name="theme-color" content="#0c0c0c">

    {{-- Theme bootstrap — runs before the stylesheet so the very first paint
         already matches the stored preference. Stored values are `dark`,
         `light` or `system` (follow the screen); the markup ships dark, so
         anything else is left untouched. --}}
    <script>
        (function () {
            var mode = null;

            try {
                mode = localStorage.getItem('theme');
            } catch (error) {}

            if (mode !== 'light' && mode !== 'system') {
                return;
            }

            var light = mode === 'light' || window.matchMedia('(prefers-color-scheme: light)').matches;
            var root = document.documentElement;

            root.classList.toggle('light', light);
            root.classList.toggle('dark', !light);

            var meta = document.querySelector('meta[name="theme-color"]');

            if (meta) {
                meta.setAttribute('content', light ? '#f6f6f3' : '#0c0c0c');
            }
        })();
    </script>

    @php
        $seoTitle = $seoMeta['og_title'] ?? trim(strip_tags($__env->getSection('title', config('app.name'))));
        $seoDescription = $seoMeta['og_description'] ?? trim(strip_tags($__env->getSection('description', 'Software solutions and IT consultancy.')));
    @endphp

    <link rel="canonical" href="{{ $seoMeta['canonical_url'] ?? url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    @if ($seoMeta['og_image'] ?? null)
        <meta property="og:image" content="{{ $seoMeta['og_image'] }}">
    @endif
    @if ($seoMeta['keywords'] ?? null)
        <meta name="keywords" content="{{ $seoMeta['keywords'] }}">
    @endif

    <meta name="twitter:card" content="{{ $seoMeta['twitter_card'] ?? 'summary_large_image' }}">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    @if ($seoMeta['og_image'] ?? null)
        <meta name="twitter:image" content="{{ $seoMeta['og_image'] }}">
    @endif

    @if ($seoMeta['noindex'] ?? false)
        <meta name="robots" content="noindex, nofollow">
    @endif

    @php
        $languages = app(\App\Services\LocaleService::class)->available();
        $currentPath = '/'.ltrim(request()->path(), '/');
    @endphp

    @foreach ($languages as $language)
        <link rel="alternate" hreflang="{{ $language->code }}"
              href="{{ app(\App\Services\LocaleService::class)->alternateUrl($language->code, $language->is_default, $currentPath) }}">
    @endforeach

    @if ($languages->isNotEmpty())
        <link rel="alternate" hreflang="x-default"
              href="{{ app(\App\Services\LocaleService::class)->alternateUrl('x-default', true, $currentPath) }}">
    @endif

    @if (! empty($structuredData))
        <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@graph' => $structuredData], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif

    @fonts

    {{-- The active design theme. Emitted before the stylesheet so it beats
         the `@theme` output of Tailwind's cascade and repaints every token
         utility at once. Null when no theme is active — app.css then stands
         on its own. --}}
    {!! app(\App\Services\ThemeCompiler::class)->styleTag() !!}

    {{-- The page's own token overrides, emitted after the theme so they win. --}}
    {!! app(\App\Services\ThemeCompiler::class)->pageTag($pageTokenOverrides ?? []) !!}

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="bg-ink text-body site-body">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:bg-accent focus:px-4 focus:py-2 focus:text-on-accent">
        Skip to content
    </a>

    @include('partials.top-bar')

    @include('partials.header')

    <main id="main">
        @yield('content')
    </main>

    @include('partials.footer')

    @stack('scripts')
</body>
</html>
