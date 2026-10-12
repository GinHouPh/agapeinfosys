@extends('layouts.app')

@section('title', 'About Agape Infosys — Engineering Excellence, Consultancy & Delivery')

@section('description', 'Meet Agape Infosys: a senior team of software engineers and IT consultants delivering web, mobile and cloud systems since 2013.')

@section('content')
    {{-- Hero --}}
    <section class="relative overflow-hidden pt-40 pb-20">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_10%,rgba(217,4,41,0.18),transparent_55%)]"
             aria-hidden="true"></div>

        <div class="shell relative">
            <span class="eyebrow">About us</span>

            <h1 class="max-w-5xl">
                <span class="text-accent">A consultancy</span>
                built around the people who actually do the work.
            </h1>
        </div>
    </section>

    {{-- Story --}}
    <section class="section section--surface">
        <div class="shell grid-2 items-start gap-16">
            <div>
                <x-section-head
                    title="Agape Infosys runs on engineering excellence"
                    subtitle="Our story"
                    mark="about"
                />

                <p class="mb-6 text-body">
                    Agape Infosys started as a small engineering studio helping businesses replace
                    brittle spreadsheets and ageing legacy tools with software that could grow with
                    them. Today we are a full service software solutions and IT consultancy practice
                    covering product discovery, custom development, cloud operations and long term
                    support.
                </p>

                <p class="mb-6 text-body">
                    We keep teams deliberately small and senior. The consultant who scopes your
                    engagement is the same person in your weekly stand-up, and the engineer who
                    writes the code is the one who supports it after launch. That continuity is
                    why clients stay with us for years rather than sprints.
                </p>

                <p class="text-body">
                    Our work spans regulated industries, logistics, education, healthcare and
                    retail — sectors where uptime, data safety and clear communication are not
                    negotiable.
                </p>
            </div>

            <div class="grid gap-6">
                <x-media :label="'Studio'" ratio="" aspect="749/457" class="border border-line" />
                <div class="grid grid-cols-2 gap-6">
                    <x-media :label="'Workshops'" ratio="" aspect="749/457" class="border border-line" />
                    <x-media :label="'Delivery'" ratio="" aspect="749/457" class="border border-line" />
                </div>
            </div>
        </div>
    </section>

    {{-- Values --}}
    <section class="section">
        <div class="shell">
            <x-section-head
                title="What We Stand For"
                subtitle="Our values"
                align="center"
                mark="values"
            />

            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($values as $index => $value)
                    <article class="border-t border-line pt-8">
                        <span class="mb-5 block font-display text-sm tracking-[0.3em] text-accent">
                            {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                        </span>

                        <h5 class="mb-3 !text-2xl text-white">{{ $value['title'] ?? '' }}</h5>
                        <p class="text-body">{{ $value['body'] ?? '' }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Stats --}}
    <section class="section section--surface">
        <div class="shell grid-2 items-center gap-16">
            <div>
                <x-section-head
                    title="Numbers That Matter"
                    subtitle="By the numbers"
                    mark="features"
                />

                <p class="mb-10 max-w-lg text-body">
                    A decade and a half of shipping production software, measured the way our
                    clients measure it — delivered, stable and still running.
                </p>

                <x-btn :href="route('portfolio.index')" variant="ghost" label="See The Work" />
            </div>

            <div class="grid gap-x-10 gap-y-8 sm:grid-cols-2">
                @foreach ($stats as $stat)
                    <div class="stat">
                        <span class="stat__value">
                            <span data-count="{{ (int) ($stat['value'] ?? 0) }}">0</span>
                        </span>
                        <span class="stat__label">{{ $stat['label'] ?? '' }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Capabilities --}}
    @if ($services->isNotEmpty())
        <section class="section">
            <div class="shell">
                <x-section-head
                    title="What We Do Best"
                    subtitle="Core capabilities"
                    align="center"
                    mark="services"
                />

                <div class="grid gap-8 sm:grid-cols-3">
                    @foreach ($services as $service)
                        <a href="{{ route('services.show', $service) }}" class="group border border-line p-8 transition-colors duration-300 hover:border-accent">
                            <h5 class="card__title !text-2xl">{{ $service->title }}</h5>
                            @if ($service->excerpt)
                                <p class="mt-4 text-body">{{ $service->excerpt }}</p>
                            @endif
                            <span class="mt-6 inline-block font-display text-xs tracking-[0.24em] text-accent uppercase">
                                Read more
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Clients --}}
    @if ($clients->isNotEmpty())
        <section class="section section--surface section--tight">
            <div class="shell mb-12">
                <x-section-head
                    title="Trusted By Teams Like Yours"
                    subtitle="Who we work with"
                    align="center"
                    mark="clients"
                />
            </div>

            <div class="marquee logo-marquee border-y border-line py-8">
                <div class="marquee__track">
                    @foreach (range(1, 2) as $pass)
                        @foreach ($clients as $client)
                            <span class="marquee__item">
                                @if ($client->logo)
                                    <img src="{{ Storage::url($client->logo) }}" alt="{{ $client->name }}" class="h-10 w-auto opacity-60 grayscale transition hover:opacity-100 hover:grayscale-0">
                                @else
                                    {{ $client->name }}
                                @endif
                            </span>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- CTA --}}
    <section class="section section--tight">
        <div class="shell text-center">
            <h2 class="mx-auto max-w-4xl">
                <span class="text-accent">Ready</span>
                to scope your next build?
            </h2>

            <div class="mt-10 flex flex-wrap justify-center gap-4">
                <x-btn :href="route('contact.create')" variant="accent" label="Start A Project" />
                <x-btn :href="route('services.index')" variant="ghost" label="Browse Services" />
            </div>
        </div>
    </section>
@endsection
