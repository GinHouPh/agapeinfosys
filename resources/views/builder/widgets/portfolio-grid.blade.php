@php
    use App\Models\Project;

    $query = Project::query()->published();

    $categories = (clone $query)
        ->whereNotNull('category')
        ->distinct()
        ->orderBy('category')
        ->pluck('category');

    $requested = request()->query('category');
    $active = is_string($requested) && $requested !== '' ? $requested : null;

    if ($active !== null) {
        $query->where('category', $active);
    }

    $projects = $query->ordered()->get();
    $showFilters = (bool) ($settings['show_filters'] ?? true);
@endphp

<x-builder.partials.section :settings="$settings">
    <div class="shell">
        <x-builder.partials.head
            :title="$settings['title'] ?? ''"
            :subtitle="$settings['subtitle'] ?? ''"
            :mark="$settings['mark'] ?? ''"
            :align="$settings['align'] ?? 'left'"
        />

        @if ($showFilters && $categories->isNotEmpty())
            <div class="mb-12 flex flex-wrap gap-3">
                <a href="{{ route('portfolio.index') }}"
                   class="tag {{ $active === null ? 'border-accent text-accent' : '' }}">
                    All
                </a>

                @foreach ($categories as $category)
                    <a href="{{ route('portfolio.index', ['category' => $category]) }}"
                       class="tag {{ $active === $category ? 'border-accent text-accent' : '' }}">
                        {{ $category }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($projects->isEmpty())
            <p class="text-muted">{{ $settings['empty_text'] ?? 'No case studies published yet.' }}</p>
        @else
            <div class="grid gap-10 md:grid-cols-2">
                @foreach ($projects as $project)
                    <a href="{{ route('portfolio.show', $project) }}" class="group card">
                        <x-media
                            :src="$project->cover_image ? \Illuminate\Support\Facades\Storage::url($project->cover_image) : null"
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
</x-builder.partials.section>
