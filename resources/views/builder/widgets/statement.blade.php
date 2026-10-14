<x-builder.partials.section :settings="$settings" spacing="tight">
    @php
        // A colour control stores the NAME of a design token. Tailwind only
        // generates classes it can see written out, so the token names are
        // spelled here as a literal map rather than glued together at
        // runtime — the same reason the section's column counts are. Every
        // entry in DesignTokens::COLORS needs a line here, or that token
        // silently falls back to the accent.
        $tones = [
            'accent' => 'text-accent',
            'accent-soft' => 'text-accent-soft',
            'on-accent' => 'text-on-accent',
            'headline' => 'text-headline',
            'body' => 'text-body',
            'muted' => 'text-muted',
            'surface' => 'text-surface',
            'box' => 'text-box',
            'ink' => 'text-ink',
            'line' => 'text-line',
        ];
        $tone = $tones[$settings['accent_color'] ?? ''] ?? $tones['accent'];
    @endphp
    <div class="shell">
        <h2 class="mx-auto max-w-5xl text-center">
            @if ($settings['accent'] ?? '')
                <span class="{{ $tone }}">{{ $settings['accent'] }}</span>
            @endif
            {{ $settings['text'] ?? '' }}
        </h2>
    </div>
</x-builder.partials.section>
