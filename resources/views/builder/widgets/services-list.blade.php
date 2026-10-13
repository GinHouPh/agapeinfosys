@php
    use App\Models\Service;

    $query = Service::query()->published()->ordered();

    $limit = (int) ($settings['limit'] ?? 0);
    $services = $limit > 0 ? $query->limit($limit)->get() : $query->get();
@endphp

<x-builder.partials.section :settings="$settings">
    <div class="shell">
        @if ($services->isEmpty())
            <p class="text-muted">Services will be published shortly.</p>
        @else
            <div class="space-y-8">
                @foreach ($services as $service)
                    <article class="grid items-center gap-8 border-b border-line pb-8 lg:grid-cols-12">
                        <div class="lg:col-span-4">
                            <a href="{{ route('services.show', $service) }}" class="group block">
                                <x-media
                                    :src="$service->image ? \Illuminate\Support\Facades\Storage::url($service->image) : null"
                                    :label="$service->title"
                                    ratio=""
                                    aspect="475/420"
                                />
                            </a>
                        </div>

                        <div class="lg:col-span-8">
                            <div class="flex flex-wrap items-center gap-4">
                                <span class="font-display text-sm tracking-[0.3em] text-accent">
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                @if ($service->eyebrow)
                                    <span class="tag">{{ $service->eyebrow }}</span>
                                @endif
                            </div>

                            <h4 class="card__title mt-4 !text-3xl">
                                <a href="{{ route('services.show', $service) }}">{{ $service->title }}</a>
                            </h4>

                            @if ($service->summary)
                                <p class="mt-4 max-w-3xl text-body">{{ $service->summary }}</p>
                            @elseif ($service->excerpt)
                                <p class="mt-4 max-w-3xl text-body">{{ $service->excerpt }}</p>
                            @endif

                            @if (! empty($settings['show_button']))
                                <div class="mt-6">
                                    <x-btn :href="route('services.show', $service)" variant="ghost" label="Read More" />
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-builder.partials.section>
