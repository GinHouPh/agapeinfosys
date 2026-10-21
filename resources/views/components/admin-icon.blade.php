@props(['name'])

@php
    /**
     * Stroke glyphs for the admin sidebar. All drawn on a 20×20
     * grid with the same weight as the public nav icons, so the
     * collapsed rail reads at the same size as the labels did.
     *
     * @var array<string, string>
     */
    $glyphs = [
        'dashboard' => '<rect x="3" y="3" width="6" height="6" rx="1"/><rect x="11" y="3" width="6" height="6" rx="1"/><rect x="3" y="11" width="6" height="6" rx="1"/><rect x="11" y="11" width="6" height="6" rx="1"/>',
        'reports' => '<path d="M3 16.5h14"/><rect x="4" y="9" width="3" height="6" rx=".6"/><rect x="8.5" y="5" width="3" height="10" rx=".6"/><rect x="13" y="11" width="3" height="4" rx=".6"/>',
        'traffic' => '<circle cx="8" cy="7" r="3.2"/><path d="M2.5 16.5c0-3 2.5-4.5 5.5-4.5s5.5 1.5 5.5 4.5"/><path d="M14 4.5h3.5M15.75 3.25v2.5"/><path d="M15 16.5h2.5"/>',
        'pages' => '<path d="M5 3.5h6.5L15 7v9.5H5V3.5Z"/><path d="M11.5 3.5V7H15"/><path d="M7.5 10.5h5M7.5 13h5"/>',
        'services' => '<path d="m10 3 7 3.5-7 3.5-7-3.5L10 3Z"/><path d="m3 10.5 7 3.5 7-3.5"/><path d="m3 14.2 7 3.5 7-3.5"/>',
        'cases' => '<path d="M3.5 16.5h13"/><path d="M6 16.5v-5M10 16.5V6M14 16.5v-8"/>',
        'articles' => '<path d="M4 4.5h9.5L16 7v8.5H4v-11Z"/><path d="M13.5 4.5V7H16"/><path d="M6.5 10h6M6.5 13h4"/>',
        'testimonials' => '<path d="M4 5.5h12v8H8l-4 3v-11Z"/><path d="M7 8.5h6M7 11h4"/>',
        'forms' => '<path d="M4 5.5h3v3H4v-3ZM4 11.5h3v3H4v-3ZM9.5 7h6.5M9.5 13h6.5"/>',
        'media' => '<rect x="3" y="4" width="14" height="12" rx="1.5"/><circle cx="7.3" cy="8.3" r="1.3"/><path d="m4 14.5 3.8-3.8 2.7 2.7 2.5-2.5 3 3"/>',
        'locales' => '<circle cx="10" cy="10" r="6.5"/><path d="M3.5 10h13M10 3.5c1.8 1.8 2.8 4 2.8 6.5S11.8 14.7 10 16.5c-1.8-1.8-2.8-4-2.8-6.5S8.2 5.3 10 3.5Z"/>',
        'collections' => '<rect x="3" y="3.5" width="6" height="6" rx="1"/><rect x="11" y="3.5" width="6" height="6" rx="1"/><rect x="3" y="11" width="6" height="6" rx="1"/><path d="M11 11h6v6h-6z"/>',
        'navigation' => '<path d="M2.5 5.5h.01M2.5 10h.01M2.5 14.5h.01"/><path d="M6 5.5h11M6 10h11M6 14.5h11"/>',
        'design' => '<circle cx="10" cy="10" r="7"/><circle cx="10" cy="10" r="2.5"/><path d="M10 3v2.5M10 14.5V17M3 10h2.5M14.5 10H17"/>',
        'seo' => '<circle cx="10" cy="10" r="6.5"/><path d="M13 7 7 13"/><circle cx="7.5" cy="7.5" r="1.5"/><circle cx="12.5" cy="12.5" r="1.5"/>',
        'showcase' => '<rect x="3" y="4" width="14" height="12" rx="1.5"/><circle cx="7.3" cy="8.3" r="1.3"/><path d="m4 14.5 3.8-3.8 2.7 2.7 2.5-2.5 3 3"/>',
        'messages' => '<rect x="3" y="5" width="14" height="10" rx="1.5"/><path d="m3.8 5.8 6.2 4.7 6.2-4.7"/>',
        'submissions' => '<path d="M3 11.5h4l1.5 2.5h3l1.5-2.5h4"/><path d="M5.5 4.5h9l2.5 7v4H3v-4l2.5-7Z"/>',
        'users' => '<circle cx="7.5" cy="7" r="2.5"/><path d="M3.5 16.5a4 4 0 0 1 8 0"/><path d="M13 5.2a2.5 2.5 0 0 1 0 4.6"/><path d="M14.5 12.8a4 4 0 0 1 2.5 3.7"/>',
        'roles' => '<path d="M10 2.5 3.5 5.5v4.2c0 4 2.7 7 6.5 8 3.8-1 6.5-4 6.5-8V5.5L10 2.5Z"/><path d="M7.5 10 9.2 11.7 13 8"/>',
        'activity' => '<path d="M3 10.5h3.5L8.5 5l3 10 2-4.5H17"/>',
        'products' => '<path d="m10 3 6.5 3.4v7.2L10 17l-6.5-3.4V6.4L10 3Z"/><path d="m3.8 6.6 6.2 3.3 6.2-3.3"/><path d="M10 9.9V17"/>',
        'categories' => '<rect x="8" y="3.2" width="4" height="3" rx=".8"/><rect x="3" y="13.8" width="4" height="3" rx=".8"/><rect x="13" y="13.8" width="4" height="3" rx=".8"/><path d="M10 6.2v3.3M5 13.8v-2h10v2M10 9.5v2.3"/>',
        'plans' => '<path d="M3.5 9.5V4.5a1 1 0 0 1 1-1h5l7 7-6 6-7-7Z"/><path d="M7 7h.01"/>',
        'orders' => '<path d="M5 6.5h10l-.8 9.5H5.8L5 6.5Z"/><path d="M7.5 6.5V5a2.5 2.5 0 0 1 5 0v1.5"/>',
        'shop-orders' => '<circle cx="8" cy="16.5" r="1.3"/><circle cx="14" cy="16.5" r="1.3"/><path d="M3 4h1.8l1.6 9.5h8L16.5 7H6"/>',
        'subscriptions' => '<path d="M4 8.5A4.5 4.5 0 0 1 8.5 4H15"/><path d="m12.5 1.8 2.8 2.2-2.8 2.2"/><path d="M16 11.5A4.5 4.5 0 0 1 11.5 16H5"/><path d="m7.5 18.2-2.8-2.2 2.8-2.2"/>',
        'gateways' => '<rect x="2.5" y="5" width="15" height="10" rx="1.5"/><path d="M2.5 8.5h15"/><path d="M5 12h3"/>',
        'profile' => '<circle cx="10" cy="7" r="3"/><path d="M4.5 16.5a5.5 5.5 0 0 1 11 0"/>',
        'business' => '<path d="M4.5 17V4.5a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1V17"/><path d="M12.5 8.5h3a1 1 0 0 1 1 1V17"/><path d="M3 17h14"/><path d="M6.5 6.5h3M6.5 9.5h3M6.5 12.5h3"/>',
        'system' => '<path d="M10 2.5v2M10 15.5v2M17.5 10h-2M4.5 10h-2M15.3 4.7l-1.4 1.4M6.1 13.9l-1.4 1.4M15.3 15.3l-1.4-1.4M6.1 6.1 4.7 4.7"/><circle cx="10" cy="10" r="3.2"/>',
        'sign-out' => '<path d="M12 5.5V4a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h7a1 1 0 0 0 1-1v-1.5"/><path d="M8 10h9"/><path d="m14 7 3 3-3 3"/>',
    ];
@endphp

<svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="shrink-0">
    {!! $glyphs[$name] ?? '' !!}
</svg>
