@php
    $record ??= null;
    $authoring ??= false;

    $limit = max(1, (int) ($settings['limit'] ?? 3));

    $others = collect();
    $title = '';
    $subtitle = '';
    $kind = '';

    if ($record instanceof \App\Models\Service) {
        $kind = 'service';
        $title = 'Related Services';
        $subtitle = 'Keep exploring';
        $others = \App\Models\Service::query()
            ->published()
            ->ordered()
            ->whereKeyNot($record->getKey())
            ->limit($limit)
            ->get();
    } elseif ($record instanceof \App\Models\Project) {
        $kind = 'project';
        $title = 'More Work';
        $subtitle = 'Keep exploring';
        $others = collect($record->related($limit));
    } elseif ($record instanceof \App\Models\Post) {
        $kind = 'post';
        $title = 'Keep Reading';
        $subtitle = 'More from the blog';
        $others = \App\Models\Post::query()
            ->published()
            ->ordered()
            ->whereKeyNot($record->getKey())
            ->limit($limit)
            ->get();
    }

    if (($settings['title'] ?? '') !== '') {
        $title = $settings['title'];
    }

    if (($settings['subtitle'] ?? '') !== '') {
        $subtitle = $settings['subtitle'];
    }
@endphp

@if ($record === null || $others->isEmpty())
    @if ($record === null || $authoring)
        <x-builder.partials.section :settings="$settings">
            <div class="shell">
                <div class="border border-dashed border-line p-8 text-sm text-muted">
                    @if ($record === null)
                        This block lists related records of the same type as the page&rsquo;s record,
                        excluding the record itself.
                    @else
                        {{ $settings['empty_text'] ?? 'More will be published shortly.' }}
                    @endif
                </div>
            </div>
        </x-builder.partials.section>
    @endif
@else
    <x-builder.partials.section :settings="$settings">
        <div class="shell">
            <x-builder.partials.head
                :title="$title"
                :subtitle="$subtitle"
                :align="$settings['align'] ?? 'center'"
            />

            <div class="grid gap-8 sm:grid-cols-3">
                @foreach ($others as $other)
                    @if ($kind === 'service')
                        <a href="{{ route('services.show', $other) }}"
                           class="group border border-line p-7 transition-colors duration-300 hover:border-accent">
                            <h5 class="card__title !text-xl">{{ $other->title }}</h5>
                            @if ($other->excerpt)
                                <p class="mt-3 text-sm text-muted">{{ $other->excerpt }}</p>
                            @endif
                        </a>
                    @elseif ($kind === 'post')
                        <article class="group card">
                            <a href="{{ route('blog.show', $other) }}">
                                <x-media
                                    :src="$other->cover_image ? \Illuminate\Support\Facades\Storage::url($other->cover_image) : null"
                                    :label="$other->title"
                                    ratio=""
                                    aspect="666/530"
                                />
                            </a>
                            <h5 class="card__title mt-5 !text-xl">
                                <a href="{{ route('blog.show', $other) }}">{{ $other->title }}</a>
                            </h5>
                        </article>
                    @else
                        <a href="{{ route('portfolio.show', $other) }}" class="group card">
                            <x-media
                                :src="$other->cover_image ? \Illuminate\Support\Facades\Storage::url($other->cover_image) : null"
                                :label="$other->title"
                                ratio=""
                                aspect="666/530"
                            />
                            <h5 class="card__title mt-5 !text-xl">{{ $other->title }}</h5>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </x-builder.partials.section>
@endif
