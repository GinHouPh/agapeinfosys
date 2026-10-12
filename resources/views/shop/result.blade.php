@extends('layouts.app')

@section('title', 'Cart order '.$order->reference.' — '.$order->status->label())
@section('description', 'Payment '.$order->status->label().' for your Agape Infosys cart order.')

@section('content')
    <section class="py-16 sm:py-24">
        <div class="mx-auto max-w-[1300px] px-5 sm:px-8">
            <div class="admin-card mx-auto max-w-lg space-y-6 !p-8">
                @if ($order->status === \App\Enums\OrderStatus::Paid)
                    <div class="grid h-14 w-14 place-items-center border border-accent text-2xl text-accent" aria-hidden="true">✓</div>

                    <h1 class="font-display text-3xl tracking-wide text-white">Payment received</h1>

                    <p class="text-sm text-body">
                        Thank you! Every plan below is active on
                        <strong class="text-white">{{ $order->subscriber_key }}</strong>. You can close this page —
                        your accounts are updated already.
                    </p>

                    <dl class="space-y-3 border-y border-line py-6 text-sm">
                        @foreach ($order->items as $item)
                            @php
                                $subscription = $subscriptions->get($item->product_id);
                            @endphp

                            <div class="flex justify-between gap-4">
                                <dt class="text-muted">
                                    {{ $item->product_name }} — {{ $item->plan_name }}
                                </dt>
                                <dd class="whitespace-nowrap text-white">
                                    @if ($subscription)
                                        Valid until {{ $subscription->ends_at?->format('d M Y') ?? 'No expiry' }}
                                    @else
                                        Active
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                @elseif ($order->status === \App\Enums\OrderStatus::Pending)
                    <div class="grid h-14 w-14 place-items-center border border-line text-2xl text-muted" aria-hidden="true">…</div>

                    <h1 class="font-display text-3xl tracking-wide text-white">Payment processing</h1>

                    <p class="text-sm text-body">
                        We haven't received a confirmation for order <strong class="text-white">{{ $order->reference }}</strong> yet.
                        If you completed the payment it will be confirmed automatically within a few minutes.
                    </p>

                    <a href="{{ route('shop.result', $order) }}" class="btn btn--ghost w-full justify-center">
                        Check again
                        <span class="btn__strip" aria-hidden="true"></span>
                    </a>
                @else
                    <div class="grid h-14 w-14 place-items-center border border-line text-2xl text-muted" aria-hidden="true">!</div>

                    <h1 class="font-display text-3xl tracking-wide text-white">
                        @if ($order->status === \App\Enums\OrderStatus::Expired)
                            Checkout expired
                        @else
                            Payment failed
                        @endif
                    </h1>

                    <p class="text-sm text-body">
                        @if ($order->status === \App\Enums\OrderStatus::Expired)
                            This checkout session timed out before payment completed. No money was captured.
                        @else
                            We couldn't complete the payment for order <strong class="text-white">{{ $order->reference }}</strong>.
                            You have not been charged. Please try again.
                        @endif
                    </p>

                    <a href="{{ route('shop.index') }}" class="btn btn--accent w-full justify-center">
                        Back to the shop
                        <span class="btn__strip" aria-hidden="true"></span>
                    </a>
                @endif

                <dl class="space-y-3 pt-2 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted">Order</dt>
                        <dd class="text-white">{{ $order->reference }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted">Amount</dt>
                        <dd class="text-white">{{ $order->formattedAmount() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted">Account</dt>
                        <dd class="break-all text-white">{{ $order->subscriber_key }}</dd>
                    </div>
                </dl>

                <a href="{{ route('shop.index') }}" class="btn btn--ghost w-full justify-center">
                    Continue shopping
                    <span class="btn__strip" aria-hidden="true"></span>
                </a>
            </div>
        </div>
    </section>
@endsection
