@props([
    'showMark' => true,
    'showText' => true,
    'iconClass' => 'grid h-9 w-9 shrink-0 place-items-center border border-line font-display text-base tracking-normal text-accent',
    'iconImgClass' => 'h-full w-full object-contain',
    'fullImgClass' => 'h-9 w-auto max-w-[14rem] object-contain',
    'textClass' => 'font-display text-lg tracking-[0.22em] text-white uppercase',
    'class' => 'inline-flex items-center gap-3',
])

@php
    $business = \App\Models\Setting::structured('business', []);
    $brandName = trim((string) ($business['brand_name'] ?? 'Agape Infosys')) ?: 'Agape Infosys';

    $words = preg_split('/\s+/', $brandName) ?: [$brandName];
    $tail = count($words) > 1 ? array_pop($words) : '';
    $lead = implode(' ', $words);
    $initial = mb_strtoupper(mb_substr($brandName, 0, 1));

    // Both theme variants are rendered; app.css shows only the one that matches
    // the <html> theme class, so the logo swaps the instant the theme toggles.
    $logos = \App\Models\Setting::structured('brand_logos', []);
@endphp

@foreach (['light', 'dark'] as $theme)
    @php
        $config = $logos[$theme] ?? [];
        $mode = $config['mode'] ?? 'default';
        $mode = in_array($mode, ['default', 'full', 'icon'], true) ? $mode : 'default';
        $full = $config['full'] ?? null;
        $icon = $config['icon'] ?? null;
    @endphp

    <span class="brand brand--{{ $theme }} {{ $class }}">
        @if ($mode === 'full' && $full)
            <img src="{{ Storage::url($full) }}" alt="{{ $brandName }}" class="{{ $fullImgClass }}">
        @else
            @if ($showMark)
                @if ($mode === 'icon' && $icon)
                    <span class="{{ $iconClass }}">
                        <img src="{{ Storage::url($icon) }}" alt="{{ $brandName }}" class="{{ $iconImgClass }}">
                    </span>
                @else
                    <span class="{{ $iconClass }}">{{ $initial }}</span>
                @endif
            @endif

            @if ($showText)
                <span class="{{ $textClass }}">{{ $lead }}<span class="text-accent">{{ $tail }}</span></span>
            @endif
        @endif
    </span>
@endforeach
