@extends('layouts.app')

@section('title', $query !== '' ? 'Search — '.$query.' | Agape Infosys' : 'Search | Agape Infosys')

@section('description', 'Search the Agape Infosys site for services, case studies, articles and products.')

@section('content')
    <section class="relative overflow-hidden pt-40 pb-16">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_70%_15%,rgba(217,4,41,0.18),transparent_55%)]"
             aria-hidden="true"></div>

        <div class="shell relative">
            <span class="eyebrow">Search</span>
            <h1 class="max-w-5xl">
                <span class="text-accent">Find</span> what you are looking for
            </h1>

            <form method="GET" action="{{ route('search.index') }}" class="mt-10 max-w-2xl">
                <label for="search-input" class="field__label mb-3 block">Search the site</label>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <input
                        id="search-input"
                        type="search"
                        name="q"
                        value="{{ $query }}"
                        placeholder="Services, products, articles…"
                        class="input !text-lg"
                        autocomplete="off"
                        autofocus
                    >

                    <button type="submit" class="btn btn--accent shrink-0">
                        Search
                        <span class="btn__strip" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </section>

    <section class="section section--surface">
        <div class="shell">
            @if ($query === '')
                <p class="text-muted">Type a query above to search our services, case studies, articles and products.</p>
            @elseif ($total === 0)
                <p class="text-muted">
                    No results found for <span class="text-white">&ldquo;{{ $query }}&rdquo;</span>.
                    Try a different keyword, or <a href="{{ route('contact.create') }}" class="text-accent hover:underline">talk to us</a> about what you need.
                </p>
            @else
                <p class="mb-12 text-muted">
                    {{ $total }} {{ str()->plural('result', $total) }} for <span class="text-white">&ldquo;{{ $query }}&rdquo;</span>.
                </p>

                @if ($results['services']->isNotEmpty())
                    <div class="mb-14">
                        <h2 class="mb-6 !text-2xl">Services</h2>

                        <div class="space-y-6">
                            @foreach ($results['services'] as $service)
                                <article class="border-b border-line pb-6">
                                    <h4 class="!text-xl">
                                        <a href="{{ route('services.show', $service) }}" class="hover:text-accent">{{ $service->title }}</a>
                                    </h4>

                                    @if ($service->summary || $service->excerpt)
                                        <p class="mt-2 text-body">{{ \Illuminate\Support\Str::limit($service->summary ?: $service->excerpt, 180) }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($results['projects']->isNotEmpty())
                    <div class="mb-14">
                        <h2 class="mb-6 !text-2xl">Case Studies</h2>

                        <div class="space-y-6">
                            @foreach ($results['projects'] as $project)
                                <article class="border-b border-line pb-6">
                                    <div class="flex flex-wrap items-center gap-4">
                                        @if ($project->category)
                                            <span class="tag">{{ $project->category }}</span>
                                        @endif
                                        @if ($project->client)
                                            <span class="text-xs text-muted">{{ $project->client }}</span>
                                        @endif
                                    </div>

                                    <h4 class="mt-3 !text-xl">
                                        <a href="{{ route('portfolio.show', $project) }}" class="hover:text-accent">{{ $project->title }}</a>
                                    </h4>

                                    @if ($project->excerpt)
                                        <p class="mt-2 text-body">{{ \Illuminate\Support\Str::limit($project->excerpt, 180) }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($results['posts']->isNotEmpty())
                    <div class="mb-14">
                        <h2 class="mb-6 !text-2xl">Articles</h2>

                        <div class="space-y-6">
                            @foreach ($results['posts'] as $post)
                                <article class="border-b border-line pb-6">
                                    <div class="flex flex-wrap items-center gap-4 text-xs text-muted">
                                        @if ($post->category)
                                            <span class="tag">{{ $post->category }}</span>
                                        @endif
                                        <time datetime="{{ $post->published_at?->toDateString() }}">
                                            {{ $post->published_at?->format('d M Y') }}
                                        </time>
                                    </div>

                                    <h4 class="mt-3 !text-xl">
                                        <a href="{{ route('blog.show', $post) }}" class="hover:text-accent">{{ $post->title }}</a>
                                    </h4>

                                    @if ($post->excerpt)
                                        <p class="mt-2 text-body">{{ \Illuminate\Support\Str::limit($post->excerpt, 180) }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($results['products']->isNotEmpty())
                    <div>
                        <h2 class="mb-6 !text-2xl">Products</h2>

                        <div class="space-y-6">
                            @foreach ($results['products'] as $product)
                                <article class="border-b border-line pb-6">
                                    <h4 class="!text-xl">
                                        <a href="{{ route('shop.show', $product) }}" class="hover:text-accent">{{ $product->name }}</a>
                                    </h4>

                                    @if ($product->description)
                                        <p class="mt-2 text-body">{{ \Illuminate\Support\Str::limit($product->description, 180) }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </section>
@endsection
