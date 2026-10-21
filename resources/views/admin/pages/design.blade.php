@extends('layouts.admin')

@section('title', 'Page colours')
@section('heading', 'Page Colours')

@section('content')
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.pages.edit', $page) }}" class="tag hover:border-accent hover:text-accent">Back to the builder</a>
        <a href="{{ route('admin.pages.index') }}" class="tag hover:border-accent hover:text-accent">All pages</a>
    </div>

    <div class="mb-6 max-w-2xl text-sm text-muted">
        These colours apply to this page only. Anything left blank follows the
        <span class="text-white">design theme</span>, so the rest of the site is untouched.
    </div>

    <form action="{{ route('admin.pages.design.update', $page) }}" method="POST" class="grid gap-8 lg:grid-cols-2">
        @csrf
        @method('PUT')

        @foreach (['dark' => 'Dark scheme', 'light' => 'Light scheme'] as $scheme => $label)
            <section class="admin-card">
                <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">{{ $label }}</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($palette as $name => $nameLabel)
                        @php
                            $value = old("overrides.{$scheme}.{$name}", $overrides[$scheme][$name] ?? '');
                        @endphp

                        <div>
                            <label class="field__label" for="token-{{ $scheme }}-{{ $name }}">
                                {{ $nameLabel }}
                                <span class="font-mono text-xs text-muted">{{ $name }}</span>
                            </label>

                            <div class="flex items-center gap-3">
                                <input type="color"
                                       class="h-10 w-12 shrink-0 cursor-pointer border border-line bg-transparent"
                                       id="token-{{ $scheme }}-{{ $name }}"
                                       value="{{ \App\Support\Colour::toHex($value) }}"
                                       oninput="this.nextElementSibling.value = this.value">
                                <input class="input font-mono text-sm"
                                       type="text"
                                       name="overrides[{{ $scheme }}][{{ $name }}]"
                                       id="token-text-{{ $scheme }}-{{ $name }}"
                                       value="{{ $value }}"
                                       placeholder="theme default">
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="lg:col-span-2">
            <button type="submit" class="btn btn--accent">
                Save Colours
                <span class="btn__strip" aria-hidden="true"></span>
            </button>
        </div>
    </form>
@endsection
