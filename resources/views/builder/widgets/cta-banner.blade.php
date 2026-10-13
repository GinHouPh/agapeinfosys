@php
    $buttons = $settings['buttons'] ?? [];
@endphp

<x-builder.partials.section :settings="$settings" spacing="tight">
    <div class="shell text-center">
        <h2 class="mx-auto max-w-4xl">
            @if ($settings['accent'] ?? '')
                <span class="text-accent">{{ $settings['accent'] }}</span>
            @endif
            {{ $settings['title'] ?? '' }}
        </h2>

        @if ($settings['lead'] ?? '')
            <p class="mx-auto mt-6 max-w-2xl text-body">{{ $settings['lead'] }}</p>
        @endif

        @if (count($buttons))
            <div class="mt-10 flex flex-wrap justify-center gap-4">
                @foreach ($buttons as $button)
                    @if (! empty($button['label']) && ! empty($button['url']))
                        <x-btn :href="$button['url']" :variant="$button['variant'] ?? 'accent'" :label="$button['label']" />
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</x-builder.partials.section>
