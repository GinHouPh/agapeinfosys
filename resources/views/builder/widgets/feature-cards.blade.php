@php
    $cards = $settings['cards'] ?? [];
    $columns = (string) ($settings['columns'] ?? '3');

    $columnClass = match ($columns) {
        '2' => 'sm:grid-cols-2',
        '4' => 'sm:grid-cols-2 lg:grid-cols-4',
        default => 'sm:grid-cols-2 lg:grid-cols-3',
    };
@endphp

<x-builder.partials.section :settings="$settings">
    <div class="shell">
        <x-builder.partials.head
            :title="$settings['title'] ?? ''"
            :subtitle="$settings['subtitle'] ?? ''"
            :mark="$settings['mark'] ?? ''"
            :align="$settings['align'] ?? 'left'"
        />

        <div class="grid gap-8 {{ $columnClass }}">
            @foreach ($cards as $card)
                @if (! empty($card['title']))
                    <div class="border border-line p-6 transition-colors duration-300 hover:border-accent">
                        <h5 class="mb-2 !text-xl text-white">{{ $card['title'] }}</h5>
                        @if (! empty($card['body']))
                            <p class="text-sm text-muted">{{ $card['body'] }}</p>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</x-builder.partials.section>
