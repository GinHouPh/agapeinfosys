@extends('layouts.app')

@section('title', 'Services — Software Development & IT Consultancy | Agape Infosys')

@section('description', 'Custom software development, web and mobile engineering, cloud and DevOps, digital transformation consulting and product design from Agape Infosys.')

@section('content')
    <section class="relative overflow-hidden pt-40 pb-20">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_75%_15%,rgba(217,4,41,0.18),transparent_55%)]"
             aria-hidden="true"></div>

        <div class="shell relative">
            <span class="eyebrow">Services</span>
            <h1 class="max-w-5xl">
                <span class="text-accent">Capabilities</span>
                that carry a product from idea to production.
            </h1>

            <p class="mt-8 max-w-2xl text-lg text-body">
                Engage us for a single discipline or a full product team. Every service is
                delivered by senior practitioners with a documented, measurable process.
            </p>
        </div>
    </section>

    <section class="section section--surface">
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
                                        :src="$service->image ? Storage::url($service->image) : null"
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

                                <div class="mt-6">
                                    <x-btn :href="route('services.show', $service)" variant="ghost" label="Read More" />
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="section section--tight">
        <div class="shell text-center">
            <h2 class="mx-auto max-w-4xl">
                Not sure which service <span class="text-accent">fits</span> your problem?
            </h2>
            <p class="mx-auto mt-6 max-w-2xl text-body">
                Book a discovery call. We will help you shape the scope before you commit to anything.
            </p>

            <div class="mt-10 flex flex-wrap justify-center gap-4">
                <x-btn :href="route('contact.create')" variant="accent" label="Talk To A Consultant" />
            </div>
        </div>
    </section>
@endsection
