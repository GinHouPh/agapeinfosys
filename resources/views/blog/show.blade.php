@extends('layouts.app')

@section('title', $post->title.' | Agape Infosys Blog')

@section('description', $post->excerpt ?: $post->title)

@section('content')
    @php
        $paragraphs = preg_split('/\R{2,}/', (string) $post->body) ?: [];
        $paragraphs = array_values(array_filter(array_map('trim', $paragraphs), fn (string $p) => $p !== ''));
    @endphp

    <section class="relative overflow-hidden pt-40 pb-14">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_80%_10%,rgba(217,4,41,0.16),transparent_55%)]"
             aria-hidden="true"></div>

        <div class="shell relative">
            <nav class="mb-8 flex flex-wrap items-center gap-3 font-display text-xs tracking-[0.24em] text-muted uppercase" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-accent">Home</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('blog.index') }}" class="hover:text-accent">Blog</a>
                <span aria-hidden="true">/</span>
                <span class="text-body">{{ \Illuminate\Support\Str::limit($post->title, 40) }}</span>
            </nav>

            <div class="mb-6 flex flex-wrap items-center gap-4 text-xs text-muted">
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

            <h1 class="max-w-4xl">{{ $post->title }}</h1>

            @if ($post->author)
                <p class="mt-6 text-body">
                    By <span class="text-white">{{ $post->author->name }}</span>
                </p>
            @endif
        </div>
    </section>

    @if ($post->cover_image)
        <section class="section section--tight pb-0">
            <div class="shell">
                <x-media :src="Storage::url($post->cover_image)" :label="$post->title" ratio="" aspect="1300/560" class="border border-line" :decorative="false" />
            </div>
        </section>
    @endif

    <section class="section section--tight">
        <div class="shell max-w-3xl">
            @if ($post->excerpt)
                <p class="mb-10 border-l-2 border-accent pl-6 text-lg text-white">{{ $post->excerpt }}</p>
            @endif

            <div class="prose-dark">
                @forelse ($paragraphs as $paragraph)
                    <p>{{ $paragraph }}</p>
                @empty
                    <p class="text-muted">This article body is being written.</p>
                @endforelse
            </div>

            <div class="mt-14 border-t border-line pt-8">
                <x-btn :href="route('blog.index')" variant="ghost" label="Back To Articles" />
            </div>
        </div>
    </section>

    @if ($others->isNotEmpty())
        <section class="section section--surface">
            <div class="shell">
                <x-section-head title="Keep Reading" subtitle="More from the blog" align="center" />

                <div class="grid gap-8 md:grid-cols-3">
                    @foreach ($others as $other)
                        <article class="group card">
                            <a href="{{ route('blog.show', $other) }}">
                                <x-media
                                    :src="$other->cover_image ? Storage::url($other->cover_image) : null"
                                    :label="$other->title"
                                    ratio=""
                                    aspect="666/530"
                                />
                            </a>
                            <h5 class="card__title mt-5 !text-xl">
                                <a href="{{ route('blog.show', $other) }}">{{ $other->title }}</a>
                            </h5>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
