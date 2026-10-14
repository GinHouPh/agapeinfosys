@php
    use App\Builder\Widgets\ShowcaseGridWidget;
    use App\Enums\ProjectStatus;
    use App\Models\ShowcaseProject;

    $requestedStatus = request()->query('status');
    $activeStatus = is_string($requestedStatus) ? ProjectStatus::tryFrom($requestedStatus) : null;

    $industries = ShowcaseProject::industries();

    $requestedIndustry = request()->query('industry');
    $activeIndustry = is_string($requestedIndustry) && in_array($requestedIndustry, $industries, true)
        ? $requestedIndustry
        : '';

    $projects = ShowcaseProject::query()
        ->active()
        ->when($activeStatus !== null, fn ($query) => $query->where('status', $activeStatus->value))
        ->when($activeIndustry !== '', fn ($query) => $query->where('industry', $activeIndustry))
        ->ordered()
        ->get();

    $statusUrl = fn (string $value): string => route('projects.index', array_filter([
        'status' => $value,
        'industry' => $activeIndustry,
    ]));

    $industryUrl = fn (string $value): string => route('projects.index', array_filter([
        'industry' => $value,
        'status' => $activeStatus?->value,
    ]));
@endphp

<x-builder.partials.section :settings="$settings">
    <div class="shell">
        <x-builder.partials.head
            :title="$settings['title'] ?? ''"
            :subtitle="$settings['subtitle'] ?? ''"
            :mark="$settings['mark'] ?? ''"
            :align="$settings['align'] ?? 'left'"
        />

        @if ($settings['show_stage_filter'] ?? true)
            <div class="mb-6 flex flex-wrap items-center gap-3">
                <span class="font-display text-xs tracking-[0.24em] text-muted uppercase">Stage</span>

                @foreach (ShowcaseGridWidget::stages() as $value => $label)
                    <a href="{{ $statusUrl($value) }}"
                       class="tag {{ ($activeStatus?->value ?? '') === $value ? 'border-accent text-accent' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        @endif

        @if (($settings['show_industry_filter'] ?? true) && $industries !== [])
            <div class="mb-12 flex flex-wrap items-center gap-3">
                <span class="font-display text-xs tracking-[0.24em] text-muted uppercase">Industry</span>

                <a href="{{ $industryUrl('') }}"
                   class="tag {{ $activeIndustry === '' ? 'border-accent text-accent' : '' }}">
                    All
                </a>

                @foreach ($industries as $industry)
                    <a href="{{ $industryUrl($industry) }}"
                       class="tag {{ $activeIndustry === $industry ? 'border-accent text-accent' : '' }}">
                        {{ $industry }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($projects->isEmpty())
            <p class="text-muted">{{ $settings['empty_text'] ?? 'No projects match this filter — try another stage or industry.' }}</p>
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
                                class="absolute top-3 left-3 border px-2.5 py-1 font-display text-[10px] tracking-[0.2em] uppercase {{ $project->status === ProjectStatus::Ongoing ? 'border-accent bg-accent text-on-accent' : 'border-line bg-ink/85 text-muted' }}"
                            >
                                {{ $project->status->label() }}
                            </span>

                            @if ($project->expected_launch)
                                <span class="absolute right-3 bottom-3 left-3 bg-ink/85 px-2.5 py-1.5 text-[11px] tracking-[0.18em] text-white/80 uppercase">
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
</x-builder.partials.section>
