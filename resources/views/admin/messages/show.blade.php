@extends('layouts.admin')

@section('title', 'Message from '.$message->name)
@section('heading', 'Message')

@section('content')
    <div class="grid gap-6 xl:grid-cols-3">
        <div class="admin-card xl:col-span-2">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-line pb-6">
                <div>
                    <h2 class="!text-2xl text-white">{{ $message->subject ?: 'No subject' }}</h2>
                    <p class="mt-2 text-sm text-muted">
                        Received {{ $message->created_at->format('d M Y, H:i') }}
                    </p>
                </div>

                <span class="badge {{ $message->is_read ? 'badge--off' : 'badge--on' }}">
                    {{ $message->is_read ? 'Read' : 'Unread' }}
                </span>
            </div>

            <div class="space-y-4 text-body">
                @foreach (preg_split('/\R{2,}/', $message->message) ?: [] as $paragraph)
                    <p>{{ trim($paragraph) }}</p>
                @endforeach
            </div>

            <div class="mt-8 flex flex-wrap gap-3 border-t border-line pt-6">
                <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.($message->subject ?: 'Your enquiry to Agape Infosys')) }}"
                   class="btn btn--accent">
                    Reply By Email
                    <span class="btn__strip" aria-hidden="true"></span>
                </a>

                <form action="{{ route('admin.messages.destroy', $message) }}" method="POST"
                      onsubmit="return confirm('Delete this message?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn--ghost">Delete</button>
                </form>
            </div>
        </div>

        <aside class="admin-card h-fit space-y-6">
            <div>
                <span class="block font-display text-xs tracking-[0.24em] text-muted uppercase">Contact</span>
                <p class="mt-3 text-lg text-white">{{ $message->name }}</p>
                <a href="mailto:{{ $message->email }}" class="block text-body hover:text-accent">{{ $message->email }}</a>
                @if ($message->phone)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $message->phone) }}" class="block text-body hover:text-accent">
                        {{ $message->phone }}
                    </a>
                @endif
            </div>

            @if ($message->company)
                <div class="border-t border-line pt-6">
                    <span class="block font-display text-xs tracking-[0.24em] text-muted uppercase">Company</span>
                    <p class="mt-3 text-body">{{ $message->company }}</p>
                </div>
            @endif

            <div class="border-t border-line pt-6">
                <a href="{{ route('admin.messages.index') }}" class="font-display text-xs tracking-[0.24em] text-accent uppercase">
                    &larr; Back to inbox
                </a>
            </div>
        </aside>
    </div>
@endsection
