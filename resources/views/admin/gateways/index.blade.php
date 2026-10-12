@extends('layouts.admin')

@section('title', 'Payment Gateways')
@section('heading', 'Payment Gateways')

@section('content')
    <p class="mb-6 max-w-2xl text-sm text-muted">
        Enable the gateways you want customers to see. Each gateway runs in test or live mode —
        add credentials for the active mode before enabling it. Setting a default makes it the
        pre-selected option at checkout.
    </p>

    <div class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Gateway</th>
                        <th>Mode</th>
                        <th>Credentials</th>
                        <th>Default</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($gateways as $gateway)
                        <tr>
                            <td>
                                <span class="text-white">{{ $gateway->name }}</span>
                                <span class="block text-xs text-muted">{{ $descriptions[$gateway->driver] ?? $gateway->driver }}</span>
                            </td>
                            <td>
                                <span class="tag">{{ $gateway->mode === 'live' ? 'Live' : 'Test' }}</span>
                            </td>
                            <td>
                                @if ($manager->make($gateway)->isConfigured())
                                    <span class="badge badge--on">Configured</span>
                                @else
                                    <span class="badge badge--off">Missing</span>
                                @endif
                            </td>
                            <td>{{ $gateway->is_default ? 'Yes' : '—' }}</td>
                            <td>
                                <span class="badge {{ $gateway->is_enabled ? 'badge--on' : 'badge--off' }}">
                                    {{ $gateway->is_enabled ? 'Enabled' : 'Disabled' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex justify-end">
                                    <a href="{{ route('admin.gateways.edit', $gateway) }}" class="text-xs tracking-[0.16em] text-accent uppercase">Configure</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
