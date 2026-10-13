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
        <div class="shell max-w-4xl">
            @if (session('status'))
                <p class="form-status" role="status">{{ session('status') }}</p>
            @endif

            @if ($errors->any())
                <div class="form-status" role="alert">
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($form->fields->isEmpty())
                <p class="text-lg text-muted">
                    This form has no questions yet — check back shortly.
                </p>
            @else
                @php
                    $action = request()->routeIs('requirements.*')
                        ? route('requirements.store')
                        : route('forms.store', $form);
                @endphp

                <form action="{{ $action }}" method="POST" class="space-y-12" novalidate>
                    @csrf

                    @php $section = 0; @endphp

                    @foreach ($groups as $group)
                        <div class="space-y-6">
                            @if ($group['group'] !== null && $group['group'] !== '')
                                <div class="border-b border-line pb-4">
                                    <h3 class="font-display text-sm tracking-[0.24em] text-white uppercase">
                                        <span class="text-accent">{{ str_pad((string) ++$section, 2, '0', STR_PAD_LEFT) }}</span>
                                        — {{ $group['group'] }}
                                    </h3>
                                </div>
                            @endif

                            <div class="grid gap-7 sm:grid-cols-2">
                                @foreach ($group['fields'] as $field)
                                    @include('forms.field', ['field' => $field])
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div class="flex flex-col items-start gap-4 border-t border-line pt-8 sm:flex-row sm:items-center">
                        <button type="submit" class="btn btn--accent">
                            {{ $form->submit_label }}
                            <span class="btn__strip" aria-hidden="true"></span>
                        </button>

                        <p class="text-sm text-muted">
                            We usually respond within one business day. Prefer to talk?
                            <a href="{{ route('contact.create') }}" class="text-accent hover:underline">Contact us</a>.
                        </p>
                    </div>
                </form>
            @endif
        </div>
    </section>
@endsection
