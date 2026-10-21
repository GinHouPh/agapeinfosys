@props(['name'])

@php
    /**
     * Stroke glyphs for the floating bottom menu, sourced from
     * config/navigation.php so the admin menu editor and the renderer
     * always agree on which icons exist.
     *
     * @var array<string, string>
     */
    $glyphs = config('navigation.icons', []);
@endphp

<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    {!! $glyphs[$name] ?? '' !!}
</svg>
