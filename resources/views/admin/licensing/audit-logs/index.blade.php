@extends('layouts.admin')

@section('title', 'Licensing Audit Trail')
@section('heading', 'Licensing Audit Trail')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ route('admin.licensing.audit-logs.index') }}" class="flex w-full max-w-4xl flex-wrap gap-2">
            <input
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="Search domain, IP address, event or key…"
                class="input !w-auto !border-b-0 !bg-ink !px-4 !py-2.5 text-sm"
            >

            <select name="event" class="input !w-auto !border-b-0 !bg-ink !px-4 !py-2.5 text-sm">
                <option value="">All Events</option>
                @foreach ($events as $e)
                    <option value="{{ $e }}" @selected($event === $e)>{{ $e }}</option>
                @endforeach
            </select>

            <select name="code" class="input !w-auto !border-b-0 !bg-ink !px-4 !py-2.5 text-sm">
                <option value="">All Status Codes</option>
                <option value="200" @selected($code === '200')>200 OK (Success)</option>
                <option value="403" @selected($code === '403')>403 Forbidden</option>
                <option value="404" @selected($code === '404')>404 Not Found</option>
                <option value="422" @selected($code === '422')>422 Unprocessable</option>
            </select>

            <button type="submit" class="tag shrink-0 hover:border-accent hover:text-accent">Filter</button>
        </form>

        <p class="text-xs text-muted self-end">Telemetry &amp; access logging for all client installations.</p>
    </div>

    <div class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Event</th>
                        <th>License Key</th>
                        <th>Product</th>
                        <th>Domain</th>
                        <th>Client IP</th>
                        <th>Code</th>
                        <th>User Agent</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-xs text-muted font-mono whitespace-nowrap">
                                {{ $log->created_at->format('d M Y, H:i:s') }}
                            </td>
                            <td>
                                @if ($log->response_code === 200)
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-mono border border-emerald-500/30 bg-emerald-500/10 text-emerald-400">
                                        {{ $log->event }}
                                    </span>
                                @elseif ($log->response_code === 403)
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-mono border border-amber-500/30 bg-amber-500/10 text-amber-400">
                                        {{ $log->event }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-mono border border-red-500/30 bg-red-500/10 text-red-400">
                                        {{ $log->event }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if ($log->license)
                                    <a href="{{ route('admin.licensing.licenses.show', $log->license) }}" class="font-mono text-xs text-white hover:text-accent">
                                        {{ $log->license->license_key }}
                                    </a>
                                @else
                                    <span class="text-xs text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-xs text-muted">
                                {{ $log->license?->product?->name ?: '—' }}
                            </td>
                            <td class="font-mono text-xs text-white">{{ $log->domain ?: '—' }}</td>
                            <td class="font-mono text-xs text-muted">{{ $log->ip_address ?: '—' }}</td>
                            <td>
                                <span class="font-mono text-xs font-semibold {{ $log->response_code === 200 ? 'text-emerald-400' : 'text-red-400' }}">
                                    {{ $log->response_code }}
                                </span>
                            </td>
                            <td class="text-xs text-muted truncate max-w-xs" title="{{ $log->user_agent }}">
                                {{ $log->user_agent ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-8">
                                No audit log records found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="border-t border-line p-4">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection
