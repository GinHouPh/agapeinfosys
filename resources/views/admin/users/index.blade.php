@extends('layouts.admin')

@section('title', 'Clients')
@section('heading', 'Clients')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex w-full max-w-2xl flex-wrap gap-2">
            <input
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="Search name, email or phone…"
                class="input !w-auto !border-b-0 !bg-ink !px-4 !py-2.5"
            >

            <select name="role" class="input !w-auto !border-b-0 !bg-ink !px-4 !py-2.5">
                <option value="">All accounts</option>
                <option value="clients" @selected($role === 'clients')>Clients</option>
                <option value="admins" @selected($role === 'admins')>Administrators</option>
            </select>

            <button type="submit" class="tag shrink-0 hover:border-accent hover:text-accent">Filter</button>
        </form>

        <a href="{{ route('admin.users.create') }}" class="btn btn--accent shrink-0">
            Add Client
            <span class="btn__strip" aria-hidden="true"></span>
        </a>
    </div>

    <div class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Orders</th>
                        <th>Subscriptions</th>
                        <th>Joined</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $client)
                        <tr>
                            <td>
                                <a href="{{ route('admin.users.show', $client) }}" class="text-white hover:text-accent">
                                    {{ $client->name }}
                                </a>
                                <span class="block text-xs text-muted">{{ $client->email }}</span>
                            </td>
                            <td class="text-muted">{{ $client->phone ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $client->is_admin ? 'badge--on' : 'badge--off' }}">
                                    {{ $client->is_admin ? 'Admin' : 'Client' }}
                                </span>
                            </td>
                            <td class="text-body">{{ $client->orders_count }}</td>
                            <td class="text-body">{{ $client->subscriptions_count }}</td>
                            <td class="whitespace-nowrap text-muted">{{ $client->created_at->format('d M Y') }}</td>
                            <td class="whitespace-nowrap text-right">
                                <a href="{{ route('admin.users.show', $client) }}" class="tag mr-2 hover:border-accent hover:text-accent">View</a>
                                <a href="{{ route('admin.users.edit', $client) }}" class="tag hover:border-accent hover:text-accent">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-muted">
                                {{ $search || $role ? 'No accounts match your filters.' : 'No client accounts yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $users->links('partials.pagination') }}
@endsection
