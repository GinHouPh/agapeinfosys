@extends('layouts.app')

@section('title', 'Page Not Found | Agape Infosys')

@section('content')
    <section class="relative flex min-h-[calc(100svh_-_7.5rem)] items-center pt-20">
        <div class="shell text-center">
            <span class="eyebrow">Error 404</span>

            <h1 class="mx-auto max-w-3xl">
                <span class="text-accent">Lost</span>
                on the way to the server room.
            </h1>

            <p class="mx-auto mt-8 max-w-xl text-body">
                The page you were looking for has moved or never existed.
                Try one of these instead.
            </p>

            <div class="mt-10 flex flex-wrap justify-center gap-4">
                <x-btn :href="route('home')" variant="accent" label="Back Home" />
                <x-btn :href="route('services.index')" variant="ghost" label="Our Services" />
            </div>
        </div>

        <x-scroll-cue />
    </section>
@endsection
