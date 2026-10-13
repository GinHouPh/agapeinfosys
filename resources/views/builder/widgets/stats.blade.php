@php
    $stats = $settings['stats'] ?? [];
    $columns = (string) ($settings['columns'] ?? '2');

    $columnClass = match ($columns) {
        '3' => 'sm:grid-cols-3',
        '4' => 'sm:grid-cols-2 lg:grid-cols-4',
        default => 'sm:grid-cols-2',
    };
@endphp

<x-builder.partials.section :settings="$settings">
    <div class="shell">
        <x-builder.partials.head
            :title="$settings['title'] ?? ''"
            :subtitle="$settings['subtitle'] ?? ''"
            :mark="$settings['mark'] ?? ''"
        />

        <div class="grid gap-x-10 gap-y-8 {{ $columnClass }}">
            @foreach ($stats as $stat)
                @if (! empty($stat['label']))
                    <div class="stat">
                        <span class="stat__value">
                            <span data-count="{{ (int) ($stat['value'] ?? 0) }}">0</span>
                        </span>
                        <span class="stat__label">{{ $stat['label'] }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</x-builder.partials.section>
