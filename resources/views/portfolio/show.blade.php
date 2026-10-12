@extends('layouts.app')

@section('title', $project->title.' | Agape Infosys Case Study')

@section('description', $project->excerpt ?: $project->title.' — a case study by Agape Infosys.')

@section('content')
    <section class="relative overflow-hidden pt-40 pb-16">
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

            @if ($project->category)
                <span class="eyebrow">{{ $project->category }}</span>
            @endif

            <h1 class="max-w-4xl">{{ $project->title }}</h1>

            @if ($project->excerpt)
                <p class="mt-8 max-w-3xl text-lg text-body">{{ $project->excerpt }}</p>
            @endif
        </div>
    </section>

    <section class="section section--tight">
        <div class="shell">
            <x-media
                :src="$project->cover_image ? Storage::url($project->cover_image) : null"
                :label="$project->title"
                ratio=""
                aspect="1300/560"
                class="border border-line"
            />
        </div>
    </section>

    <section class="section section--tight pt-0">
        <div class="shell grid-2 items-start gap-16">
            <div>
                @if ($project->body)
                    <div class="prose-dark">
                        {!! $project->body !!}
                    </div>
                @endif

                @if (! empty($project->outcomes))
                    <div class="mt-10">
                        <h5 class="mb-6 text-white">Outcomes</h5>
                        <ul class="space-y-4">
                            @foreach ($project->outcomes as $outcome)
                                <li class="flex items-start gap-4 border-b border-line pb-4">
                                    <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                                    <span class="text-body">{{ $outcome }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <aside class="border border-line bg-surface p-8 lg:sticky lg:top-28">
                <dl class="space-y-6">
                    @if ($project->client)
                        <div>
                            <dt class="font-display text-xs tracking-[0.24em] text-muted uppercase">Client</dt>
                            <dd class="mt-1 text-white">{{ $project->client }}</dd>
                        </div>
                    @endif

                    @if ($project->service_type)
                        <div>
                            <dt class="font-display text-xs tracking-[0.24em] text-muted uppercase">Engagement</dt>
                            <dd class="mt-1 text-white">{{ $project->service_type }}</dd>
                        </div>
                    @endif

                    @if ($project->year)
                        <div>
                            <dt class="font-display text-xs tracking-[0.24em] text-muted uppercase">Year</dt>
                            <dd class="mt-1 text-white">{{ $project->year }}</dd>
                        </div>
                    @endif

                    @if (! empty($project->stack))
                        <div>
                            <dt class="font-display text-xs tracking-[0.24em] text-muted uppercase">Stack</dt>
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
                        <a href="{{ $project->project_url }}" target="_blank" rel="noopener noreferrer" class="font-display text-xs tracking-[0.24em] text-accent uppercase">
                            Visit project &rarr;
                        </a>
                    </div>
                @endif

                <div class="mt-8 border-t border-line pt-6">
                    <x-btn :href="route('contact.create')" variant="accent" label="Build Something Like This" />
                </div>
            </aside>
        </div>
    </section>

    @if ($others->isNotEmpty())
        <section class="section section--surface">
            <div class="shell">
                <x-section-head title="More Work" subtitle="Keep exploring" align="center" />

                <div class="grid gap-8 md:grid-cols-3">
                    @foreach ($others as $other)
                        <a href="{{ route('portfolio.show', $other) }}" class="group card">
                            <x-media
                                :src="$other->cover_image ? Storage::url($other->cover_image) : null"
                                :label="$other->title"
                                ratio=""
                                aspect="666/530"
                            />
                            <h5 class="card__title mt-5 !text-xl">{{ $other->title }}</h5>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
