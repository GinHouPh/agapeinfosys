@extends('layouts.app')

@section('title', 'Projects — Ongoing & Upcoming | Agape Infosys')

@section('description', 'The products Agape Infosys is building now and proposing next — spanning OTT, appointment booking, hospital management, POS, logistics and more.')

@section('content')
    <section class="relative overflow-hidden pt-40 pb-16">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_25%_15%,rgba(217,4,41,0.18),transparent_55%)]"
             aria-hidden="true"></div>

        <div class="shell relative">
            <span class="eyebrow">Showcase</span>
            <h1 class="max-w-5xl">
                <span class="text-accent">Projects</span>
                in the making — and coming soon.
            </h1>

            <p class="mt-8 max-w-2xl text-lg text-body">
                A look at the products we are building right now and the ones we are gearing up to
                launch — across the industries we know best.
            </p>
        </div>
    </section>

    <section class="section section--surface">
        <div class="shell">
            <div class="mb-6 flex flex-wrap items-center gap-3">
                <span class="font-display text-xs tracking-[0.24em] text-muted uppercase">Stage</span>

                @foreach (['' => 'All', 'ongoing' => 'Ongoing', 'upcoming' => 'Upcoming'] as $value => $label)
                    <a
                        href="{{ route('projects.index', array_filter(['status' => $value, 'industry' => $activeIndustry])) }}"
                        class="tag {{ ($activeStatus?->value ?? '') === $value ? 'border-accent text-accent' : '' }}"
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            @if ($industries !== [])
                <div class="mb-12 flex flex-wrap items-center gap-3">
                    <span class="font-display text-xs tracking-[0.24em] text-muted uppercase">Industry</span>

                    <a
                        href="{{ route('projects.index', array_filter(['industry' => '', 'status' => $activeStatus?->value])) }}"
                        class="tag {{ $activeIndustry === '' ? 'border-accent text-accent' : '' }}"
                    >
                        All
                    </a>

                    @foreach ($industries as $industry)
                        <a
                            href="{{ route('projects.index', array_filter(['industry' => $industry, 'status' => $activeStatus?->value])) }}"
                            class="tag {{ $activeIndustry === $industry ? 'border-accent text-accent' : '' }}"
                        >
                            {{ $industry }}
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($projects->isEmpty())
                <p class="text-muted">No projects match this filter — try another stage or industry.</p>
            @else
                <div class="grid grid-cols-2 gap-x-6 gap-y-12 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach ($projects as $project)
                        <article class="group">
                            <div class="relative overflow-hidden">
                                <x-media
                                    :src="$project->posterUrl()"
                                    :label="$project->title"
                                    ratio="aspect-[2/3]"
                                />

                                <span
                                    class="absolute top-3 left-3 border px-2.5 py-1 font-display text-[10px] tracking-[0.2em] uppercase {{ $project->status === \App\Enums\ProjectStatus::Ongoing ? 'border-accent bg-accent text-white' : 'border-line bg-ink/85 text-muted' }}"
                                >
                                    {{ $project->status->label() }}
                                </span>

                                @if ($project->expected_launch)
                                    <span class="absolute right-3 bottom-3 left-3 text-[11px] tracking-[0.18em] text-white/80 uppercase">
                                        Expected {{ $project->expected_launch->format('M Y') }}
                                    </span>
                                @endif
                            </div>

                            <p class="mt-4 text-xs tracking-[0.18em] text-accent uppercase">{{ $project->industry }}</p>

                            <h3 class="mt-2 font-display text-lg tracking-wide text-white">
                                {{ $project->title }}
                            </h3>

                            @if ($project->tagline)
                                <p class="mt-2 text-sm text-body line-clamp-3">{{ $project->tagline }}</p>
                            @endif

                            @if ($project->stack !== null && $project->stack !== [])
                                <p class="mt-3 text-[11px] tracking-widest text-muted uppercase">
                                    {{ implode(' · ', $project->stack) }}
                                </p>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="section">
        <div class="shell">
            <div class="admin-card flex flex-col items-start justify-between gap-6 md:flex-row md:items-center">
                <div>
                    <h2 class="font-display text-2xl tracking-wide text-white">
                        Have a project in mind?
                    </h2>
                    <p class="mt-3 max-w-2xl text-body">
                        Share your requirement specification and we will come back with scope,
                        timelines and a quotation.
                    </p>
                </div>

                <a href="{{ route('requirements.create') }}" class="btn btn--accent shrink-0">
                    Start a Requirement Brief
                    <span class="btn__strip" aria-hidden="true"></span>
                </a>
            </div>
        </div>
    </section>
@endsection
