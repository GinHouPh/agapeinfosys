@extends('layouts.app')

@section('title', 'Shop')
@section('description', 'Browse the software products from Agape Infosys and subscribe to the plan that fits.')

@section('content')
    <section class="border-b border-line py-16 sm:py-24">
        <div class="mx-auto max-w-[1300px] px-5 sm:px-8">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <x-section-head
                    title="Our products"
                    subtitle="Software built by Agape Infosys — pick a plan, check out once, and your account is activated instantly."
                    mark="Shop"
                />

                <a href="{{ route('shop.cart.index') }}" class="tag hover:border-accent hover:text-accent">
                    Cart ({{ $cartCount }})
                </a>
            </div>

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
                    <p class="text-muted md:col-span-3">No products are available right now. Please check back soon.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
