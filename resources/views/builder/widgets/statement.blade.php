<x-builder.partials.section :settings="$settings" spacing="tight">
    <div class="shell">
        <h2 class="mx-auto max-w-5xl text-center">
            @if ($settings['accent'] ?? '')
                <span class="text-accent">{{ $settings['accent'] }}</span>
            @endif
            {{ $settings['text'] ?? '' }}
        </h2>
    </div>
</x-builder.partials.section>
