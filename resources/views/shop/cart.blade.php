@extends('layouts.app')

@section('title', 'Your cart')
@section('description', 'Review the plans in your cart before secure checkout.')

@section('content')
    <section class="py-16 sm:py-24">
        <div class="mx-auto max-w-[1300px] px-5 sm:px-8">
            <x-section-head
                title="Your cart"
                subtitle="One payment activates every plan in this cart."
                mark="Cart"
            />

            @if ($lines->isEmpty())
                <div class="admin-card mx-auto mt-12 max-w-xl space-y-4 !p-8 text-center">
                    <p class="text-sm text-body">Your cart is empty.</p>

                    <a href="{{ route('shop.index') }}" class="btn btn--accent inline-flex justify-center">
                        Browse products
                        <span class="btn__strip" aria-hidden="true"></span>
                    </a>
                </div>
            @else
                <div class="admin-card mt-12 !p-0">
                    <div class="overflow-x-auto">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Plan</th>
                                    <th>Billing</th>
                                    <th class="text-right">Price</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lines as $line)
                                    <tr>
                                        <td class="text-white">{{ $line['product']->name }}</td>
                                        <td class="text-muted">{{ $line['plan']->name }}</td>
                                        <td class="text-muted">
                                            @if ($line['plan']->isRecurring())
                                                Every {{ \App\Enums\BillingType::intervalLabel($line['plan']->interval_days ?? 30) }}
                                            @else
                                                {{ $line['plan']->validity_days ?? 365 }} days
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap text-right text-white">{{ $line['plan']->formattedPrice() }}</td>
                                        <td class="text-right">
                                            <form action="{{ route('shop.cart.destroy') }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="product_id" value="{{ $line['product']->getKey() }}">
                                                <input type="hidden" name="plan_id" value="{{ $line['plan']->getKey() }}">
                                                <button type="submit" class="tag hover:border-accent hover:text-accent">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-6">
                    <div class="text-sm text-muted">
                        One plan per product — each plan activates the account you enter at checkout.
                    </div>

                    <div class="flex items-center gap-6">
                        <div class="text-right">
                            <span class="block text-xs text-muted">Subtotal</span>
                            <span class="font-display text-2xl text-accent">
                                {{ \Illuminate\Support\Number::currency($subtotal / 100, $lines->first()['plan']->currency) }}
                            </span>
                        </div>

                        <a href="{{ route('shop.checkout.show') }}" class="btn btn--accent">
                            Checkout
                            <span class="btn__strip" aria-hidden="true"></span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
