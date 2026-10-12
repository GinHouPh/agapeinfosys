@extends('layouts.app')

@section('title', 'Pay for order '.$order->reference)
@section('description', 'Complete your '.$order->plan?->name.' payment for '.$order->product?->name.'.')

@section('content')
    <section class="py-16 sm:py-24">
        <div class="mx-auto max-w-[1300px] px-5 sm:px-8">
            <div class="admin-card mx-auto max-w-lg space-y-6 !p-8">
                <div class="flex items-baseline justify-between gap-4">
                    <h1 class="font-display text-2xl tracking-wide text-white">Checkout</h1>
                    <span class="tag">{{ $order->reference }}</span>
                </div>

                <dl class="space-y-3 border-y border-line py-6 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted">Product</dt>
                        <dd class="text-white">{{ $order->product?->name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted">Plan</dt>
                        <dd class="text-white">{{ $order->plan?->name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted">Account</dt>
                        <dd class="break-all text-white">{{ $order->subscriber_key }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted">Total</dt>
                        <dd class="font-display text-lg text-accent">{{ $order->formattedAmount() }}</dd>
                    </div>
                </dl>

                @if ($form)
                    {{-- Signed form POST to a hosted payment page (e.g. PayU) --}}
                    <form action="{{ $form['url'] }}" method="POST" id="hosted-checkout">
                        @csrf
                        @foreach ($form['fields'] as $field => $value)
                            <input type="hidden" name="{{ $field }}" value="{{ $value }}">
                        @endforeach
                        <button type="submit" class="btn btn--accent w-full justify-center">
                            Continue to secure payment
                            <span class="btn__strip" aria-hidden="true"></span>
                        </button>
                    </form>
                @elseif ($order->gateway?->driver === 'sandbox')
                    {{-- Local simulator: no provider round trip --}}
                    <form
                        action="{{ route('checkout.result', [$order->product, $order]) }}"
                        method="POST"
                        class="space-y-3"
                    >
                        @csrf
                        <input type="hidden" name="token" value="{{ $payload['token'] ?? '' }}">
                        <button type="submit" name="outcome" value="success" class="btn btn--accent w-full justify-center">
                            Simulate successful payment
                            <span class="btn__strip" aria-hidden="true"></span>
                        </button>
                        <button type="submit" name="outcome" value="failure" class="btn btn--ghost w-full justify-center">
                            Simulate failed payment
                            <span class="btn__strip" aria-hidden="true"></span>
                        </button>
                    </form>
                @elseif ($payload !== [])
                    {{-- Inline provider SDK (Cashfree) --}}
                    <button type="button" id="inline-checkout" class="btn btn--accent w-full justify-center">
                        Pay {{ $order->formattedAmount() }}
                        <span class="btn__strip" aria-hidden="true"></span>
                    </button>

                    <p class="text-center text-xs text-muted">
                        You will be redirected back here once payment completes.
                    </p>
                @else
                    <p class="text-sm text-muted">
                        This checkout session has expired. Go back to
                        <a class="text-accent underline" href="{{ route('checkout.show', $order->product) }}">the plans page</a>
                        to start again.
                    </p>
                @endif
            </div>
        </div>
    </section>
@endsection

@push('head')
    @if ($script)
        <script src="{{ $script }}" crossorigin="anonymous"></script>
    @endif
@endpush

@push('scripts')
    @if ($payload !== [] && $order->gateway?->driver === 'cashfree')
        <script>
            (function () {
                var payload = @json($payload);

                // Cashfree checkout opens on the hosted payment page and
                // returns the customer to the order result URL.
                var cashfree = Cashfree({ mode: payload.mode });

                function openCheckout() {
                    cashfree.checkout({
                        paymentSessionId: payload.payment_session_id,
                        redirectTarget: '_self',
                    });
                }

                document.getElementById('inline-checkout').addEventListener('click', openCheckout);

                // Auto-open for a smoother flow.
                openCheckout();
            })();
        </script>
    @endif
@endpush
