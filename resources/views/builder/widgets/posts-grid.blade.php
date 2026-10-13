@php
    use App\Models\Post;

    $query = Post::query()->published()->ordered();

    $limit = (int) ($settings['limit'] ?? 0);
    $posts = $limit > 0 ? $query->limit($limit)->get() : $query->get();
@endphp

<x-builder.partials.section :settings="$settings">
    <div class="shell">
        <x-builder.partials.head
            :title="$settings['title'] ?? ''"
            :subtitle="$settings['subtitle'] ?? ''"
            :mark="$settings['mark'] ?? ''"
            :align="$settings['align'] ?? 'center'"
        />

        <div class="grid gap-8 md:grid-cols-3">
            @forelse ($posts as $post)
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
                        <span>
                            {{ $post->published_at?->format('d/m/Y') ?? '' }}
                        </span>
                    </div>

                    <h5 class="card__title mt-4 !text-xl">
                        <a href="{{ route('blog.show', $post) }}">{{ $post->title }}</a>
                    </h5>

                    @if ($post->excerpt)
                        <p class="mt-3 text-sm text-body">{{ $post->excerpt }}</p>
                    @endif
                </article>
            @empty
                <p class="text-muted md:col-span-3">No articles published yet.</p>
            @endforelse
        </div>
    </div>
</x-builder.partials.section>
