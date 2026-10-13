@php
    $steps = $settings['steps'] ?? [];
@endphp

<x-builder.partials.section :settings="$settings">
    <div class="shell">
        <x-builder.partials.head
            :title="$settings['title'] ?? ''"
            :subtitle="$settings['subtitle'] ?? ''"
            :mark="$settings['mark'] ?? ''"
            :align="$settings['align'] ?? 'center'"
        />

        <div class="grid gap-10 md:grid-cols-3">
            @foreach ($steps as $index => $step)
                @if (! empty($step['title']))
                    <article class="relative border-t border-line pt-8">
                        <span class="mb-6 block font-display text-6xl leading-none text-accent">
                            {{ $step['number'] ?? str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                        </span>

                        <h5 class="mb-4 !text-2xl text-white">{{ $step['title'] }}</h5>
                        @if (! empty($step['body']))
                            <p class="text-body">{{ $step['body'] }}</p>
                        @endif
                    </article>
                @endif
            @endforeach
        </div>
    </div>
</x-builder.partials.section>
