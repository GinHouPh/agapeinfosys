@extends('layouts.app')

@section('title', $product->name)
@section('description', $product->description ?: 'Choose a plan for '.$product->name.' and check out securely through Agape Infosys.')

@section('content')
    <x-shop.market-nav :markets="$markets" />

    <section class="section">
        <div class="shell">
            <nav class="mb-8 flex flex-wrap items-center gap-2 font-display text-xs tracking-[0.2em] text-muted uppercase" aria-label="Breadcrumb">
                <a href="{{ route('shop.index') }}" class="transition-colors duration-300 hover:text-accent">Marketplace</a>

                @foreach ($product->category?->ancestors() ?? collect() as $ancestor)
                    <span aria-hidden="true">/</span>
                    <a href="{{ route('shop.category', $ancestor) }}" class="transition-colors duration-300 hover:text-accent">
                        {{ $ancestor->name }}
                    </a>
                @endforeach

                @if ($product->category)
                    <span aria-hidden="true">/</span>
                    <a href="{{ route('shop.category', $product->category) }}" class="transition-colors duration-300 hover:text-accent">
                        {{ $product->category->name }}
                    </a>
                @endif

                <span aria-hidden="true">/</span>
                <span class="text-white">{{ $product->name }}</span>
            </nav>

            <div class="grid gap-12 lg:grid-cols-[minmax(0,1fr)_360px] lg:gap-16">
                <div>
                    <div class="flex flex-wrap items-start gap-6">
                        @if ($product->imageUrl())
                            <img
                                src="{{ $product->imageUrl() }}"
                                alt="{{ $product->name }}"
                                class="h-24 w-24 shrink-0 border border-line object-cover"
                            >
                        @else
                            <span class="grid h-24 w-24 shrink-0 place-items-center border border-line font-display text-4xl text-accent" aria-hidden="true">
                                {{ strtoupper(substr($product->name, 0, 1)) }}
                            </span>
                        @endif

                        <div class="min-w-0 flex-1">
                            @if ($product->category)
                                <a href="{{ route('shop.category', $product->category) }}" class="eyebrow">
                                    {{ $product->category->name }}
                                </a>
                            @endif

                            <h1 class="text-4xl text-white sm:text-5xl">{{ $product->name }}</h1>

                            @if ($product->domain)
                                <a
                                    href="https://{{ $product->domain }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="mt-3 inline-flex items-center gap-2 text-sm text-muted transition-colors duration-300 hover:text-accent"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-accent" aria-hidden="true"></span>
                                    {{ $product->domain }}
                                </a>
                            @endif
                        </div>
                    </div>

                    @if ($product->description)
                        <div class="prose-dark mt-10 max-w-3xl">
                            <p>{{ $product->description }}</p>
                        </div>
                    @endif

                    @if ($plans->isNotEmpty())
                        <div class="mt-14">
                            <h2 class="text-2xl text-white">Choose your plan</h2>
                            <p class="mt-2 text-sm text-muted">Pick a plan below — it is added to your cart and activated after checkout.</p>

                            <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach ($plans as $plan)
                                    @php
                                        $inCart = $cartLines->contains(fn (array $line): bool => $line['plan']->getKey() === $plan->getKey());
                                    @endphp

                                    <article class="admin-card flex flex-col !p-6 {{ $inCart ? 'ring-1 ring-accent' : '' }}">
                                        <h3 class="font-display text-xl tracking-wide text-white">{{ $plan->name }}</h3>

                                        <p class="mt-3 font-display text-3xl text-accent">{{ $plan->formattedPrice() }}</p>

                                        <p class="mt-2 text-sm text-muted">
                                            @if ($plan->isRecurring())
                                                Billed every {{ \App\Enums\BillingType::intervalLabel($plan->interval_days ?? 30) }}
                                            @elseif ($plan->isLifetime())
                                                One-time — lifetime access
                                            @else
                                                One-time — {{ $plan->validity_days ?? 365 }} days of access
                                            @endif
                                        </p>

                                        @if ($plan->description)
                                            <p class="mt-4 flex-1 text-sm text-body">{{ $plan->description }}</p>
                                        @else
                                            <div class="flex-1"></div>
                                        @endif

                                        @if ($inCart)
                                            <a href="{{ route('shop.cart.index') }}" class="btn btn--ghost mt-6 w-full justify-center">
                                                In cart — view cart
                                                <span class="btn__strip" aria-hidden="true"></span>
                                            </a>
                                        @else
                                            <form action="{{ route('shop.cart.store') }}" method="POST" class="mt-6">
                                                @csrf
                                                <input type="hidden" name="product" value="{{ $product->slug }}">
                                                <input type="hidden" name="plan" value="{{ $plan->code }}">
                                                <button type="submit" class="btn btn--accent w-full justify-center">
                                                    Add to cart
                                                    <span class="btn__strip" aria-hidden="true"></span>
                                                </button>
                                            </form>
                                        @endif
                                    </article>
                                @endforeach
                            </div>

                            @error('plan')
                                <p class="field__error mt-6">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <p class="mt-12 text-muted">No plans are available for {{ $product->name }} right now. Please check back soon.</p>
                    @endif
                </div>

                <aside class="space-y-6">
                    <div class="admin-card !p-6">
                        <h2 class="font-display text-sm tracking-[0.2em] text-muted uppercase">Details</h2>

                        <dl class="mt-5 space-y-4 text-sm">
                            @if ($product->category)
                                <div class="flex items-start justify-between gap-4">
                                    <dt class="text-muted">Category</dt>
                                    <dd class="text-right text-white">
                                        <a href="{{ route('shop.category', $product->category) }}" class="transition-colors duration-300 hover:text-accent">
                                            {{ $product->category->name }}
                                        </a>
                                    </dd>
                                </div>
                            @endif

                            @if ($product->domain)
                                <div class="flex items-start justify-between gap-4">
                                    <dt class="text-muted">Website</dt>
                                    <dd class="text-right text-white">{{ $product->domain }}</dd>
                                </div>
                            @endif

                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-muted">Plans</dt>
                                <dd class="text-right text-white">{{ $plans->count() }}</dd>
                            </div>
                        </dl>

                        <a href="{{ route('shop.cart.index') }}" class="btn btn--ghost mt-6 w-full justify-center">
                            View cart ({{ $cartLines->count() }})
                            <span class="btn__strip" aria-hidden="true"></span>
                        </a>
                    </div>

                    <div class="admin-card !p-6">
                        <h2 class="font-display text-sm tracking-[0.2em] text-muted uppercase">Need a custom build?</h2>
                        <p class="mt-3 text-sm text-body">Talk to the Agape Infosys team about a tailored version of {{ $product->name }}.</p>

                        <a href="{{ route('contact.create') }}" class="btn btn--solid mt-6 w-full justify-center">
                            Let's Talk
                            <span class="btn__strip" aria-hidden="true"></span>
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="section section--surface border-t border-line">
            <div class="shell">
                <x-section-head
                    title="Related products"
                    subtitle="More from the same corner of the marketplace."
                    mark="More"
                />

                <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <article class="admin-card flex flex-col !p-6">
                            <div class="flex items-start gap-4">
                                @if ($item->imageUrl())
                                    <img src="{{ $item->imageUrl() }}" alt="{{ $item->name }}" class="h-14 w-14 shrink-0 border border-line object-cover">
                                @else
                                    <span class="grid h-14 w-14 shrink-0 place-items-center border border-line font-display text-xl text-accent" aria-hidden="true">
                                        {{ strtoupper(substr($item->name, 0, 1)) }}
                                    </span>
                                @endif

                                <h3 class="font-display text-lg tracking-wide text-white">{{ $item->name }}</h3>
                            </div>

                            @if ($item->description)
                                <p class="mt-4 flex-1 text-sm text-body">{{ \Illuminate\Support\Str::limit($item->description, 120) }}</p>
                            @else
                                <div class="flex-1"></div>
                            @endif

                            <div class="mt-6 flex items-center justify-between gap-4 border-t border-line pt-4">
                                <span class="text-sm text-muted">
                                    @if ($item->plans->isNotEmpty())
                                        From {{ $item->plans->sortBy('price')->first()->formattedPrice() }}
                                    @else
                                        Plans coming soon
                                    @endif
                                </span>

                                <a href="{{ route('shop.show', $item) }}" class="tag hover:border-accent hover:text-accent">
                                    View plans
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
