@extends('layouts.app')

@section('title', $category->name)
@section('description', $category->description ?: 'Browse '.$category->name.' on the Agape Infosys marketplace.')

@section('content')
    <x-shop.market-nav :markets="$markets" />

    <section class="section">
        <div class="shell">
            <nav class="mb-8 flex flex-wrap items-center gap-2 font-display text-xs tracking-[0.2em] text-muted uppercase" aria-label="Breadcrumb">
                <a href="{{ route('shop.index') }}" class="transition-colors duration-300 hover:text-accent">Marketplace</a>

                @foreach ($category->ancestors() as $ancestor)
                    <span aria-hidden="true">/</span>
                    <a href="{{ route('shop.category', $ancestor) }}" class="transition-colors duration-300 hover:text-accent">
                        {{ $ancestor->name }}
                    </a>
                @endforeach

                <span aria-hidden="true">/</span>
                <span class="text-white">{{ $category->name }}</span>
            </nav>

            <div class="flex flex-wrap items-end justify-between gap-6">
                <x-section-head
                    :title="$category->name"
                    :subtitle="$category->description"
                    mark="Category"
                />

                <a href="{{ route('shop.cart.index') }}" class="tag hover:border-accent hover:text-accent">
                    Cart ({{ $cartCount }})
                </a>
            </div>

            @if ($subcategories->isNotEmpty())
                <div class="mb-12 flex flex-wrap gap-2">
                    @foreach ($subcategories as $subcategory)
                        <a href="{{ route('shop.category', $subcategory) }}" class="tag hover:border-accent hover:text-accent">
                            {{ $subcategory->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($products as $product)
                    <article class="admin-card flex flex-col !p-6">
                        <div class="flex items-start gap-4">
                            @if ($product->imageUrl())
                                <img
                                    src="{{ $product->imageUrl() }}"
                                    alt="{{ $product->name }}"
                                    class="h-16 w-16 shrink-0 border border-line object-cover"
                                >
                            @else
                                <span class="grid h-16 w-16 shrink-0 place-items-center border border-line font-display text-2xl text-accent" aria-hidden="true">
                                    {{ strtoupper(substr($product->name, 0, 1)) }}
                                </span>
                            @endif

                            <div class="min-w-0">
                                <h2 class="font-display text-xl tracking-wide text-white">{{ $product->name }}</h2>

                                @if ($product->domain)
                                    <span class="mt-1 block text-xs text-muted">{{ $product->domain }}</span>
                                @endif
                            </div>
                        </div>

                        @if ($product->description)
                            <p class="mt-4 flex-1 text-sm text-body">{{ \Illuminate\Support\Str::limit($product->description, 140) }}</p>
                        @else
                            <div class="flex-1"></div>
                        @endif

                        <div class="mt-6 flex items-center justify-between gap-4 border-t border-line pt-4">
                            <span class="text-sm text-muted">
                                @if ($product->plans->isNotEmpty())
                                    From {{ $product->plans->sortBy('price')->first()->formattedPrice() }}
                                @else
                                    Plans coming soon
                                @endif
                            </span>

                            <a href="{{ route('shop.show', $product) }}" class="tag hover:border-accent hover:text-accent">
                                View plans
                            </a>
                        </div>
                    </article>
                @empty
                    <p class="text-muted md:col-span-3">
                        Nothing is listed in {{ $category->name }} yet. Explore another market or check back soon.
                    </p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
