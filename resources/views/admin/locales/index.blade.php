@extends('layouts.admin')

@section('title', 'Languages')
@section('heading', 'Languages')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <p class="max-w-2xl text-sm text-muted">
            A language lets the site be read in more than one tongue. The default language is
            what every record falls back to; the others hold translations of it.
        </p>
    </div>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <section class="admin-card !p-0">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Language</th>
                            <th>Code</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($locales as $locale)
                            <tr>
                                <td>
                                    <span class="text-white">{{ $locale->name }}</span>
                                    <span class="block text-xs text-muted">{{ $locale->native_name }}</span>
                                </td>
                                <td class="font-mono text-xs text-muted">{{ $locale->code }}</td>
                                <td>
                                    @if ($locale->is_default)
                                        <span class="badge badge--on">Default</span>
                                    @elseif ($locale->is_active)
                                        <span class="badge badge--off">Active</span>
                                    @else
                                        <span class="badge badge--off">Off</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex justify-end gap-3">
                                        @unless ($locale->is_default)
                                            <form action="{{ route('admin.locales.activate', $locale) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-xs tracking-[0.16em] text-accent uppercase">
                                                    Make default
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.locales.destroy', $locale) }}" method="POST"
                                                  onsubmit="return confirm('Delete this language and its translations?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs tracking-[0.16em] text-muted uppercase hover:text-accent">
                                                    Delete
                                                </button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-10 text-center text-muted">
                                    No languages yet — add one on the right.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card h-fit">
            <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">New language</h2>

            <form action="{{ route('admin.locales.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="field__label" for="locale-name">Name *</label>
                    <input class="input" type="text" id="locale-name" name="name" value="{{ old('name') }}"
                           placeholder="German" required>
                    @error('name') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="field__label" for="locale-code">Code *</label>
                    <input class="input font-mono text-sm" type="text" id="locale-code" name="code" value="{{ old('code') }}"
                           placeholder="de" required>
                    <p class="text-xs text-muted">A two-letter code, such as de or fr.</p>
                    @error('code') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="field__label" for="locale-native">Native name</label>
                    <input class="input" type="text" id="locale-native" name="native_name" value="{{ old('native_name') }}"
                           placeholder="Deutsch">
                    @error('native_name') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="field__label" for="locale-direction">Direction</label>
                    <select class="input" id="locale-direction" name="direction">
                        <option value="ltr" @selected(old('direction') === 'ltr')>Left to right</option>
                        <option value="rtl" @selected(old('direction') === 'rtl')>Right to left</option>
                    </select>
                </div>

                <label class="flex items-center gap-3 text-body">
                    <input type="hidden" name="is_default" value="0">
                    <input type="checkbox" name="is_default" value="1" class="h-4 w-4 accent-[#d90429]"
                           @checked(old('is_default'))>
                    Make this the default language
                </label>

                <button type="submit" class="btn btn--accent w-full">
                    Add Language
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>
            </form>
        </section>
    </div>
@endsection
