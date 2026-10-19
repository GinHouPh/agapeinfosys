@extends('layouts.admin')

@section('title', 'Configure '.$gateway->name)
@section('heading', 'Configure '.$gateway->name)

@php
    $fields = match ($gateway->driver) {
        'phonepe' => [
            'merchant_id' => 'Merchant ID',
            'salt' => 'Salt',
            'salt_index' => 'Salt Index',
        ],
        'cashfree' => [
            'client_id' => 'Client ID',
            'client_secret' => 'Client Secret',
        ],
        'payu' => [
            'merchant_key' => 'Merchant Key',
            'salt' => 'Salt',
        ],
        'paypal' => [
            'client_id' => 'Client ID',
            'client_secret' => 'Client Secret',
            'webhook_id' => 'Webhook ID',
        ],
        default => [],
    };
@endphp

@section('content')
    <p class="mb-6 max-w-2xl text-sm text-muted">{{ $description }}</p>

    <form action="{{ route('admin.gateways.update', $gateway) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                <div class="admin-card space-y-6">
                    <div class="grid gap-6 sm:grid-cols-3">
                        <div class="field">
                            <label class="field__label" for="mode">Mode *</label>
                            <select class="input" id="mode" name="mode" required>
                                <option value="test" @selected(old('mode', $gateway->mode) === 'test')>Test (sandbox)</option>
                                <option value="live" @selected(old('mode', $gateway->mode) === 'live')>Live (real money)</option>
                            </select>
                            @error('mode') <span class="field__error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field flex items-end pb-2">
                            <label class="flex items-center gap-3 text-sm text-white">
                                <input type="hidden" name="is_enabled" value="0">
                                <input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $gateway->is_enabled))>
                                Enabled at checkout
                            </label>
                        </div>

                        <div class="field flex items-end pb-2">
                            <label class="flex items-center gap-3 text-sm text-white">
                                <input type="hidden" name="is_default" value="0">
                                <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $gateway->is_default))>
                                Default gateway
                            </label>
                        </div>
                    </div>
                </div>

                @if ($fields !== [])
                    <div class="admin-card space-y-6">
                        <h3 class="font-display text-sm tracking-[0.2em] text-white uppercase">
                            Credentials — {{ old('mode', $gateway->mode) === 'live' ? 'Live' : 'Test' }} mode
                        </h3>

                        @foreach ($fields as $key => $label)
                            <div class="field">
                                <label class="field__label" for="{{ $key }}">{{ $label }}</label>
                                <input
                                    class="input"
                                    type="text"
                                    id="{{ $key }}"
                                    name="{{ $key }}"
                                    value=""
                                    placeholder="Leave blank to keep the stored value"
                                    autocomplete="off"
                                >
                                @error($key) <span class="field__error">{{ $message }}</span> @enderror
                            </div>
                        @endforeach

                        <p class="text-xs text-muted">
                            Stored encrypted. Blank fields keep the currently saved values.
                        </p>
                    </div>
                @else
                    <div class="admin-card">
                        <h3 class="font-display text-sm tracking-[0.2em] text-white uppercase">Simulator</h3>
                        <p class="mt-2 text-sm text-muted">
                            The sandbox gateway needs no credentials — it simulates successful and
                            failed payments end to end for local testing.
                        </p>
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <div class="admin-card">
                    <h3 class="font-display text-sm tracking-[0.2em] text-white uppercase">Status</h3>

                    <dl class="mt-3 space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            <dt class="w-24 text-muted">Configured</dt>
                            <dd>
                                @if ($driver->isConfigured())
                                    <span class="badge badge--on">Yes</span>
                                @else
                                    <span class="badge badge--off">No</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                @if ($fields !== [])
                    <div class="admin-card space-y-3">
                        <h3 class="font-display text-sm tracking-[0.2em] text-white uppercase">Webhook URL</h3>

                        <p class="text-xs text-muted">
                            Paste this into {{ $gateway->name }} &rarr; Developers &rarr; Webhooks and
                            select the order and subscription events. It must be the public HTTPS
                            domain serving this app (not localhost).
                        </p>

                        <div class="flex items-center gap-2">
                            <input
                                class="input flex-1 text-xs"
                                type="text"
                                id="webhook-url"
                                value="{{ route('checkout.webhook', $gateway->driver) }}"
                                readonly
                                onfocus="this.select()"
                            >
                            <button type="button" class="tag hover:border-accent hover:text-accent" id="copy-webhook">
                                Copy
                            </button>
                        </div>
                    </div>
                @endif

                <div class="flex gap-4">
                    <button type="submit" class="btn btn--accent">
                        Save gateway
                        <span class="btn__strip" aria-hidden="true"></span>
                    </button>

                    <a href="{{ route('admin.gateways.index') }}" class="tag self-center hover:border-accent hover:text-accent">Back</a>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    @if ($fields !== [])
        <script>
            (function () {
                var button = document.getElementById('copy-webhook');
                var input = document.getElementById('webhook-url');

                if (!button || !input) {
                    return;
                }

                button.addEventListener('click', function () {
                    input.select();
                    navigator.clipboard.writeText(input.value).then(function () {
                        button.textContent = 'Copied';
                        setTimeout(function () { button.textContent = 'Copy'; }, 1500);
                    });
                });
            })();
        </script>
    @endif
@endpush
