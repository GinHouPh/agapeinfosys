@props([
    'src' => null,
    'label' => '',
    'ratio' => 'aspect-4/5',
    'aspect' => null,
    'decorative' => true,
])

@php
    $text = trim($label);
    $seed = $text !== '' ? abs(crc32($text)) : 0;
    $variant = $seed % 3;
    $gradientId = 'media-'.uniqid();
@endphp

<div {{ $attributes->merge(['class' => "media $ratio", 'style' => $aspect ? "aspect-ratio: $aspect" : null]) }}>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $text }}" loading="lazy" decoding="async">
    @else
        <svg viewBox="0 0 480 600" preserveAspectRatio="xMidYMid slice" class="h-full w-full" role="img" aria-label="{{ $text }}">
            <defs>
                <linearGradient id="{{ $gradientId }}" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="#1b1b1b"/>
                    <stop offset="55%" stop-color="#141414"/>
                    <stop offset="100%" stop-color="#0c0c0c"/>
                </linearGradient>
            </defs>

            <rect width="480" height="600" fill="url(#{{ $gradientId }})"/>

            @if ($variant === 0)
                <g fill="none" stroke="#383838" stroke-width="1">
                    <circle cx="360" cy="140" r="60"/>
                    <circle cx="360" cy="140" r="105"/>
                    <circle cx="360" cy="140" r="150"/>
                    <path d="M0 460 L480 300"/>
                </g>
                <circle cx="360" cy="140" r="8" fill="#d90429"/>
            @elseif ($variant === 1)
                <g stroke="#2c2c2c" stroke-width="1">
                    @for ($i = -6; $i < 16; $i++)
                        <path d="M{{ $i * 60 }} 0 L{{ $i * 60 + 600 }} 600"/>
                    @endfor
                </g>
                <path d="M60 600 L420 0" stroke="#d90429" stroke-width="2"/>
            @else
                <g fill="none" stroke="#383838" stroke-width="1">
                    <rect x="70" y="110" width="340" height="340"/>
                    <rect x="120" y="160" width="240" height="240"/>
                    <path d="M70 110 L410 450 M410 110 L70 450"/>
                </g>
                <rect x="228" y="278" width="24" height="24" fill="#d90429"/>
            @endif

            @if ($text !== '' && $decorative)
                <text
                    x="240"
                    y="540"
                    text-anchor="middle"
                    fill="#4f4f4f"
                    font-family="Akshar, sans-serif"
                    font-size="26"
                    letter-spacing="6"
                >{{ mb_strtoupper(mb_strimwidth($text, 0, 26, '…')) }}</text>
            @endif
        </svg>
    @endif
</div>
