@extends('layouts.admin')

@section('title', 'Messages')
@section('heading', 'Messages')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.messages.index') }}"
               class="tag {{ $inbox === 'all' ? 'border-accent text-accent' : '' }}">
                All
            </a>
            <a href="{{ route('admin.messages.index', ['inbox' => 'unread']) }}"
               class="tag {{ $inbox === 'unread' ? 'border-accent text-accent' : '' }}">
                Unread ({{ $unreadCount }})
            </a>
        </div>

        <p class="text-sm text-muted">Enquiries submitted through the contact form.</p>
    </div>

    <div class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>From</th>
                        <th>Company</th>
                        <th>Message</th>
                        <th>Received</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($messages as $message)
                        <tr>
                            <td>
                                <a href="{{ route('admin.messages.show', $message) }}" class="text-white hover:text-accent">
                                    {{ $message->name }}
                                </a>
                                <span class="block text-xs text-muted">{{ $message->email }}</span>
                                @if ($message->phone)
                                    <span class="block text-xs text-muted">{{ $message->phone }}</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $message->company ?: '—' }}</td>
                            <td class="max-w-md text-body">
                                {{ \Illuminate\Support\Str::limit($message->message, 110) }}
                                @if ($message->subject)
                                    <span class="block text-xs text-muted">{{ $message->subject }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-muted">{{ $message->created_at->format('d M Y, H:i') }}</td>
                            <td>
                                <span class="badge {{ $message->is_read ? 'badge--off' : 'badge--on' }}">
                                    {{ $message->is_read ? 'Read' : 'Unread' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.messages.show', $message) }}" class="text-xs tracking-[0.16em] text-accent uppercase">Open</a>
                                    <form action="{{ route('admin.messages.destroy', $message) }}" method="POST"
                                          onsubmit="return confirm('Delete this message?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs tracking-[0.16em] text-muted uppercase hover:text-accent">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-muted">
                                {{ $inbox === 'unread' ? 'No unread messages.' : 'No messages yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $messages->links('partials.pagination') }}
@endsection
