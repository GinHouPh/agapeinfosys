@php
    use App\Models\Post;

    $requestedCategory = request()->query('category');
    $activeCategory = is_string($requestedCategory) && $requestedCategory !== '' ? $requestedCategory : null;

    $categories = Post::query()
        ->published()
        ->whereNotNull('category')
        ->distinct()
        ->orderBy('category')
        ->pluck('category');

    $perPage = max(1, (int) ($settings['per_page'] ?? 6));

    $posts = Post::query()
        ->published()
        ->when($activeCategory !== null, fn ($query) => $query->where('category', $activeCategory))
        ->ordered()
        ->paginate($perPage)
        ->withQueryString();
@endphp

<x-builder.partials.section :settings="$settings">
    <div class="shell">
        <x-builder.partials.head
            :title="$settings['title'] ?? ''"
            :subtitle="$settings['subtitle'] ?? ''"
            :mark="$settings['mark'] ?? ''"
            :align="$settings['align'] ?? 'left'"
        />

        @if (($settings['show_filters'] ?? true) && $categories->isNotEmpty())
            <div class="mb-12 flex flex-wrap gap-3">
                <a href="{{ route('blog.index') }}"
                   class="tag {{ $activeCategory === null ? 'border-accent text-accent' : '' }}">
                    All
                </a>

                @foreach ($categories as $category)
                    <a href="{{ route('blog.index', ['category' => $category]) }}"
                       class="tag {{ $activeCategory === $category ? 'border-accent text-accent' : '' }}">
                        {{ $category }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($posts->isEmpty())
            <p class="text-muted">{{ $settings['empty_text'] ?? 'No articles published yet.' }}</p>
        @else
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <article class="group card">
                        <a href="{{ route('blog.show', $post) }}">
                            <x-media
                                :src="$post->cover_image ? \Illuminate\Support\Facades\Storage::url($post->cover_image) : null"
                                :label="$post->title"
                                ratio=""
                                aspect="666/530"
                            />
                        </a>

                        <div class="mt-6 flex flex-wrap items-center gap-4 text-xs text-muted">
                            @if ($post->category)
                                <span class="tag">{{ $post->category }}</span>
                            @endif
                            <time datetime="{{ $post->published_at?->toDateString() }}">
                                {{ $post->published_at?->format('d M Y') }}
                            </time>
                            @if ($post->reading_time)
                                <span>{{ $post->reading_time }}</span>
                            @endif
                        </div>

                        <h4 class="card__title mt-4 !text-xl">
                            <a href="{{ route('blog.show', $post) }}">{{ $post->title }}</a>
                        </h4>

                        @if ($post->excerpt)
                            <p class="mt-3 text-sm text-body">{{ $post->excerpt }}</p>
                        @endif

                        <a href="{{ route('blog.show', $post) }}"
                           class="mt-auto inline-block pt-5 font-display text-xs tracking-[0.24em] text-accent uppercase">
                            Read More &rarr;
                        </a>
                    </article>
                @endforeach
            </div>

            @if (($settings['show_pagination'] ?? true) && $posts->hasPages())
                {{ $posts->links('partials.pagination') }}
            @endif
        @endif
    </div>
</x-builder.partials.section>
