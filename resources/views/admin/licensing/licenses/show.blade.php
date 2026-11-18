@extends('layouts.admin')

@section('title', 'Manage License: '.$license->license_key)
@section('heading', 'Manage License')

@section('content')
    @if (session('status'))
        <div class="mb-6 rounded-none border border-accent/40 bg-accent/10 px-4 py-3 text-sm text-accent">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="font-mono text-xl font-bold text-white tracking-wide">{{ $license->license_key }}</h2>
                @if ($license->status === 'active')
                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium border border-emerald-500/30 bg-emerald-500/10 text-emerald-400">Active</span>
                @elseif ($license->status === 'suspended')
                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium border border-amber-500/30 bg-amber-500/10 text-amber-400">Suspended</span>
                @elseif ($license->status === 'revoked')
                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium border border-red-500/30 bg-red-500/10 text-red-400">Revoked</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium border border-rose-500/30 bg-rose-500/10 text-rose-400">{{ ucfirst($license->status) }}</span>
                @endif
            </div>
            <p class="mt-1 text-sm text-muted">Product: <span class="text-white">{{ $license->product?->name }}</span> ({{ $license->channel }})</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <form action="{{ route('admin.licensing.licenses.status', $license) }}" method="POST" class="inline">
                @csrf
                @if ($license->status !== 'active')
                    <input type="hidden" name="status" value="active">
                    <button type="submit" class="tag !border-emerald-500/40 !bg-emerald-500/10 !text-emerald-400 hover:!border-emerald-500">
                        Activate License
                    </button>
                @endif
                @if ($license->status !== 'suspended')
                    <button type="submit" name="status" value="suspended" class="tag !border-amber-500/40 !bg-amber-500/10 !text-amber-400 hover:!border-amber-500">
                        Suspend
                    </button>
                @endif
                @if ($license->status !== 'revoked')
                    <button type="submit" name="status" value="revoked" class="tag !border-red-500/40 !bg-red-500/10 !text-red-400 hover:!border-red-500" onclick="return confirm('Revoke this license? All domain bindings will be immediately terminated.');">
                        Revoke
                    </button>
                @endif
            </form>

            <form action="{{ route('admin.licensing.licenses.reissue-salt', $license) }}" method="POST" class="inline" onsubmit="return confirm('Reissue client salt? Existing signed client tokens will require verification update.');">
                @csrf
                <button type="submit" class="tag hover:border-line hover:text-white">
                    Rotate Secret Salt
                </button>
            </form>

            <a href="{{ route('admin.licensing.licenses.index') }}" class="tag hover:border-line hover:text-white">
                Back to Licenses
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-8">
        <!-- License Meta -->
        <div class="admin-card space-y-4 !p-6 lg:col-span-2">
            <h3 class="font-display text-xs tracking-[0.2em] text-white uppercase">License &amp; Buyer Metadata</h3>

            <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="text-xs text-muted uppercase">Buyer Name</dt>
                    <dd class="mt-1 font-medium text-white">{{ $license->buyer_username ?: '—' }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-muted uppercase">Buyer Email</dt>
                    <dd class="mt-1 font-medium text-white font-mono text-xs">{{ $license->buyer_email ?: '—' }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-muted uppercase">License Tier</dt>
                    <dd class="mt-1 font-mono uppercase text-accent font-semibold">{{ $license->tier }}</dd>
                </div>

                <div>
                    <dt class="text-xs text-muted uppercase">Max Domain Allocations</dt>
                    <dd class="mt-1 font-mono text-white">{{ $license->max_domains }} domain(s)</dd>
                </div>

                <div>
                    <dt class="text-xs text-muted uppercase">Support Expiry</dt>
                    <dd class="mt-1 text-white">
                        {{ $license->support_until ? $license->support_until->format('d M Y, H:i') : 'No support period' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs text-muted uppercase">License Validity</dt>
                    <dd class="mt-1 text-white">
                        {{ $license->expires_at ? $license->expires_at->format('d M Y, H:i') : 'Perpetual (No Expiration)' }}
                    </dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-xs text-muted uppercase">Client Secret Salt (HMAC Root)</dt>
                    <dd class="mt-1 font-mono text-xs text-muted break-all bg-ink p-2 border border-line">
                        {{ $license->client_secret_salt }}
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Support Extension Tool -->
        <div class="admin-card space-y-4 !p-6">
            <h3 class="font-display text-xs tracking-[0.2em] text-white uppercase">Extend Support</h3>
            <p class="text-xs text-muted">Grant additional customer support time to this license for updates and ticket access.</p>

            <form action="{{ route('admin.licensing.licenses.extend-support', $license) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="months" class="block text-xs text-muted uppercase">Duration (Months)</label>
                    <select name="months" id="months" class="input mt-1 w-full text-sm">
                        <option value="6">+6 Months</option>
                        <option value="12" selected>+12 Months (1 Year)</option>
                        <option value="24">+24 Months (2 Years)</option>
                    </select>
                </div>

                <button type="submit" class="tag w-full justify-center hover:border-accent hover:text-accent">
                    Apply Extension
                </button>
            </form>
        </div>
    </div>

    <!-- Bound Domains Table -->
    <div class="admin-card !p-0 mb-8">
        <div class="p-5 border-b border-line flex items-center justify-between">
            <div>
                <h3 class="font-display text-xs tracking-[0.2em] text-white uppercase">Bound Client Domains</h3>
                <p class="text-xs text-muted mt-0.5">Active installations currently locked to this license key.</p>
            </div>
            <span class="font-mono text-xs text-muted">
                {{ $license->activeBindings->count() }} of {{ $license->max_domains }} domain seats used
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Domain</th>
                        <th>Client IP</th>
                        <th>Version</th>
                        <th>Status</th>
                        <th>Activated Date</th>
                        <th>Last Heartbeat</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($license->domainBindings as $binding)
                        <tr>
                            <td>
                                <span class="font-mono text-sm text-white font-medium">{{ $binding->domain }}</span>
                            </td>
                            <td class="font-mono text-xs text-muted">{{ $binding->ip_address ?: '—' }}</td>
                            <td class="font-mono text-xs text-muted">{{ $binding->installed_version ?: '—' }}</td>
                            <td>
                                @if ($binding->status === 'active')
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium border border-emerald-500/30 bg-emerald-500/10 text-emerald-400">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium border border-line bg-surface text-muted">Deactivated</span>
                                @endif
                            </td>
                            <td class="text-xs text-muted">{{ $binding->activated_at->format('d M Y, H:i') }}</td>
                            <td class="text-xs text-muted">
                                {{ $binding->last_heartbeat_at ? $binding->last_heartbeat_at->diffForHumans() : 'Never' }}
                            </td>
                            <td class="text-right">
                                @if ($binding->status === 'active')
                                    <form action="{{ route('admin.licensing.licenses.unbind', [$license, $binding]) }}" method="POST" class="inline" onsubmit="return confirm('Unbind domain [{{ $binding->domain }}]? This frees 1 seat for the buyer.');">
                                        @csrf
                                        <button type="submit" class="tag hover:border-amber-500 hover:text-amber-400">
                                            Unbind Domain
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-muted">Unbound</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-6">
                                No domains have been bound to this license yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Audit History -->
    <div class="admin-card !p-0">
        <div class="p-5 border-b border-line flex items-center justify-between">
            <div>
                <h3 class="font-display text-xs tracking-[0.2em] text-white uppercase">Recent Audit Trail</h3>
                <p class="text-xs text-muted mt-0.5">Last 20 verification, heartbeat and download events for this license.</p>
            </div>
            <a href="{{ route('admin.licensing.audit-logs.index', ['q' => $license->license_key]) }}" class="text-xs text-accent hover:underline">
                View all logs &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Event</th>
                        <th>Domain</th>
                        <th>IP Address</th>
                        <th>Status Code</th>
                        <th>User Agent</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($auditLogs as $log)
                        <tr>
                            <td class="text-xs text-muted font-mono whitespace-nowrap">
                                {{ $log->created_at->format('d M Y, H:i:s') }}
                            </td>
                            <td>
                                <span class="font-mono text-xs {{ $log->response_code === 200 ? 'text-emerald-400' : 'text-amber-400' }}">
                                    {{ $log->event }}
                                </span>
                            </td>
                            <td class="font-mono text-xs text-white">{{ $log->domain ?: '—' }}</td>
                            <td class="font-mono text-xs text-muted">{{ $log->ip_address ?: '—' }}</td>
                            <td>
                                <span class="font-mono text-xs {{ $log->response_code === 200 ? 'text-emerald-400' : 'text-red-400' }}">
                                    {{ $log->response_code }}
                                </span>
                            </td>
                            <td class="text-xs text-muted truncate max-w-xs" title="{{ $log->user_agent }}">
                                {{ $log->user_agent ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-6">
                                No audit events recorded for this license yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
