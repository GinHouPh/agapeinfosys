@extends('layouts.app')

@section('title', $project->title.' | Agape Infosys Case Study')

@section('description', $project->excerpt ?: $project->title.' — a case study by Agape Infosys.')

@section('content')
    <section class="relative overflow-hidden pt-40 pb-12">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_80%_20%,rgba(217,4,41,0.16),transparent_55%)]"
             aria-hidden="true"></div>

        <div class="shell relative">
            <nav class="mb-8 flex flex-wrap items-center gap-3 font-display text-xs tracking-[0.24em] text-muted uppercase" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-accent">Home</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('portfolio.index') }}" class="hover:text-accent">Portfolio</a>
                <span aria-hidden="true">/</span>
                <span class="text-body">{{ $project->title }}</span>
            </nav>

            <div class="flex flex-wrap items-center gap-3">
                @if ($project->category)
                    <span class="eyebrow !mb-0">{{ $project->category }}</span>
                @endif

                @if ($project->is_featured)
                    <span class="tag border-accent text-accent">Featured Case Study</span>
                @endif
            </div>

            <h1 class="mt-4 max-w-4xl">{{ $project->title }}</h1>

            @if ($project->excerpt)
                <p class="mt-6 max-w-3xl text-lg text-body">{{ $project->excerpt }}</p>
            @endif

            @if ($project->client || $project->service_type || $project->year)
                <div class="mt-10 grid gap-6 border-y border-line py-6 sm:grid-cols-3">
                    @if ($project->client)
                        <div>
                            <p class="font-display text-xs tracking-[0.24em] text-muted uppercase">Client</p>
                            <p class="mt-1 text-base text-white">{{ $project->client }}</p>
                        </div>
                    @endif

                    @if ($project->service_type)
                        <div>
                            <p class="font-display text-xs tracking-[0.24em] text-muted uppercase">Engagement</p>
                            <p class="mt-1 text-base text-white">{{ $project->service_type }}</p>
                        </div>
                    @endif

                    @if ($project->year)
                        <div>
                            <p class="font-display text-xs tracking-[0.24em] text-muted uppercase">Year</p>
                            <p class="mt-1 text-base text-white">{{ $project->year }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <section class="section section--tight pt-4">
        <div class="shell">
            <div class="border border-line bg-surface">
                <x-media
                    :src="$project->cover_image ? Storage::url($project->cover_image) : null"
                    :label="$project->title"
                    ratio=""
                    aspect="1300/560"
                />

                @if ($project->project_url || ! empty($project->gallery))
                    <div class="flex flex-wrap items-center justify-between gap-4 border-t border-line px-6 py-4">
                        <div class="flex flex-wrap items-center gap-3">
                            @if ($project->project_url)
                                <a href="{{ $project->project_url }}" target="_blank" rel="noopener noreferrer" class="btn btn--accent">
                                    Visit Live Project
                                    <span class="btn__strip" aria-hidden="true"></span>
                                </a>
                            @endif

                            @if (! empty($project->gallery))
                                <a href="#project-gallery" class="btn btn--ghost">
                                    Screenshots ({{ count($project->gallery) }})
                                    <span class="btn__strip" aria-hidden="true"></span>
                                </a>
                            @endif
                        </div>

                        @if ($project->category)
                            <span class="font-display text-xs tracking-[0.2em] text-muted uppercase">{{ $project->category }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="section section--tight pt-0">
        <div class="shell grid gap-12 lg:grid-cols-[minmax(0,1fr)_360px] lg:gap-16 items-start">
            <div class="min-w-0">
                @if ($project->body)
                    <div class="prose-dark">
                        {!! $project->body !!}
                    </div>
                @endif

                @if (! empty($project->outcomes))
                    <div class="mt-12 border-t border-line pt-10">
                        <span class="eyebrow">Impact</span>
                        <h2 class="text-2xl text-white">Key Outcomes</h2>

                        <ul class="mt-6 grid gap-4 sm:grid-cols-2">
                            @foreach ($project->outcomes as $index => $outcome)
                                <li class="admin-card flex items-start gap-4 !p-5">
                                    <span class="font-display text-sm tracking-[0.2em] text-accent">
                                        {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <span class="text-sm text-body">{{ $outcome }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (! empty($project->gallery))
                    <div id="project-gallery" class="mt-12 scroll-mt-28 border-t border-line pt-10">
                        <span class="eyebrow">Visuals</span>
                        <h2 class="text-2xl text-white">Project Screenshots</h2>

                        <div class="mt-6 grid gap-6 sm:grid-cols-2">
                            @foreach ($project->gallery as $image)
                                @php
                                    $imageSrc = is_string($image) && str_starts_with($image, 'http')
                                        ? $image
                                        : (is_string($image) ? Storage::url($image) : null);
                                @endphp

                                @if ($imageSrc)
                                    <div class="overflow-hidden border border-line bg-surface">
                                        <img
                                            src="{{ $imageSrc }}"
                                            alt="{{ $project->title }} screenshot"
                                            class="h-full w-full object-cover"
                                            loading="lazy"
                                        >
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <aside class="border border-line bg-surface p-8 lg:sticky lg:top-28">
                <h2 class="font-display text-sm tracking-[0.2em] text-muted uppercase">Project Specs</h2>

                <dl class="mt-6 space-y-5 text-sm">
                    @if ($project->client)
                        <div class="border-b border-line pb-4">
                            <dt class="font-display text-xs tracking-[0.24em] text-muted uppercase">Client</dt>
                            <dd class="mt-1 text-white">{{ $project->client }}</dd>
                        </div>
                    @endif

                    @if ($project->service_type)
                        <div class="border-b border-line pb-4">
                            <dt class="font-display text-xs tracking-[0.24em] text-muted uppercase">Engagement</dt>
                            <dd class="mt-1 text-white">{{ $project->service_type }}</dd>
                        </div>
                    @endif

                    @if ($project->category)
                        <div class="border-b border-line pb-4">
                            <dt class="font-display text-xs tracking-[0.24em] text-muted uppercase">Category</dt>
                            <dd class="mt-1 text-white">{{ $project->category }}</dd>
                        </div>
                    @endif

                    @if ($project->year)
                        <div class="border-b border-line pb-4">
                            <dt class="font-display text-xs tracking-[0.24em] text-muted uppercase">Year</dt>
                            <dd class="mt-1 text-white">{{ $project->year }}</dd>
                        </div>
                    @endif

                    @if (! empty($project->stack))
                        <div>
                            <dt class="font-display text-xs tracking-[0.24em] text-muted uppercase">Tech Stack</dt>
                            <dd class="mt-3 flex flex-wrap gap-2">
                                @foreach ($project->stack as $technology)
                                    <span class="tag">{{ $technology }}</span>
                                @endforeach
                            </dd>
                        </div>
                    @endif
                </dl>

                @if ($project->project_url)
                    <div class="mt-8 border-t border-line pt-6">
                        <a href="{{ $project->project_url }}" target="_blank" rel="noopener noreferrer" class="btn btn--ghost w-full justify-center">
                            Visit project
                            <span class="btn__strip" aria-hidden="true"></span>
                        </a>
                    </div>
                @endif
            </aside>
        </div>
    </section>

    @if ($others->isNotEmpty())
        <section class="section section--surface border-t border-line">
            <div class="shell">
                <x-section-head title="More Work" subtitle="Keep exploring" align="center" />

                <div class="mt-12 grid gap-8 md:grid-cols-3">
                    @foreach ($others as $other)
                        <a href="{{ route('portfolio.show', $other) }}" class="group card">
                            <x-media
                                :src="$other->cover_image ? Storage::url($other->cover_image) : null"
                                :label="$other->title"
                                ratio=""
                                aspect="666/530"
                            />
                            @if ($other->category)
                                <p class="mt-4 font-display text-xs tracking-[0.2em] text-accent uppercase">{{ $other->category }}</p>
                            @endif
                            <h5 class="card__title mt-2 !text-xl">{{ $other->title }}</h5>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
