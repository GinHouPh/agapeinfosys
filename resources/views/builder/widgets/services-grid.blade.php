@php
    use App\Models\Service;

    $query = Service::query()->published()->ordered();

    if (! empty($settings['featured_only'])) {
        $query->featured();
    }

    $limit = (int) ($settings['limit'] ?? 0);
    $services = $limit > 0 ? $query->limit($limit)->get() : $query->get();
@endphp

<x-builder.partials.section :settings="$settings">
    <div class="shell">
        <x-builder.partials.head
            :title="$settings['title'] ?? ''"
            :subtitle="$settings['subtitle'] ?? ''"
            :mark="$settings['mark'] ?? ''"
            :align="$settings['align'] ?? 'center'"
        />

        @if ($services->isEmpty())
            <p class="text-muted">Services will be published shortly.</p>
        @else
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <a href="{{ route('services.show', $service) }}" class="group card">
                        <x-media
                            :src="$service->image ? \Illuminate\Support\Facades\Storage::url($service->image) : null"
                            :label="$service->title"
                            ratio=""
                            :aspect="$loop->index % 2 === 0 ? '475/590' : '475/520'"
                        />

                        <div class="mt-5 flex items-start justify-between gap-4">
                            <h4 class="card__title !text-2xl">
                                {{ $service->title }}
                            </h4>

                            <span class="mt-1 font-display text-xs tracking-[0.2em] text-muted uppercase">
                                {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>

                        @if ($service->excerpt)
                            <p class="mt-3 text-sm text-muted">{{ $service->excerpt }}</p>
                        @endif
                    </a>
                @endforeach
            </div>

            @if (! empty($settings['show_button']) && ! empty($settings['button_label']) && ! empty($settings['button_url']))
                <div class="mt-14 text-center">
                    <x-btn :href="$settings['button_url']" variant="ghost" :label="$settings['button_label']" />
                </div>
            @endif
        @endif
    </div>
</x-builder.partials.section>
