@extends('layouts.admin')

@section('title', 'Translations')
@section('heading', 'Translations')

@section('content')
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.locales.index') }}" class="tag hover:border-accent hover:text-accent">All languages</a>

        <form method="GET" class="flex items-center gap-2">
            <select class="input !py-2" name="locale" onchange="this.form.submit()">
                @foreach ($languages as $option)
                    <option value="{{ $option->code }}" @selected($option->code === $language->code)>
                        {{ $option->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <p class="mb-6 max-w-2xl text-sm text-muted">
        Translating <span class="text-white">{{ $record->title ?? 'this record' }}</span> into
        <span class="text-white">{{ $language->name }}</span>. Anything left blank falls back to the
        original text.
    </p>

    <form action="{{ route('admin.locales.translations.update', ['type' => $type, 'id' => $record->id, 'locale' => $language->code]) }}"
          method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="admin-card">
            @forelse ($fields as $address => $label)
                <div class="border-b border-line p-5 last:border-b-0">
                    <label class="field__label" for="value-{{ $loop->index }}">{{ $label }}</label>
                    <textarea class="input" id="value-{{ $loop->index }}" name="values[{{ $address }}]" rows="2"
                              placeholder="Original: {{ \Illuminate\Support\Str::limit($values[$address] ?? '', 90) }}">{{ $values[$address] ?? '' }}</textarea>
                    <p class="mt-1 font-mono text-xs text-muted">{{ $address }}</p>
                </div>
            @empty
                <p class="py-10 text-center text-muted">
                    There is nothing on this record to translate yet.
                </p>
            @endforelse
        </section>

        @if ($fields !== [])
            <button type="submit" class="btn btn--accent">
                Save Translations
                <span class="btn__strip" aria-hidden="true"></span>
            </button>
        @endif
    </form>
@endsection
