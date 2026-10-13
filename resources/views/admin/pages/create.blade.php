@extends('layouts.admin')

@section('title', 'New Page')
@section('heading', 'New Page')

@section('content')
    <div class="grid gap-6 xl:grid-cols-3">
        <div class="admin-card xl:col-span-2">
            <h2 class="mb-6 border-b border-line pb-4 font-display text-sm tracking-[0.24em] text-white uppercase">
                Page Details
            </h2>

            <form action="{{ route('admin.pages.store') }}" method="POST" class="space-y-6">
                @csrf

                @include('admin.pages.form')

                <div class="flex items-center gap-4 border-t border-line pt-6">
                    <button type="submit" class="btn btn--accent">
                        Create &amp; Open Builder
                        <span class="btn__strip" aria-hidden="true"></span>
                    </button>

                    <a href="{{ route('admin.pages.index') }}" class="text-xs tracking-[0.16em] text-muted uppercase hover:text-accent">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <aside class="admin-card h-fit space-y-6">
            <div>
                <span class="block font-display text-xs tracking-[0.24em] text-muted uppercase">How it works</span>
                <p class="mt-3 text-sm text-body">
                    After creating the page you land on the visual builder: pick sections from the
                    palette, edit them in the side panel and drag to reorder. Save when you are happy,
                    then flip the status to Published to make it live.
                </p>
            </div>

            <div class="border-t border-line pt-6">
                <span class="block font-display text-xs tracking-[0.24em] text-muted uppercase">Flagship slugs</span>
                <p class="mt-3 text-sm text-body">
                    Use the slug <span class="font-mono text-xs text-accent">home</span>,
                    <span class="font-mono text-xs text-accent">about</span> or
                    <span class="font-mono text-xs text-accent">services</span> to take over that
                    existing route once published.
                </p>
            </div>

            <div class="border-t border-line pt-6">
                <a href="{{ route('admin.pages.index') }}" class="font-display text-xs tracking-[0.24em] text-accent uppercase">
                    &larr; Back to pages
                </a>
            </div>
        </aside>
    </div>
@endsection
