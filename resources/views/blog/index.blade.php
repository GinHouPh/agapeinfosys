@extends('layouts.app')

@section('title', 'Blog — Insights on Software, Cloud & Digital Transformation | Agape Infosys')

@section('description', 'Articles from the Agape Infosys team on software engineering, cloud operations, product delivery and digital transformation.')

@section('content')
    <section class="relative overflow-hidden pt-24 pb-16 lg:pb-28">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_70%_15%,rgba(217,4,41,0.18),transparent_55%)]"
             aria-hidden="true"></div>

        <div class="shell relative">
            <span class="eyebrow">Blog</span>
            <h1 class="max-w-5xl">
                <span class="text-accent">Read</span>
                our latest articles
            </h1>

            <p class="mt-8 max-w-2xl text-lg text-body">
                Practical notes from the people doing the work — delivery, architecture,
                cloud economics and everything in between.
            </p>
        </div>

        <x-scroll-cue />
    </section>

    <section class="section section--surface">
        <div class="shell">
            @if ($categories->isNotEmpty())
                <div class="mb-12 flex flex-wrap gap-3">
                    <span class="tag border-accent text-accent">All</span>
                    @foreach ($categories as $category)
                        <span class="tag">{{ $category }}</span>
                    @endforeach
                </div>
            @endif

            @if ($posts->isEmpty())
                <p class="text-muted">No articles published yet.</p>
            @else
                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $post)
                        <article class="group card">
                            <a href="{{ route('blog.show', $post) }}">
                                <x-media
                                    :src="$post->cover_image ? Storage::url($post->cover_image) : null"
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

                {{ $posts->links('partials.pagination') }}
            @endif
        </div>
    </section>
@endsection
