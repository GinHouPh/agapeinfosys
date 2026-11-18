@extends('layouts.admin')

@section('title', 'Publish New Software Release')
@section('heading', 'Publish New Software Release')

@section('content')
    <div class="max-w-3xl">
        <form action="{{ route('admin.licensing.releases.store') }}" method="POST" enctype="multipart/form-data" class="admin-card space-y-6 !p-6">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <label for="product_id" class="block text-xs font-semibold uppercase tracking-wider text-muted">Software Product</label>
                    <select name="product_id" id="product_id" required class="input mt-1 w-full text-sm">
                        <option value="">Select a product…</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}" @selected(old('product_id') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="version" class="block text-xs font-semibold uppercase tracking-wider text-muted">Version Tag</label>
                    <input
                        type="text"
                        name="version"
                        id="version"
                        value="{{ old('version') }}"
                        required
                        class="input mt-1 w-full font-mono text-sm"
                        placeholder="2.5.0"
                    >
                    @error('version')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="min_php_version" class="block text-xs font-semibold uppercase tracking-wider text-muted">Minimum PHP Version</label>
                    <input
                        type="text"
                        name="min_php_version"
                        id="min_php_version"
                        value="{{ old('min_php_version', '8.2.0') }}"
                        required
                        class="input mt-1 w-full font-mono text-sm"
                        placeholder="8.2.0"
                    >
                    @error('min_php_version')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="release_file" class="block text-xs font-semibold uppercase tracking-wider text-muted">Release Archive (.zip)</label>
                    <input
                        type="file"
                        name="release_file"
                        id="release_file"
                        accept=".zip"
                        class="input mt-1 w-full text-xs file:mr-3 file:py-1 file:px-2 file:border-0 file:text-xs file:bg-surface file:text-white"
                    >
                    <p class="mt-1 text-xs text-muted">SHA-256 and file size are calculated automatically.</p>
                    @error('release_file')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="changelog" class="block text-xs font-semibold uppercase tracking-wider text-muted">Changelog &amp; Release Notes (Markdown)</label>
                <textarea
                    name="changelog"
                    id="changelog"
                    rows="6"
                    class="input mt-1 w-full font-mono text-xs"
                    placeholder="### Version 2.6.0&#10;- Added new AI spec builder templates&#10;- Improved multi-agent MCP gateway latency"
                >{{ old('changelog') }}</textarea>
                @error('changelog')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap items-center gap-6 pt-2">
                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="is_active"
                        id="is_active"
                        value="1"
                        @checked(old('is_active', true))
                        class="rounded border-line bg-ink text-accent focus:ring-0"
                    >
                    <label for="is_active" class="text-sm text-white">Active (Eligible for auto-updates)</label>
                </div>

                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="is_prerelease"
                        id="is_prerelease"
                        value="1"
                        @checked(old('is_prerelease', false))
                        class="rounded border-line bg-ink text-accent focus:ring-0"
                    >
                    <label for="is_prerelease" class="text-sm text-white">Pre-release (Beta / RC)</label>
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-line pt-6">
                <a href="{{ route('admin.licensing.releases.index') }}" class="tag hover:border-line hover:text-white">
                    Cancel
                </a>

                <button type="submit" class="btn btn--accent">
                    Publish Release
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>
            </div>
        </form>
    </div>
@endsection
