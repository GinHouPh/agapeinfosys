@extends('layouts.app')

@section('title', 'Case Studies — Selected Work | Agape Infosys')

@section('description', 'Selected software engineering case studies from Agape Infosys: web platforms, mobile products, cloud migrations and enterprise integrations.')

@section('content')
    <section class="relative overflow-hidden pt-24 pb-16 lg:pb-28">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_25%_15%,rgba(217,4,41,0.18),transparent_55%)]"
             aria-hidden="true"></div>

        <div class="shell relative">
            <span class="eyebrow">Portfolio</span>
            <h1 class="max-w-5xl">
                <span class="text-accent">Work</span>
                that shipped, scaled and stayed shipped.
            </h1>

            <p class="mt-8 max-w-2xl text-lg text-body">
                A selection of the platforms, products and systems we have designed, built and
                operated for our clients.
            </p>
        </div>

        <x-scroll-cue />
    </section>

    <section class="section section--surface">
        <div class="shell">
            @if ($categories->isNotEmpty())
                <div class="mb-12 flex flex-wrap gap-3">
                    <a
                        href="{{ route('portfolio.index') }}"
                        class="tag {{ $activeCategory === null ? 'border-accent text-accent' : '' }}"
                    >
                        All
                    </a>

                    @foreach ($categories as $category)
                        <a
                            href="{{ route('portfolio.index', ['category' => $category]) }}"
                            class="tag {{ $activeCategory === $category ? 'border-accent text-accent' : '' }}"
                        >
                            {{ $category }}
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($projects->isEmpty())
                <p class="text-muted">No case studies published yet.</p>
            @else
                <div class="grid gap-10 md:grid-cols-2">
                    @foreach ($projects as $project)
                        <a href="{{ route('portfolio.show', $project) }}" class="group card">
                            <x-media
                                :src="$project->cover_image ? Storage::url($project->cover_image) : null"
                                :label="$project->title"
                                ratio=""
                                aspect="666/530"
                            />

                            <div class="mt-6 flex flex-wrap items-center gap-3">
                                @if ($project->category)
                                    <span class="tag">{{ $project->category }}</span>
                                @endif
                                @if ($project->client)
                                    <span class="text-xs tracking-[0.2em] text-muted uppercase">{{ $project->client }}</span>
                                @endif
                                @if ($project->year)
                                    <span class="text-xs tracking-[0.2em] text-muted uppercase">{{ $project->year }}</span>
                                @endif
                            </div>

                            <h4 class="card__title mt-5 !text-2xl">{{ $project->title }}</h4>

                            @if ($project->excerpt)
                                <p class="mt-3 text-body">{{ $project->excerpt }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
