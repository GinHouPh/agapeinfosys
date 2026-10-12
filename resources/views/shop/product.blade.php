@extends('layouts.app')

@section('title', 'Shop — '.$product->name)
@section('description', 'Choose a plan for '.$product->name.' and check out securely through Agape Infosys.')

@section('content')
    <section class="border-b border-line py-16 sm:py-24">
        <div class="mx-auto max-w-[1300px] px-5 sm:px-8">
            <div class="flex flex-wrap items-start justify-between gap-6">
                <x-section-head
                    :title="'Choose your '.$product->name.' plan'"
                    :subtitle="$product->description"
                    :mark="$product->domain"
                />

                <a href="{{ route('shop.cart.index') }}" class="tag hover:border-accent hover:text-accent">
                    Cart ({{ $cartLines->count() }})
                </a>
            </div>

            <div class="mt-12 grid gap-4 md:grid-cols-3">
                @foreach ($plans as $plan)
                    @php
                        $inCart = $cartLines->contains(fn (array $line): bool => $line['plan']->getKey() === $plan->getKey());
                    @endphp

                    <article class="admin-card flex flex-col !p-8 {{ $inCart ? 'ring-1 ring-accent' : '' }}">
                        <h3 class="font-display text-2xl tracking-wide text-white">{{ $plan->name }}</h3>

                        <p class="mt-4 font-display text-4xl text-accent">{{ $plan->formattedPrice() }}</p>

                        <p class="mt-2 text-sm text-muted">
                            @if ($plan->isRecurring())
                                Billed every {{ \App\Enums\BillingType::intervalLabel($plan->interval_days ?? 30) }}
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
                            <a href="{{ route('shop.cart.index') }}" class="btn btn--ghost mt-8 w-full justify-center">
                                In cart — view cart
                                <span class="btn__strip" aria-hidden="true"></span>
                            </a>
                        @else
                            <form action="{{ route('shop.cart.store') }}" method="POST" class="mt-8">
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

                @if ($plans->isEmpty())
                    <p class="text-muted md:col-span-3">No plans are available right now. Please check back soon.</p>
                @endif
            </div>

            @error('plan')
                <p class="field__error mt-6">{{ $message }}</p>
            @enderror
        </div>
    </section>
@endsection
