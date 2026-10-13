@php
    $values = $settings['values'] ?? [];
    $columns = (string) ($settings['columns'] ?? '4');

    $columnClass = match ($columns) {
        '2' => 'sm:grid-cols-2',
        '3' => 'sm:grid-cols-3',
        default => 'sm:grid-cols-2 lg:grid-cols-4',
    };
@endphp

<x-builder.partials.section :settings="$settings">
    <div class="shell">
        <x-builder.partials.head
            :title="$settings['title'] ?? ''"
            :subtitle="$settings['subtitle'] ?? ''"
            :mark="$settings['mark'] ?? ''"
            :align="$settings['align'] ?? 'center'"
        />

        <div class="grid gap-8 {{ $columnClass }}">
            @foreach ($values as $index => $value)
                @if (! empty($value['title']))
                    <article class="border-t border-line pt-8">
                        <span class="mb-5 block font-display text-sm tracking-[0.3em] text-accent">
                            {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                        </span>

                        <h5 class="mb-3 !text-2xl text-white">{{ $value['title'] }}</h5>
                        @if (! empty($value['body']))
                            <p class="text-body">{{ $value['body'] }}</p>
                        @endif
                    </article>
                @endif
            @endforeach
        </div>
    </div>
</x-builder.partials.section>
