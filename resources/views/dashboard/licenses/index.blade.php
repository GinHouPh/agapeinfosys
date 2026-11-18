@extends('layouts.app')

@section('title', 'Software Licenses — '.config('app.name'))

@section('content')
    @include('dashboard.partials.nav', ['title' => $title, 'lead' => $lead])

    @if ($errors->any())
        <div class="mb-6 rounded-none border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-400">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="space-y-8">
        @forelse ($licenses as $license)
            <div class="rounded-xl border border-line bg-surface/50 p-6 sm:p-8 backdrop-blur transition hover:border-line/80">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between pb-6 border-b border-line">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="text-xl font-bold text-white">{{ $license->product?->name }}</h2>
                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-mono uppercase tracking-wider border border-line bg-surface text-muted">
                                {{ $license->tier }} License
                            </span>
                            @if ($license->status === 'active')
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium border border-emerald-500/30 bg-emerald-500/10 text-emerald-400">Active</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium border border-amber-500/30 bg-amber-500/10 text-amber-400">{{ ucfirst($license->status) }}</span>
                            @endif
                        </div>
                        <p class="text-xs text-muted mt-1 font-mono">Channel: {{ $license->channel === 'envato' ? 'CodeCanyon (Envato)' : 'Agape Direct' }}</p>
                    </div>

                    @if ($license->product?->latestRelease && $license->isValid())
                        <a
                            href="{{ route('dashboard.licenses.download', $license) }}"
                            class="tag shrink-0 !border-accent/40 !bg-accent/10 !text-accent hover:!border-accent"
                            title="Download latest version package"
                        >
                            Download v{{ $license->product->latestRelease->version }} (.zip)
                        </a>
                    @endif
                </div>

                <!-- License Key Box -->
                <div class="mt-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-lg border border-line bg-ink p-4">
                    <div>
                        <span class="block text-[11px] font-semibold uppercase tracking-wider text-muted">License Key</span>
                        <code class="block font-mono text-base font-bold text-white mt-0.5 select-all">
                            {{ $license->license_key }}
                        </code>
                    </div>

                    <div class="text-xs text-muted sm:text-right">
                        <span class="block">Allowed Seats: <strong class="text-white">{{ $license->activeBindings->count() }} / {{ $license->max_domains }} domain(s)</strong></span>
                        <span class="block mt-0.5">Support until: <strong class="text-white">{{ $license->support_until ? $license->support_until->format('d M Y') : 'Active' }}</strong></span>
                    </div>
                </div>

                <!-- Bound Domains -->
                <div class="mt-6">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-muted mb-3">Active Domain Allocations</h3>

                    @if ($license->activeBindings->isNotEmpty())
                        <div class="overflow-x-auto rounded-lg border border-line">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-surface/80 text-xs uppercase text-muted border-b border-line">
                                    <tr>
                                        <th class="px-4 py-3">Domain</th>
                                        <th class="px-4 py-3">Installed Version</th>
                                        <th class="px-4 py-3">Activated Date</th>
                                        <th class="px-4 py-3 text-right">Self-Service Migration</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line font-mono text-xs">
                                    @foreach ($license->activeBindings as $binding)
                                        <tr>
                                            <td class="px-4 py-3 text-white font-medium">{{ $binding->domain }}</td>
                                            <td class="px-4 py-3 text-muted">v{{ $binding->installed_version ?: '1.0' }}</td>
                                            <td class="px-4 py-3 text-muted">{{ $binding->activated_at->format('d M Y') }}</td>
                                            <td class="px-4 py-3 text-right font-sans">
                                                <form action="{{ route('dashboard.licenses.unbind', [$license, $binding]) }}" method="POST" class="inline" onsubmit="return confirm('Unbind {{ $binding->domain }}? This frees 1 domain seat so you can activate on a new host.');">
                                                    @csrf
                                                    <button type="submit" class="tag hover:border-amber-500 hover:text-amber-400">
                                                        Release Domain
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="rounded-lg border border-dashed border-line p-4 text-center text-xs text-muted">
                            No domains bound yet. Enter this license key in your software installation wizard (<code class="text-white font-mono">/install</code>) to activate.
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-line p-12 text-center">
                <p class="text-muted text-base">You have not purchased any software licenses yet.</p>
                <p class="text-xs text-muted mt-2">If you purchased on CodeCanyon, your license key is available in your Envato Market downloads.</p>
                <div class="mt-6">
                    <a href="{{ route('shop.index') }}" class="btn btn--accent inline-flex items-center">
                        Browse Software Products
                        <span class="btn__strip" aria-hidden="true"></span>
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    @include('dashboard.partials.end')
@endsection
