@extends('layouts.admin')

@section('title', 'Articles')
@section('heading', 'Articles')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.posts.index') }}" class="flex w-full max-w-sm gap-2">
            <input
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="Search articles…"
                class="input !border-b-0 !bg-ink !px-4 !py-2.5"
            >
            <button type="submit" class="tag shrink-0 hover:border-accent hover:text-accent">Search</button>
        </form>

        <a href="{{ route('admin.posts.create') }}" class="btn btn--accent self-start">
            New Article
            <span class="btn__strip" aria-hidden="true"></span>
        </a>
    </div>

    <div class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>Published</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($posts as $post)
                        <tr>
                            <td>
                                <a href="{{ route('admin.posts.edit', $post) }}" class="text-white hover:text-accent">
                                    {{ $post->title }}
                                </a>
                                <span class="block text-xs text-muted">/blog/{{ $post->slug }}</span>
                            </td>
                            <td class="text-muted">{{ $post->category ?: '—' }}</td>
                            <td class="text-muted">{{ $post->author?->name ?: '—' }}</td>
                            <td class="whitespace-nowrap text-muted">
                                {{ $post->published_at?->format('d M Y') ?: '—' }}
                            </td>
                            <td>
                                <span class="badge {{ $post->is_published ? 'badge--on' : 'badge--off' }}">
                                    {{ $post->is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.posts.edit', $post) }}" class="text-xs tracking-[0.16em] text-accent uppercase">Edit</a>
                                    <a href="{{ route('admin.seo.edit', ['type' => 'post', 'id' => $post->id]) }}"
                                       class="text-xs tracking-[0.16em] text-muted uppercase hover:text-accent">SEO</a>
                                    <form action="{{ route('admin.posts.destroy', $post) }}" method="POST"
                                          onsubmit="return confirm('Delete this article?');">
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
                                {{ $search ? 'No articles match your search.' : 'No articles yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $posts->links('partials.pagination') }}
@endsection
