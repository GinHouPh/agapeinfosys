@extends('layouts.admin')

@section('title', 'Software Releases')
@section('heading', 'Software Releases')

@section('content')
    @if (session('status'))
        <div class="mb-6 rounded-none border border-accent/40 bg-accent/10 px-4 py-3 text-sm text-accent">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.licensing.releases.index') }}" class="flex w-full max-w-md gap-2">
            <select name="product_id" class="input !w-auto !border-b-0 !bg-ink !px-4 !py-2.5 text-sm">
                <option value="">All Software Products</option>
                @foreach ($products as $p)
                    <option value="{{ $p->id }}" @selected($productId == $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="tag shrink-0 hover:border-accent hover:text-accent">Filter</button>
        </form>

        <a href="{{ route('admin.licensing.releases.create') }}" class="btn btn--accent self-start">
            Publish New Release
            <span class="btn__strip" aria-hidden="true"></span>
        </a>
    </div>

    <div class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Version</th>
                        <th>Min PHP</th>
                        <th>File Size</th>
                        <th>SHA-256 Checksum</th>
                        <th>Status</th>
                        <th>Released Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($releases as $release)
                        <tr>
                            <td>
                                <span class="font-medium text-white">{{ $release->product?->name }}</span>
                                <span class="block text-xs text-muted font-mono">/{{ $release->product?->slug }}</span>
                            </td>
                            <td>
                                <span class="font-mono text-sm font-bold text-accent">v{{ $release->version }}</span>
                                @if ($release->is_prerelease)
                                    <span class="ml-1 inline-flex items-center px-1.5 py-0.2 text-[10px] font-mono border border-amber-500/30 bg-amber-500/10 text-amber-400">Pre-release</span>
                                @endif
                            </td>
                            <td class="font-mono text-xs text-muted">&gt;= {{ $release->min_php_version }}</td>
                            <td class="font-mono text-xs text-muted">
                                {{ $release->file_size_bytes > 0 ? number_format($release->file_size_bytes / (1024 * 1024), 2).' MB' : '—' }}
                            </td>
                            <td class="font-mono text-[11px] text-muted truncate max-w-xs" title="{{ $release->sha256_checksum }}">
                                {{ Str::limit($release->sha256_checksum, 16) }}
                            </td>
                            <td>
                                @if ($release->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium border border-emerald-500/30 bg-emerald-500/10 text-emerald-400">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium border border-red-500/30 bg-red-500/10 text-red-400">Archived</span>
                                @endif
                            </td>
                            <td class="text-xs text-muted whitespace-nowrap">{{ $release->created_at->format('d M Y') }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <form action="{{ route('admin.licensing.releases.verify-integrity', $release) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="tag hover:border-emerald-500 hover:text-emerald-400" title="Check file exists and matches SHA-256">
                                            Verify
                                        </button>
                                    </form>

                                    <a href="{{ route('admin.licensing.releases.download', $release) }}" class="tag hover:border-accent hover:text-accent" title="Download archive">
                                        Download
                                    </a>

                                    <a href="{{ route('admin.licensing.releases.edit', $release) }}" class="tag hover:border-accent hover:text-accent">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.licensing.releases.destroy', $release) }}" method="POST" onsubmit="return confirm('Delete this release package?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tag hover:border-red-500 hover:text-red-400">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-8">
                                No software releases published yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($releases->hasPages())
            <div class="border-t border-line p-4">
                {{ $releases->links() }}
            </div>
        @endif
    </div>
@endsection
