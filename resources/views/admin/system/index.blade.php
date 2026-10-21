@extends('layouts.admin')

@section('title', 'System')
@section('heading', 'Platform Administration')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <p class="max-w-2xl text-sm text-muted">
            The state of the platform: what it depends on, what is switched on, and how to
            take the public site offline for a moment.
        </p>

        <a href="{{ route('admin.system.flags') }}" class="tag self-start">Feature flags</a>
    </div>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <section class="admin-card !p-0">
            <div class="border-b border-line p-5">
                <h2 class="font-display text-lg tracking-[0.12em] text-white uppercase">Health</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Check</th>
                            <th>Value</th>
                            <th class="text-right">State</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report as $key => $check)
                            <tr>
                                <td>
                                    <span class="text-body">{{ $check['label'] }}</span>
                                    @if (! empty($check['detail']))
                                        <span class="block text-xs text-accent">{{ $check['detail'] }}</span>
                                    @endif
                                </td>
                                <td class="font-mono text-xs text-muted">{{ $check['value'] }}</td>
                                <td class="text-right">
                                    <span class="badge {{ $check['ok'] ? 'badge--on' : 'badge--off' }}">
                                        {{ $check['ok'] ? 'OK' : 'Check' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div class="space-y-8">
            <section class="admin-card">
                <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Maintenance mode</h2>

                <form action="{{ route('admin.system.maintenance') }}" method="POST" class="space-y-4">
                    @csrf

                    <label class="flex items-center gap-3 text-body">
                        <input type="hidden" name="enabled" value="0">
                        <input type="checkbox" name="enabled" value="1" class="h-4 w-4 accent-[#d90429]"
                               @checked(old('enabled', $maintenance['enabled'] ?? false))>
                        Take the public site offline
                    </label>

                    <div>
                        <label class="field__label" for="maintenance-message">Notice</label>
                        <textarea class="input" id="maintenance-message" name="message" rows="3"
                                  placeholder="We are carrying out scheduled work.">{{ old('message', $maintenance['message'] ?? '') }}</textarea>
                        @error('message') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <p class="text-xs text-muted">
                        Visitors see the notice; admins still get through.
                    </p>

                    <button type="submit" class="btn btn--accent w-full">
                        Save
                        <span class="btn__strip" aria-hidden="true"></span>
                    </button>
                </form>
            </section>

            <section class="admin-card">
                <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Feature flags</h2>

                @if ($flags->isEmpty())
                    <p class="text-sm text-muted">
                        No flags yet. A flag that does not exist is simply off.
                    </p>
                @else
                    <ul class="space-y-3">
                        @foreach ($flags as $flag)
                            <li class="flex items-center justify-between gap-3 border border-line px-3 py-2">
                                <span class="text-sm text-body">{{ $flag->name }}</span>
                                <span class="badge {{ $flag->is_enabled ? 'badge--on' : 'badge--off' }}">
                                    {{ $flag->is_enabled ? 'On' : 'Off' }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <a href="{{ route('admin.system.flags') }}"
                   class="mt-4 inline-block text-xs tracking-[0.16em] text-accent uppercase">
                    Manage flags
                </a>
            </section>
        </div>
    </div>
@endsection
