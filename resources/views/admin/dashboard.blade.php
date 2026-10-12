@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($counts as $count)
            <a href="{{ route($count['route']) }}" class="admin-card group transition-colors duration-300 hover:border-accent">
                <span class="block font-display text-5xl text-white group-hover:text-accent">
                    {{ $count['value'] }}
                </span>
                <span class="mt-3 block font-display text-xs tracking-[0.24em] text-muted uppercase">
                    {{ $count['label'] }}
                </span>
            </a>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-3">
        <section class="admin-card xl:col-span-2">
            <div class="mb-5 flex items-center justify-between gap-4">
                <h2 class="!text-xl text-white">Unread messages</h2>
                <a href="{{ route('admin.messages.index', ['inbox' => 'unread']) }}" class="text-xs tracking-[0.2em] text-accent uppercase">
                    View all
                </a>
            </div>

            @if ($unreadMessages->isEmpty())
                <p class="text-muted">Inbox zero — no unread enquiries.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>From</th>
                                <th>Subject</th>
                                <th>Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($unreadMessages as $message)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.messages.show', $message) }}" class="text-white hover:text-accent">
                                            {{ $message->name }}
                                        </a>
                                        <span class="block text-xs text-muted">{{ $message->email }}</span>
                                    </td>
                                    <td class="text-body">{{ $message->subject ?: \Illuminate\Support\Str::limit($message->message, 60) }}</td>
                                    <td class="whitespace-nowrap text-muted">{{ $message->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="admin-card">
            <h2 class="mb-5 !text-xl text-white">Drafts</h2>

            <ul class="space-y-4">
                <li class="flex items-center justify-between border-b border-line pb-4">
                    <span class="text-body">Unpublished services</span>
                    <span class="font-display text-2xl text-white">{{ $drafts['services'] }}</span>
                </li>
                <li class="flex items-center justify-between border-b border-line pb-4">
                    <span class="text-body">Unpublished case studies</span>
                    <span class="font-display text-2xl text-white">{{ $drafts['projects'] }}</span>
                </li>
                <li class="flex items-center justify-between">
                    <span class="text-body">Unpublished articles</span>
                    <span class="font-display text-2xl text-white">{{ $drafts['posts'] }}</span>
                </li>
            </ul>

            <div class="mt-6 border-t border-line pt-6">
                <h3 class="mb-4 font-display text-xs tracking-[0.24em] text-muted uppercase">Latest articles</h3>

                <ul class="space-y-3 text-sm">
                    @forelse ($recentPosts as $post)
                        <li>
                            <a href="{{ route('admin.posts.edit', $post) }}" class="text-body hover:text-accent">
                                {{ \Illuminate\Support\Str::limit($post->title, 46) }}
                            </a>
                        </li>
                    @empty
                        <li class="text-muted">No articles yet.</li>
                    @endforelse
                </ul>
            </div>
        </section>
    </div>
@endsection
