@extends('layouts.app')

@section('title', 'Order '.$order->reference.' — '.$order->status->label())
@section('description', 'Payment '.$order->status->label().' for '.$order->plan?->name.' on '.$product->name.'.')

@section('content')
    <section class="py-16 sm:py-24">
        <div class="mx-auto max-w-[1300px] px-5 sm:px-8">
            <div class="admin-card mx-auto max-w-lg space-y-6 !p-8">
                @if ($order->status === \App\Enums\OrderStatus::Paid)
                    <div class="grid h-14 w-14 place-items-center border border-accent text-2xl text-accent" aria-hidden="true">✓</div>

                    <h1 class="font-display text-3xl tracking-wide text-white">Payment received</h1>

                    <p class="text-sm text-body">
                        Thank you! Your <strong class="text-white">{{ $order->plan?->name }}</strong> plan for
                        <strong class="text-white">{{ $product->name }}</strong> is active. You can close this
                        page and return to {{ $product->name }} — your account is updated already.
                    </p>

                    @if ($order->subscription)
                        <dl class="space-y-3 border-y border-line py-6 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-muted">Status</dt>
                                <dd class="text-white">{{ $order->subscription->status->label() }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-muted">Valid until</dt>
                                <dd class="text-white">{{ $order->subscription->ends_at?->format('d M Y') ?? 'No expiry' }}</dd>
                            </div>
                            @if ($order->subscription->renews_at)
                                <div class="flex justify-between gap-4">
                                    <dt class="text-muted">Renews on</dt>
                                    <dd class="text-white">{{ $order->subscription->renews_at->format('d M Y') }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif
                @elseif ($order->status === \App\Enums\OrderStatus::Pending)
                    <div class="grid h-14 w-14 place-items-center border border-line text-2xl text-muted" aria-hidden="true">…</div>

                    <h1 class="font-display text-3xl tracking-wide text-white">Payment processing</h1>

                    <p class="text-sm text-body">
                        We haven't received a confirmation for order <strong class="text-white">{{ $order->reference }}</strong> yet.
                        If you completed the payment it will be confirmed automatically within a few minutes.
                    </p>

                    <a href="{{ route('checkout.result', [$product, $order]) }}" class="btn btn--ghost w-full justify-center">
                        Check again
                        <span class="btn__strip" aria-hidden="true"></span>
                    </a>
                @else
                    <div class="grid h-14 w-14 place-items-center border border-line text-2xl text-muted" aria-hidden="true">!</div>

                    <h1 class="font-display text-3xl tracking-wide text-white">
                        @if ($order->status === \App\Enums\OrderStatus::Expired)
                            Checkout expired
                        @elseif ($order->status === \App\Enums\OrderStatus::Refunded)
                            Order refunded
                        @else
                            Payment failed
                        @endif
                    </h1>

                    <p class="text-sm text-body">
                        @if ($order->status === \App\Enums\OrderStatus::Expired)
                            This checkout session timed out before payment completed. No money was captured.
                        @elseif ($order->status === \App\Enums\OrderStatus::Refunded)
                            This order was refunded. Your plan will reflect the change shortly.
                        @else
                            We couldn't complete the payment for order <strong class="text-white">{{ $order->reference }}</strong>.
                            You have not been charged. Please try again.
                        @endif
                    </p>

                    @if ($order->status === \App\Enums\OrderStatus::Failed || $order->status === \App\Enums\OrderStatus::Expired)
                        <a href="{{ route('checkout.show', $product) }}" class="btn btn--accent w-full justify-center">
                            Try again
                            <span class="btn__strip" aria-hidden="true"></span>
                        </a>
                    @endif
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

                @if ($product->domain)
                    <a href="https://{{ $product->domain }}" class="btn btn--ghost w-full justify-center">
                        Return to {{ $product->name }}
                        <span class="btn__strip" aria-hidden="true"></span>
                    </a>
                @endif
            </div>
        </div>
    </section>
@endsection
