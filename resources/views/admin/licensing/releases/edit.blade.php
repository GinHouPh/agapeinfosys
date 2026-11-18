@extends('layouts.admin')

@section('title', 'Edit Release v'.$release->version)
@section('heading', 'Edit Software Release')

@section('content')
    <div class="max-w-3xl">
        <form action="{{ route('admin.licensing.releases.update', $release) }}" method="POST" class="admin-card space-y-6 !p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-muted">Software Product</label>
                    <p class="mt-2 text-sm font-medium text-white">{{ $release->product?->name }}</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-muted">Version Tag</label>
                    <p class="mt-2 font-mono text-sm font-bold text-accent">v{{ $release->version }}</p>
                </div>
            </div>

            <div>
                <label for="min_php_version" class="block text-xs font-semibold uppercase tracking-wider text-muted">Minimum PHP Version</label>
                <input
                    type="text"
                    name="min_php_version"
                    id="min_php_version"
                    value="{{ old('min_php_version', $release->min_php_version) }}"
                    required
                    class="input mt-1 w-full font-mono text-sm"
                >
                @error('min_php_version')
                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="changelog" class="block text-xs font-semibold uppercase tracking-wider text-muted">Changelog &amp; Release Notes (Markdown)</label>
                <textarea
                    name="changelog"
                    id="changelog"
                    rows="8"
                    class="input mt-1 w-full font-mono text-xs"
                >{{ old('changelog', $release->changelog) }}</textarea>
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
                        @checked(old('is_active', $release->is_active))
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
                        @checked(old('is_prerelease', $release->is_prerelease))
                        class="rounded border-line bg-ink text-accent focus:ring-0"
                    >
                    <label for="is_prerelease" class="text-sm text-white">Pre-release (Beta / RC)</label>
                </div>
            </div>

            <div class="border-t border-line pt-4 text-xs text-muted space-y-1">
                <p>File path: <code class="text-white">{{ $release->file_path }}</code></p>
                <p>SHA-256: <code class="text-white font-mono">{{ $release->sha256_checksum }}</code></p>
                <p>File size: {{ number_format($release->file_size_bytes / (1024 * 1024), 2) }} MB</p>
            </div>

            <div class="flex items-center justify-between border-t border-line pt-6">
                <a href="{{ route('admin.licensing.releases.index') }}" class="tag hover:border-line hover:text-white">
                    Cancel
                </a>

                <button type="submit" class="btn btn--accent">
                    Update Release
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>
            </div>
        </form>
    </div>
@endsection
