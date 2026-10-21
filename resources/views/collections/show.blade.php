@extends('layouts.app')

@section('title', $record->title.' | '.$type->name)
@section('description', $type->description)

@section('content')
    <main class="shell py-20">
        <p class="eyebrow">{{ $type->name }}</p>

        <h1 class="mt-4 font-display text-4xl tracking-[0.06em] text-white uppercase sm:text-5xl">
            {{ $record->title }}
        </h1>

        @if ($type->description)
            <p class="mt-6 max-w-2xl text-lg text-body">{{ $type->description }}</p>
        @endif

        <dl class="mt-12 grid gap-8 sm:grid-cols-2">
            @foreach ($type->fields as $field)
                @php($value = $record->value($field->name))

                @continue($value === null || $value === '' || $value === [])

                <div>
                    <dt class="font-display text-xs tracking-[0.24em] text-muted uppercase">
                        {{ $field->label }}
                    </dt>
                    <dd class="mt-3 text-body">
                        @if (is_array($value))
                            {{ implode(', ', array_filter($value, 'is_scalar')) }}
                        @else
                            {{ $value }}
                        @endif
                    </dd>
                </div>
            @endforeach
        </dl>
    </main>
@endsection
