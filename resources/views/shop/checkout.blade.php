@extends('layouts.app')

@section('title', 'Checkout')
@section('description', 'Enter your account details and pay once for every plan in the cart.')

@section('content')
    <section class="py-16 sm:py-24">
        <div class="mx-auto max-w-[1300px] px-5 sm:px-8">
            <x-section-head
                title="Checkout"
                subtitle="One payment activates every plan below on the account you enter."
                mark="Secure"
            />

            <div class="mt-12 grid gap-8 lg:grid-cols-2">
                <div class="admin-card !p-6">
                    <h3 class="font-display text-sm tracking-[0.2em] text-white uppercase">Order summary</h3>

                    <dl class="mt-4 space-y-3 text-sm">
                        @foreach ($lines as $line)
                            <div class="flex justify-between gap-4 border-b border-line pb-3">
                                <dt class="text-white">
                                    {{ $line['product']->name }} — {{ $line['plan']->name }}
                                    <span class="mt-0.5 block text-xs text-muted">
                                        @if ($line['plan']->isRecurring())
                                            Billed every {{ \App\Enums\BillingType::intervalLabel($line['plan']->interval_days ?? 30) }}
                                        @else
                                            {{ $line['plan']->validity_days ?? 365 }} days of access
                                        @endif
                                    </span>
                                </dt>
                                <dd class="whitespace-nowrap text-white">{{ $line['plan']->formattedPrice() }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    <div class="mt-4 flex items-baseline justify-between gap-4">
                        <span class="text-sm text-muted">Total</span>
                        <span class="font-display text-2xl text-accent">
                            {{ \Illuminate\Support\Number::currency($subtotal / 100, $lines->first()['plan']->currency) }}
                        </span>
                    </div>

                    <a href="{{ route('shop.cart.index') }}" class="tag mt-6 inline-block hover:border-accent hover:text-accent">
                        Edit cart
                    </a>
                </div>

                <form action="{{ route('shop.checkout.store') }}" method="POST" class="admin-card space-y-6 !p-8">
                    @csrf

                    <h3 class="font-display text-xl tracking-wide text-white">Your details</h3>

                    <div class="field">
                        <label class="field__label" for="subscriber_key">Account ID on your product *</label>
                        <input
                            class="input"
                            type="text"
                            id="subscriber_key"
                            name="subscriber_key"
                            value="{{ old('subscriber_key') }}"
                            placeholder="The user ID or email of your account"
                            required
                        >
                        @error('subscriber_key') <span class="field__error">{{ $message }}</span> @enderror
                        <span class="mt-1 block text-xs text-muted">Access is granted to this account for every plan you buy.</span>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="field">
                            <label class="field__label" for="name">Full name</label>
                            <input class="input" type="text" id="name" name="name" value="{{ old('name') }}">
                            @error('name') <span class="field__error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field">
                            <label class="field__label" for="email">Email</label>
                            <input class="input" type="email" id="email" name="email" value="{{ old('email') }}">
                            @error('email') <span class="field__error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="field">
                        <label class="field__label" for="phone">Phone</label>
                        <input class="input" type="tel" id="phone" name="phone" value="{{ old('phone') }}">
                        @error('phone') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="btn btn--accent w-full justify-center">
                        Proceed to payment
                        <span class="btn__strip" aria-hidden="true"></span>
                    </button>

                    <p class="text-center text-xs text-muted">
                        Secure checkout · Card, UPI, netbanking &amp; wallets
                    </p>
                </form>
            </div>
        </div>
    </section>
@endsection
