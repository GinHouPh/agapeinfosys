@php
    $records = \App\Models\CollectionRecord::query()
        ->whereHas('collectionType', fn ($query) => $query->where('slug', $settings['collection'] ?? ''))
        ->published()
        ->orderBy('sort_order')
        ->orderBy('id')
        ->limit(max(1, (int) ($settings['limit'] ?? 6)))
        ->get();
@endphp

<x-builder.partials.section :settings="$settings">
    <div class="shell">
        @if ($records->isEmpty())
            <p class="text-center text-muted">{{ $settings['empty_text'] ?? 'Nothing here yet.' }}</p>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($records as $record)
                    <a href="{{ url($record->publicPath()) }}"
                       class="group border border-line p-6 transition-colors duration-300 hover:border-accent">
                        <h3 class="font-display text-lg tracking-[0.06em] text-white uppercase group-hover:text-accent">
                            {{ $record->title }}
                        </h3>

                        @foreach ($record->collectionType?->fields ?? [] as $field)
                            @php($value = $record->value($field->name))

                            @continue($value === null || $value === '' || $value === [])

                            <p class="mt-3 text-sm text-body">
                                @if (is_array($value))
                                    {{ implode(', ', array_filter($value, 'is_scalar')) }}
                                @else
                                    {{ $value }}
                                @endif
                            </p>
                        @endforeach
                    </a>
                @endforeach
            </div>
        @endif

        @if (($settings['show_button'] ?? false) && ($settings['button_url'] ?? '') !== '')
            <div class="mt-10 text-center">
                <x-btn :href="$settings['button_url']" :label="$settings['button_label'] ?? 'See all'" />
            </div>
        @endif
    </div>
</x-builder.partials.section>
