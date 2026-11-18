@extends('layouts.admin')

@section('title', 'Software Licenses')
@section('heading', 'Software Licenses')

@section('content')
    @if (session('status'))
        <div class="mb-6 rounded-none border border-accent/40 bg-accent/10 px-4 py-3 text-sm text-accent">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ route('admin.licensing.licenses.index') }}" class="flex w-full max-w-4xl flex-wrap gap-2">
            <input
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="Search license key, email or buyer username…"
                class="input !w-auto !border-b-0 !bg-ink !px-4 !py-2.5 text-sm"
            >

            <select name="product_id" class="input !w-auto !border-b-0 !bg-ink !px-4 !py-2.5 text-sm">
                <option value="">All Products</option>
                @foreach ($products as $p)
                    <option value="{{ $p->id }}" @selected($productId == $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>

            <select name="channel" class="input !w-auto !border-b-0 !bg-ink !px-4 !py-2.5 text-sm">
                <option value="">All Channels</option>
                <option value="envato" @selected($channel === 'envato')>Envato (CodeCanyon)</option>
                <option value="agape_direct" @selected($channel === 'agape_direct')>Agape Direct</option>
                <option value="partner" @selected($channel === 'partner')>Partner / Enterprise</option>
            </select>

            <select name="status" class="input !w-auto !border-b-0 !bg-ink !px-4 !py-2.5 text-sm">
                <option value="">All Statuses</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="suspended" @selected($status === 'suspended')>Suspended</option>
                <option value="revoked" @selected($status === 'revoked')>Revoked</option>
                <option value="refunded" @selected($status === 'refunded')>Refunded</option>
            </select>

            <button type="submit" class="tag shrink-0 hover:border-accent hover:text-accent">Filter</button>
        </form>

        <a href="{{ route('admin.licensing.licenses.create') }}" class="btn btn--accent shrink-0 self-start">
            Issue License
            <span class="btn__strip" aria-hidden="true"></span>
        </a>
    </div>

    <div class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>License Key</th>
                        <th>Product</th>
                        <th>Buyer</th>
                        <th>Tier</th>
                        <th>Channel</th>
                        <th>Domains</th>
                        <th>Status</th>
                        <th>Expires / Support</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($licenses as $license)
                        <tr>
                            <td>
                                <a href="{{ route('admin.licensing.licenses.show', $license) }}" class="font-mono text-xs text-white hover:text-accent font-semibold">
                                    {{ $license->license_key }}
                                </a>
                                <span class="block text-xs text-muted">{{ $license->created_at->format('d M Y, H:i') }}</span>
                            </td>
                            <td>
                                <span class="text-sm text-white">{{ $license->product?->name }}</span>
                                <span class="block text-xs text-muted font-mono">/{{ $license->product?->slug }}</span>
                            </td>
                            <td class="text-muted">
                                <span class="block text-white text-xs">{{ $license->buyer_username ?: '—' }}</span>
                                <span class="block text-xs text-muted">{{ $license->buyer_email ?: '—' }}</span>
                            </td>
                            <td>
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-mono uppercase tracking-wider border border-line bg-surface text-muted">
                                    {{ $license->tier }}
                                </span>
                            </td>
                            <td>
                                @if ($license->channel === 'envato')
                                    <span class="text-xs text-emerald-400 font-medium">Envato</span>
                                @elseif ($license->channel === 'partner')
                                    <span class="text-xs text-purple-400 font-medium">Partner</span>
                                @else
                                    <span class="text-xs text-blue-400 font-medium">Agape Direct</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-sm font-mono {{ $license->active_bindings_count >= $license->max_domains ? 'text-amber-400' : 'text-white' }}">
                                    {{ $license->active_bindings_count }} / {{ $license->max_domains }}
                                </span>
                            </td>
                            <td>
                                @if ($license->status === 'active')
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium border border-emerald-500/30 bg-emerald-500/10 text-emerald-400">Active</span>
                                @elseif ($license->status === 'suspended')
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium border border-amber-500/30 bg-amber-500/10 text-amber-400">Suspended</span>
                                @elseif ($license->status === 'revoked')
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium border border-red-500/30 bg-red-500/10 text-red-400">Revoked</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium border border-rose-500/30 bg-rose-500/10 text-rose-400">{{ ucfirst($license->status) }}</span>
                                @endif
                            </td>
                            <td class="text-xs text-muted">
                                @if ($license->support_until)
                                    <span class="block">Support: {{ $license->support_until->format('d M Y') }}</span>
                                @endif
                                <span>{{ $license->expires_at ? 'Exp: '.$license->expires_at->format('d M Y') : 'Perpetual' }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.licensing.licenses.show', $license) }}" class="tag hover:border-accent hover:text-accent">
                                    Manage
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-8">
                                No licenses found matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($licenses->hasPages())
            <div class="border-t border-line p-4">
                {{ $licenses->links() }}
            </div>
        @endif
    </div>
@endsection
