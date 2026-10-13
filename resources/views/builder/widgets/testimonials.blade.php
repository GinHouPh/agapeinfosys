@php
    use App\Models\Testimonial;

    $query = Testimonial::query()->published()->ordered();

    $limit = (int) ($settings['limit'] ?? 0);
    $testimonials = $limit > 0 ? $query->limit($limit)->get() : $query->get();
@endphp

<x-builder.partials.section :settings="$settings">
    <div class="shell">
        <x-builder.partials.head
            :title="$settings['title'] ?? ''"
            :subtitle="$settings['subtitle'] ?? ''"
            :mark="$settings['mark'] ?? ''"
            :align="$settings['align'] ?? 'center'"
        />

        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($testimonials as $testimonial)
                <figure class="quote-card">
                    <span class="quote-card__mark" aria-hidden="true">&ldquo;</span>

                    <div class="flex items-center gap-4 pt-4">
                        @if ($testimonial->avatar)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($testimonial->avatar) }}" alt="{{ $testimonial->name }}" class="h-14 w-14 rounded-full object-cover" loading="lazy">
                        @else
                            <span class="grid h-14 w-14 place-items-center rounded-full border border-line font-display text-lg text-accent">
                                {{ mb_strtoupper(mb_substr($testimonial->name, 0, 1)) }}
                            </span>
                        @endif

                            <figcaption>
                                <span class="block font-display text-lg tracking-[0.06em] text-white capitalize">
                                    {{ $testimonial->name }}
                                </span>
                                <span class="text-sm text-muted">
                                    {{ trim(($testimonial->role ?? '').($testimonial->company ? ', '.$testimonial->company : ''), ', ') }}
                                </span>
                            </figcaption>
                    </div>

                    <blockquote class="text-body">
                        {{ $testimonial->quote }}
                    </blockquote>

                    <div class="mt-auto flex gap-1 text-accent" aria-label="{{ $testimonial->rating }} out of 5">
                        @foreach (range(1, 5) as $star)
                            <span aria-hidden="true">{{ $star <= $testimonial->rating ? '★' : '☆' }}</span>
                        @endforeach
                    </div>
                </figure>
            @empty
                <p class="text-muted md:col-span-2 lg:col-span-3">Testimonials are being collected.</p>
            @endforelse
        </div>
    </div>
</x-builder.partials.section>
