@extends('layouts.app')

@section('title', $service->title.' | Agape Infosys Services')

@section('description', $service->excerpt ?: $service->title.' delivered by Agape Infosys.')

@section('content')
    <section class="relative overflow-hidden pt-40 pb-16">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_85%_20%,rgba(217,4,41,0.16),transparent_55%)]"
             aria-hidden="true"></div>

        <div class="shell relative">
            <nav class="mb-8 flex flex-wrap items-center gap-3 font-display text-xs tracking-[0.24em] text-muted uppercase" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-accent">Home</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('services.index') }}" class="hover:text-accent">Services</a>
                <span aria-hidden="true">/</span>
                <span class="text-body">{{ $service->title }}</span>
            </nav>

            <span class="eyebrow">{{ $service->eyebrow ?: 'Service' }}</span>

            <h1 class="max-w-4xl">{{ $service->title }}</h1>

            @if ($service->summary)
                <p class="mt-8 max-w-3xl text-lg text-body">{{ $service->summary }}</p>
            @endif
        </div>
    </section>

    <section class="section section--tight">
        <div class="shell grid-2 items-start gap-16">
            <div class="lg:sticky lg:top-28">
                <x-media
                    :src="$service->image ? Storage::url($service->image) : null"
                    :label="$service->title"
                    ratio=""
                    aspect="475/560"
                    class="border border-line"
                />

                <div class="mt-8 border border-line p-7">
                    <h6 class="mb-4 text-base text-white uppercase">Engage us</h6>
                    <p class="mb-6 text-sm text-body">
                        Need this capability embedded in your team? We can start with a two week
                        discovery sprint.
                    </p>
                    <x-btn :href="route('contact.create')" variant="accent" label="Request A Quote" />
                </div>
            </div>

            <div>
                @if ($service->body)
                    <div class="prose-dark">
                        {!! $service->body !!}
                    </div>
                @elseif ($service->excerpt)
                    <p class="text-lg text-body">{{ $service->excerpt }}</p>
                @endif

                @if (! empty($service->highlights))
                    <div class="mt-10">
                        <h5 class="mb-6 text-white">What you get</h5>
                        <ul class="space-y-4">
                            @foreach ($service->highlights as $highlight)
                                <li class="flex items-start gap-4 border-b border-line pb-4">
                                    <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                                    <span class="text-body">{{ $highlight }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </section>

    @if ($others->isNotEmpty())
        <section class="section section--surface">
            <div class="shell">
                <x-section-head
                    title="Related Services"
                    subtitle="Keep exploring"
                    align="center"
                />

                <div class="grid gap-8 sm:grid-cols-3">
                    @foreach ($others as $other)
                        <a href="{{ route('services.show', $other) }}" class="group border border-line p-7 transition-colors duration-300 hover:border-accent">
                            <h5 class="card__title !text-xl">{{ $other->title }}</h5>
                            @if ($other->excerpt)
                                <p class="mt-3 text-sm text-muted">{{ $other->excerpt }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
