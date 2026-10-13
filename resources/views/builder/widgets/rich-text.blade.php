@php
    $paragraphs = $settings['paragraphs'] ?? [];
@endphp

<x-builder.partials.section :settings="$settings">
    <div @class(['shell' => true, 'text-center' => ($settings['align'] ?? 'left') === 'center'])>
        <x-builder.partials.head
            :title="$settings['title'] ?? ''"
            :subtitle="$settings['subtitle'] ?? ''"
            :mark="$settings['mark'] ?? ''"
            :align="$settings['align'] ?? 'left'"
        />

        <div @class(['prose-dark' => true, 'mx-auto max-w-3xl' => ($settings['align'] ?? 'left') === 'center'])>
            @foreach ($paragraphs as $paragraph)
                @if (! empty($paragraph['text']))
                    <p>{{ $paragraph['text'] }}</p>
                @endif
            @endforeach
        </div>
    </div>
</x-builder.partials.section>
