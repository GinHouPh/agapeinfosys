<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>Back shortly — {{ config('app.name') }}</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink">
    <main class="shell grid min-h-screen place-items-center py-20">
        <div class="max-w-xl text-center">
            <x-brand show-mark text-class="font-display text-sm tracking-[0.24em] text-white uppercase" />

            <h1 class="mt-10 font-display text-4xl tracking-[0.08em] text-white uppercase sm:text-5xl">
                Back shortly
            </h1>

            <p class="mt-6 text-body">
                {{ $message }}
            </p>

            <p class="mt-10 text-xs tracking-[0.16em] text-muted uppercase">
                {{ config('app.name') }}
            </p>
        </div>
    </main>
</body>
</html>
