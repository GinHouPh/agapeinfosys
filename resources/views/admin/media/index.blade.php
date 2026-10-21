@extends('layouts.admin')

@section('title', 'Media')
@section('heading', 'Media Library')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" action="{{ route('admin.media.index') }}" class="flex w-full max-w-2xl flex-wrap gap-2">
            <input
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="Search by name, title or alt text…"
                class="input !border-b-0 !bg-ink !px-4 !py-2.5"
            >

            <select name="kind" class="input !border-b-0 !bg-ink !px-4 !py-2.5">
                <option value="">Everything</option>
                <option value="images" @selected($kind === 'images')>Images</option>
                <option value="documents" @selected($kind === 'documents')>Documents</option>
            </select>

            <button type="submit" class="tag shrink-0 hover:border-accent hover:text-accent">Filter</button>
        </form>

        <form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data"
              class="flex items-center gap-2">
            @csrf
            <input class="input !bg-ink !px-4 !py-2.5" type="file" name="file" required>
            <button type="submit" class="tag shrink-0 hover:border-accent hover:text-accent">Upload</button>
        </form>
    </div>

    @if ($items->isEmpty())
        <div class="admin-card">
            <p class="py-10 text-center text-muted">
                The library is empty. Upload a file, or add an image from the page builder.
            </p>
        </div>
    @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($items as $item)
                <div class="admin-card !p-0">
                    <div class="aspect-video overflow-hidden border-b border-line bg-ink">
                        @if ($item->isImage())
                            <img src="{{ $item->url() }}" alt="{{ $item->alt_text ?? $item->original_name }}"
                                 class="h-full w-full object-cover">
                        @else
                            <div class="grid h-full place-items-center text-muted">
                                <span class="font-display text-xs tracking-[0.24em] uppercase">{{ $item->mime_type }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="space-y-3 p-4">
                        <p class="truncate text-sm text-white" title="{{ $item->original_name }}">
                            {{ $item->original_name }}
                        </p>
                        <p class="text-xs text-muted">
                            {{ $item->readableSize() }}
                            @if ($item->width)
                                · {{ $item->width }}×{{ $item->height }}
                            @endif
                        </p>

                        <form action="{{ route('admin.media.update', $item) }}" method="POST" class="space-y-3">
                            @csrf
                            @method('PUT')

                            <input class="input !py-2 text-sm" type="text" name="alt_text"
                                   value="{{ old('alt_text', $item->alt_text) }}" placeholder="Alt text">

                            <div class="grid grid-cols-2 gap-2">
                                <input class="input !py-2 text-sm" type="number" name="focal_x" min="0" max="100"
                                       value="{{ old('focal_x', $item->focal_x) }}" placeholder="Focus X %">
                                <input class="input !py-2 text-sm" type="number" name="focal_y" min="0" max="100"
                                       value="{{ old('focal_y', $item->focal_y) }}" placeholder="Focus Y %">
                            </div>

                            <button type="submit" class="tag w-full hover:border-accent hover:text-accent">Save</button>
                        </form>

                        <form action="{{ route('admin.media.destroy', $item) }}" method="POST"
                              onsubmit="return confirm('Delete this file? It will be removed from disk.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full text-xs tracking-[0.16em] text-muted uppercase hover:text-accent">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        {{ $items->links('partials.pagination') }}
    @endif
@endsection
