@php
    $topLines = array_values(array_filter(array_map('trim', explode("\n", (string) ($settings['title_top'] ?? '')))));
    $gradient = $settings['gradient'] ?? 'right';
    $showCard = (bool) ($settings['show_card'] ?? true);
    $cardStats = $settings['card_stats'] ?? [];
    $buttons = $settings['buttons'] ?? [];
    $spacing = $settings['spacing'] ?? 'default';
    $ptClass = $spacing === 'none' ? 'pt-8' : ($spacing === 'tight' ? 'pt-20' : 'pt-24');
    $pbClass = $spacing === 'none' ? 'pb-8' : 'pb-20';

    $glow = match ($gradient) {
        'left' => 'circle_at_22%_14%',
        'center' => 'circle_at_50%_10%',
        default => 'circle_at_78%_18%',
    };
@endphp

<section class="relative flex min-h-[calc(100svh_-_7.5rem)] items-center overflow-hidden {{ $ptClass }} {{ $pbClass }}">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient({{ $glow }},rgba(217,4,41,0.20),transparent_58%)]"
         aria-hidden="true"></div>
    <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_right,rgba(56,56,56,0.35)_1px,transparent_1px),linear-gradient(to_bottom,rgba(56,56,56,0.35)_1px,transparent_1px)] bg-[size:80px_80px] [mask-image:radial-gradient(ellipse_at_center,black,transparent_78%)]"
         aria-hidden="true"></div>

    <div class="shell relative grid items-center gap-14 lg:grid-cols-12">
        <div @class(['lg:col-span-7' => $showCard, 'lg:col-span-12' => ! $showCard])>
            @if ($settings['eyebrow'] ?? '')
                <span class="eyebrow">{{ $settings['eyebrow'] }}</span>
            @endif

            @if ($topLines || ($settings['title_accent'] ?? ''))
                <h1 class="mb-8">
                    @foreach ($topLines as $line)
                        <span class="block text-white">{{ $line }}</span>
                    @endforeach
                    @if ($settings['title_accent'] ?? '')
                        <span class="block text-accent">{{ $settings['title_accent'] }}</span>
                    @endif
                </h1>
            @endif

            @if ($settings['lead'] ?? '')
                <p class="mb-10 max-w-xl text-lg text-body">{{ $settings['lead'] }}</p>
            @endif

            @if (count($buttons))
                <div class="flex flex-wrap gap-4">
                    @foreach ($buttons as $button)
                        @if (! empty($button['label']) && ! empty($button['url']))
                            <x-btn :href="$button['url']" :variant="$button['variant'] ?? 'ghost'" :label="$button['label']" />
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        @if ($showCard)
            <div class="lg:col-span-5">
                <div class="relative border border-line bg-surface/70 p-8 backdrop-blur">
                    @if ($settings['card_badge'] ?? '')
                        <span class="absolute -top-3 left-8 bg-ink px-3 font-display text-xs tracking-[0.3em] text-accent uppercase">
                            {{ $settings['card_badge'] }}
                        </span>
                    @endif

                    @if ($settings['card_quote'] ?? '')
                        <p class="font-display text-2xl leading-snug tracking-[0.06em] text-white capitalize">
                            {{ $settings['card_quote'] }}
                        </p>
                    @endif

                    @if (count($cardStats))
                        <div class="mt-8 grid grid-cols-2 gap-6">
                            @foreach ($cardStats as $stat)
                                @if (! empty($stat['label']))
                                    <div class="border-t border-line pt-4">
                                        <span class="block font-display text-4xl text-white">
                                            <span data-count="{{ (int) ($stat['value'] ?? 0) }}">0</span>{{ $stat['suffix'] ?? '' }}
                                        </span>
                                        <span class="text-sm text-muted">{{ $stat['label'] }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    @if ($settings['show_scroll'] ?? true)
        <x-scroll-cue />
    @endif
</section>
