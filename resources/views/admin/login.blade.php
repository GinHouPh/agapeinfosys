<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>Sign In — {{ config('app.name') }} Admin</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink text-body">
    <main class="flex min-h-screen items-center justify-center px-5 py-16">
        <div class="w-full max-w-md">
            <a href="{{ route('home') }}" class="mb-10 flex items-center justify-center gap-3">
                <x-brand icon-class="grid h-10 w-10 place-items-center border border-line font-display text-lg text-accent" />
            </a>

            <div class="border border-line bg-surface p-8 sm:p-10">
                <span class="eyebrow">Control panel</span>

                <h1 class="mb-8 !text-3xl">
                    <span class="text-accent">Sign</span> in
                </h1>

                @if (session('status'))
                    <p class="form-status" role="status">{{ session('status') }}</p>
                @endif

                @if ($errors->any())
                    <div class="form-status" role="alert">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('admin.login.attempt') }}" method="POST" class="space-y-7">
                    @csrf

                    <div class="field">
                        <label class="field__label" for="email">Email</label>
                        <input
                            class="input"
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="username"
                            autofocus
                        >
                    </div>

                    <div class="field">
                        <label class="field__label" for="password">Password</label>
                        <input
                            class="input"
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                        >
                    </div>

                    <label class="flex items-center gap-3 text-sm text-muted">
                        <input type="checkbox" name="remember" value="1" class="h-4 w-4 accent-[#d90429]">
                        Keep me signed in
                    </label>

                    <button type="submit" class="btn btn--accent w-full">
                        Sign In
                        <span class="btn__strip" aria-hidden="true"></span>
                    </button>
                </form>
            </div>

            <p class="mt-8 text-center text-sm text-muted">
                <a href="{{ route('home') }}" class="hover:text-accent">&larr; Back to the website</a>
            </p>
        </div>
    </main>
</body>
</html>
