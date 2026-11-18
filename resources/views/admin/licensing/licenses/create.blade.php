@extends('layouts.admin')

@section('title', 'Issue Manual Software License')
@section('heading', 'Issue Manual Software License')

@section('content')
    <div class="max-w-2xl">
        <form action="{{ route('admin.licensing.licenses.store') }}" method="POST" class="admin-card space-y-6 !p-6">
            @csrf

            <div>
                <label for="product_id" class="block text-xs font-semibold uppercase tracking-wider text-muted">Software Product</label>
                <select name="product_id" id="product_id" required class="input mt-1 w-full text-sm">
                    <option value="">Select a product…</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}" @selected(old('product_id') == $p->id)>{{ $p->name }} ({{ $p->slug }})</option>
                    @endforeach
                </select>
                @error('product_id')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="channel" class="block text-xs font-semibold uppercase tracking-wider text-muted">Channel</label>
                    <select name="channel" id="channel" required class="input mt-1 w-full text-sm">
                        <option value="agape_direct" @selected(old('channel') === 'agape_direct')>Agape Direct</option>
                        <option value="partner" @selected(old('channel') === 'partner')>Partner / Custom Deal</option>
                        <option value="envato" @selected(old('channel') === 'envato')>Envato (Marketplace)</option>
                    </select>
                    @error('channel')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tier" class="block text-xs font-semibold uppercase tracking-wider text-muted">License Tier</label>
                    <select name="tier" id="tier" required class="input mt-1 w-full text-sm">
                        <option value="regular" @selected(old('tier') === 'regular')>Regular License (1 Domain)</option>
                        <option value="extended" @selected(old('tier') === 'extended')>Extended License (3 Domains)</option>
                        <option value="agency" @selected(old('tier') === 'agency')>Agency License (5 Domains)</option>
                        <option value="enterprise" @selected(old('tier') === 'enterprise')>Enterprise License (Unlimited)</option>
                        <option value="developer" @selected(old('tier') === 'developer')>Developer License</option>
                    </select>
                    @error('tier')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="buyer_email" class="block text-xs font-semibold uppercase tracking-wider text-muted">Buyer Email Address</label>
                    <input
                        type="email"
                        name="buyer_email"
                        id="buyer_email"
                        value="{{ old('buyer_email') }}"
                        required
                        class="input mt-1 w-full text-sm"
                        placeholder="client@agency.com"
                    >
                    @error('buyer_email')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="buyer_username" class="block text-xs font-semibold uppercase tracking-wider text-muted">Buyer Username / Agency Name</label>
                    <input
                        type="text"
                        name="buyer_username"
                        id="buyer_username"
                        value="{{ old('buyer_username') }}"
                        class="input mt-1 w-full text-sm"
                        placeholder="Agency Name or Name"
                    >
                    @error('buyer_username')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label for="max_domains" class="block text-xs font-semibold uppercase tracking-wider text-muted">Max Allowed Domains</label>
                    <input
                        type="number"
                        name="max_domains"
                        id="max_domains"
                        value="{{ old('max_domains', 1) }}"
                        min="1"
                        max="1000"
                        required
                        class="input mt-1 w-full text-sm font-mono"
                    >
                    @error('max_domains')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="support_until" class="block text-xs font-semibold uppercase tracking-wider text-muted">Support Expiry Date</label>
                    <input
                        type="date"
                        name="support_until"
                        id="support_until"
                        value="{{ old('support_until', now()->addMonths(6)->format('Y-m-d')) }}"
                        class="input mt-1 w-full text-sm"
                    >
                    @error('support_until')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="expires_at" class="block text-xs font-semibold uppercase tracking-wider text-muted">License Expiry Date</label>
                    <input
                        type="date"
                        name="expires_at"
                        id="expires_at"
                        value="{{ old('expires_at') }}"
                        class="input mt-1 w-full text-sm"
                    >
                    <p class="mt-1 text-xs text-muted">Leave empty for perpetual.</p>
                    @error('expires_at')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-line pt-6">
                <a href="{{ route('admin.licensing.licenses.index') }}" class="tag hover:border-line hover:text-white">
                    Cancel
                </a>

                <button type="submit" class="btn btn--accent">
                    Generate &amp; Issue License
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>
            </div>
        </form>
    </div>
@endsection
