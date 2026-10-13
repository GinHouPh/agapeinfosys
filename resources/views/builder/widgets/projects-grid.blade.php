@php
    use App\Models\Project;

    $query = Project::query()->published()->ordered();

    if (! empty($settings['featured_only'])) {
        $query->featured();
    }

    $limit = (int) ($settings['limit'] ?? 0);
    $projects = $limit > 0 ? $query->limit($limit)->get() : $query->get();
@endphp

<x-builder.partials.section :settings="$settings">
    <div class="shell">
        <x-builder.partials.head
            :title="$settings['title'] ?? ''"
            :subtitle="$settings['subtitle'] ?? ''"
            :mark="$settings['mark'] ?? ''"
            :align="$settings['align'] ?? 'left'"
        />

        <div class="grid gap-10 md:grid-cols-2">
            @forelse ($projects as $project)
                <a href="{{ route('portfolio.show', $project) }}" class="group card">
                    <x-media
                        :src="$project->cover_image ? \Illuminate\Support\Facades\Storage::url($project->cover_image) : null"
                        :label="$project->title"
                        ratio=""
                        aspect="666/530"
                    />

                    <div class="mt-6 flex flex-wrap gap-3">
                        @if ($project->category)
                            <span class="tag">{{ $project->category }}</span>
                        @endif
                        @if ($project->year)
                            <span class="tag">{{ $project->year }}</span>
                        @endif
                    </div>

                    <h5 class="card__title mt-5 !text-2xl">{{ $project->title }}</h5>

                    @if ($project->excerpt)
                        <p class="mt-3 text-body">{{ $project->excerpt }}</p>
                    @endif
                </a>
            @empty
                <p class="text-muted md:col-span-2">Case studies are on the way — check back shortly.</p>
            @endforelse
        </div>

        @if ($projects->isNotEmpty() && ! empty($settings['show_button']) && ! empty($settings['button_label']) && ! empty($settings['button_url']))
            <div class="mt-14">
                <x-btn :href="$settings['button_url']" variant="ghost" :label="$settings['button_label']" />
            </div>
        @endif
    </div>
</x-builder.partials.section>
