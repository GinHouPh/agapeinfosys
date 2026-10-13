@php
    $images = $settings['images'] ?? [];
    $paragraphs = $settings['paragraphs'] ?? [];
    $mediaSide = $settings['media_side'] ?? 'left';
    $mediaFirst = $mediaSide === 'left';
@endphp

<x-builder.partials.section :settings="$settings">
    <div class="shell grid-2 items-center gap-16">
        @if ($mediaFirst)
            <div class="relative">
                <x-builder.partials.media-stack :images="$images" />
            </div>
        @endif

        <div>
            <x-builder.partials.head
                :title="$settings['title'] ?? ''"
                :subtitle="$settings['subtitle'] ?? ''"
                :mark="$settings['mark'] ?? ''"
            />

            @foreach ($paragraphs as $index => $paragraph)
                @if (! empty($paragraph['text']))
                    <p @class(['mb-6 text-body' => $index < count($paragraphs) - 1, 'mb-10 text-body' => $index === count($paragraphs) - 1])>
                        {{ $paragraph['text'] }}
                    </p>
                @endif
            @endforeach

            @if (! empty($settings['button_label']) && ! empty($settings['button_url']))
                <div class="mt-2">
                    <x-btn :href="$settings['button_url']" variant="solid" :label="$settings['button_label']" />
                </div>
            @endif
        </div>

        @if (! $mediaFirst)
            <div class="relative">
                <x-builder.partials.media-stack :images="$images" />
            </div>
        @endif
    </div>
</x-builder.partials.section>
