@extends('layouts.app')

@section('title', 'Marketplace')
@section('description', 'Browse software products, scripts and digital goods across every market, built and sold by Agape Infosys.')

@section('content')
    <x-shop.market-nav :markets="$markets" />

    <section class="section">
        <div class="shell">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <x-section-head
                    title="Marketplace"
                    subtitle="Software built by Agape Infosys — browse by market, pick a plan, and your account activates instantly."
                    mark="Shop"
                />

                <a href="{{ route('shop.cart.index') }}" class="tag hover:border-accent hover:text-accent">
                    Cart ({{ $cartCount }})
                </a>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($markets as $market)
                    <a href="{{ route('shop.category', $market) }}" class="group admin-card flex flex-col !p-6 transition-colors duration-300 hover:border-accent">
                        <span class="flex items-center justify-between gap-3">
                            <span class="font-display text-lg tracking-wide text-white transition-colors duration-300 group-hover:text-accent">
                                {{ $market->name }}
                            </span>

                            <svg class="text-muted transition-colors duration-300 group-hover:text-accent" width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 10h12M11 5l5 5-5 5"/>
                            </svg>
                        </span>

                        <span class="mt-3 text-sm text-muted">
                            {{ $market->children->count() }} {{ \Illuminate\Support\Str::plural('category', $market->children->count()) }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section section--surface border-t border-line">
        <div class="shell">
            <x-section-head
                title="Featured products"
                subtitle="The products ready to buy right now."
                mark="Buy"
            />

            <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
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

                                @if ($product->category)
                                    <a href="{{ route('shop.category', $product->category) }}" class="mt-1 block text-xs text-muted transition-colors duration-300 hover:text-accent">
                                        {{ $product->category->name }}
                                    </a>
                                @elseif ($product->domain)
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
                    <p class="text-muted md:col-span-3">No products are available right now. Please check back soon.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
