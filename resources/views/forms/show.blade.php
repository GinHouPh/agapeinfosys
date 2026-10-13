@extends('layouts.app')

@section('title', $form->title.' | Agape Infosys')

@section('description', \Illuminate\Support\Str::limit(strip_tags($form->description ?? ''), 155, '…'))

@section('content')
    <section class="relative overflow-hidden pt-40 pb-16">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_25%_15%,rgba(217,4,41,0.18),transparent_55%)]"
             aria-hidden="true"></div>

        <div class="shell relative">
            <span class="eyebrow">{{ $form->title }}</span>
            <h1 class="max-w-5xl">
                @if ($form->slug === \App\Http\Controllers\FormController::REQUIREMENT_FORM_SLUG)
                    Tell us what to <span class="text-accent">build</span>.
                @else
                    {{ $form->title }}
                @endif
            </h1>

            @if ($form->description)
                <p class="mt-8 max-w-2xl text-lg text-body">{!! nl2br(e($form->description)) !!}</p>
            @endif
        </div>
    </section>

    <section class="section section--surface">
        @include('partials.dynamic-form')
    </section>
@endsection
