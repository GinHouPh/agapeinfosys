@extends('layouts.app')

@section('title', 'Subscribe to '.$product->name)
@section('description', 'Choose a plan for '.$product->name.' and pay securely through Agape Infosys.')

@section('content')
    <section class="border-b border-line py-16 sm:py-24">
        <div class="mx-auto max-w-[1300px] px-5 sm:px-8">
            <x-section-head
                :title="'Choose your '.$product->name.' plan'"
                :subtitle="$product->description"
                :mark="$product->domain"
            />

            <div class="mt-12 grid gap-4 md:grid-cols-3">
                @foreach ($plans as $plan)
                    <article class="admin-card flex flex-col !p-8 {{ $preselected === $plan->code ? 'ring-1 ring-accent' : '' }}">
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

                        <button
                            type="button"
                            class="btn btn--accent mt-8 w-full justify-center"
                            data-plan="{{ $plan->code }}"
                            data-checkout-trigger
                        >
                            Subscribe
                            <span class="btn__strip" aria-hidden="true"></span>
                        </button>
                    </article>
                @endforeach

                @if ($plans->isEmpty())
                    <p class="text-muted md:col-span-3">No plans are available right now. Please check back soon.</p>
                @endif
            </div>
        </div>
    </section>

    <section class="py-16 sm:py-24">
        <div class="mx-auto max-w-[1300px] px-5 sm:px-8">
            <form
                action="{{ route('checkout.store', $product) }}"
                method="POST"
                class="admin-card mx-auto max-w-xl space-y-6 !p-8"
            >
                @csrf

                <input type="hidden" name="plan" value="{{ old('plan', $preselected) }}" data-plan-input>

                <h3 class="font-display text-xl tracking-wide text-white">Your details</h3>

                <div class="field">
                    <label class="field__label" for="subscriber_key">Account ID on {{ $product->name }} *</label>
                    <input
                        class="input"
                        type="text"
                        id="subscriber_key"
                        name="subscriber_key"
                        value="{{ old('subscriber_key', $subscriberKey) }}"
                        placeholder="The user ID or email of your account"
                        required
                    >
                    @error('subscriber_key') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="field">
                        <label class="field__label" for="name">Full name</label>
                        <input class="input" type="text" id="name" name="name" value="{{ old('name', $name) }}">
                        @error('name') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="email">Email</label>
                        <input class="input" type="email" id="email" name="email" value="{{ old('email', $email) }}">
                        @error('email') <span class="field__error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="field">
                    <label class="field__label" for="phone">Phone</label>
                    <input class="input" type="tel" id="phone" name="phone" value="{{ old('phone') }}">
                    @error('phone') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                @error('plan') <span class="field__error">{{ $message }}</span> @enderror

                <button type="submit" class="btn btn--accent w-full justify-center">
                    Proceed to payment
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>

                <p class="text-center text-xs text-muted">
                    Secure checkout · Card, UPI, netbanking &amp; wallets
                </p>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (function () {
            var planInput = document.querySelector('[data-plan-input]');

            document.querySelectorAll('[data-checkout-trigger]').forEach(function (button) {
                button.addEventListener('click', function () {
                    planInput.value = button.getAttribute('data-plan');
                    planInput.form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    var first = planInput.form.querySelector('input[name="subscriber_key"]');
                    if (first) first.focus({ preventScroll: true });
                });
            });

            var preselected = @json($preselected);
            if (preselected) {
                planInput.value = preselected;
            }
        })();
    </script>
@endpush
