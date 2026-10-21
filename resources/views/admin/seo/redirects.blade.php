@extends('layouts.admin')

@section('title', 'Redirects')
@section('heading', 'Redirects')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <p class="max-w-2xl text-sm text-muted">
            A visitor on an old address is sent to its replacement. Use 301 for a permanent
            move and 302 for a temporary one.
        </p>

        <a href="{{ route('admin.system.index') }}" class="tag self-start">System</a>
    </div>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <section class="admin-card !p-0">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>From</th>
                            <th>To</th>
                            <th>Code</th>
                            <th>Hits</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($redirects as $redirect)
                            <tr>
                                <td class="font-mono text-xs text-muted">{{ $redirect->from_path }}</td>
                                <td class="font-mono text-xs text-accent">{{ $redirect->to_path }}</td>
                                <td class="text-muted">{{ $redirect->status_code }}</td>
                                <td class="text-muted">{{ $redirect->hits }}</td>
                                <td>
                                    <div class="flex justify-end">
                                        <form action="{{ route('admin.seo.redirects.destroy', $redirect) }}" method="POST"
                                              onsubmit="return confirm('Delete this redirect?');">
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
                                <td colspan="5" class="py-10 text-center text-muted">
                                    No redirects yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card h-fit">
            <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">New redirect</h2>

            <form action="{{ route('admin.seo.redirects.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="field__label" for="from_path">From path *</label>
                    <input class="input font-mono text-sm" type="text" id="from_path" name="from_path"
                           value="{{ old('from_path') }}" placeholder="/old-page" required>
                    @error('from_path') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="field__label" for="to_path">To path *</label>
                    <input class="input font-mono text-sm" type="text" id="to_path" name="to_path"
                           value="{{ old('to_path') }}" placeholder="/new-page" required>
                    @error('to_path') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="field__label" for="status_code">Status code</label>
                    <select class="input" id="status_code" name="status_code">
                        @foreach ([301, 302, 307, 308] as $code)
                            <option value="{{ $code }}" @selected((string) old('status_code', '301') === (string) $code)>
                                {{ $code }}
                            </option>
                        @endforeach
                    </select>
                    @error('status_code') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <label class="flex items-center gap-3 text-body">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="h-4 w-4 accent-[#d90429]"
                           @checked(old('is_active', true))>
                    Switched on
                </label>

                <button type="submit" class="btn btn--accent w-full">
                    Create Redirect
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>
            </form>
        </section>
    </div>
@endsection
