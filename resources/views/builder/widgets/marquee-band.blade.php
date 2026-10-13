@php
    $words = array_values(array_filter(array_map(function ($row) {
        return trim((string) ($row['text'] ?? ''));
    }, $settings['words'] ?? [])));

    $sizeClass = ($settings['size'] ?? 'lg') === 'md' ? '!text-2xl md:!text-4xl' : '!text-3xl md:!text-5xl';
    $reverseClass = ($settings['reverse'] ?? false) ? 'marquee--reverse' : '';
@endphp

@if (count($words))
    <section class="border-y border-line bg-surface py-6" aria-label="Capabilities">
        <div class="marquee {{ $reverseClass }}">
            <div class="marquee__track">
                @foreach (range(1, 2) as $pass)
                    @foreach ($words as $word)
                        <span class="marquee__item {{ $sizeClass }}">
                            {{ $word }}
                            <span class="marquee__dot" aria-hidden="true"></span>
                        </span>
                    @endforeach
                @endforeach
            </div>
        </div>
    </section>
@endif
