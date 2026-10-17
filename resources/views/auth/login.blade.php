@extends('layouts.app')

@section('title', 'Client Login')
@section('description', 'Sign in to your client dashboard to see orders, subscriptions and digital access bought from Agape Infosys.')

@section('content')
    <section class="relative overflow-hidden pt-40 pb-24">
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_25%_20%,rgba(217,4,41,0.20),transparent_55%)]"
            aria-hidden="true"
        ></div>

        <div class="shell relative">
            <div class="mx-auto w-full max-w-md">
                <span class="eyebrow">Client area</span>

                <h1 class="mb-8 !text-4xl sm:!text-5xl">
                    <span class="text-accent">Sign</span> in
                </h1>

                <div class="border border-line bg-surface p-8 sm:p-10">
                    @if (session('status'))
                        <p class="form-status mb-8" role="status">{{ session('status') }}</p>
                    @endif

                    @if ($errors->any())
                        <div class="form-status mb-8" role="alert">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('login.attempt') }}" method="POST" class="space-y-7">
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
                            @error('email') <span class="field__error">{{ $message }}</span> @enderror
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
                            @error('password') <span class="field__error">{{ $message }}</span> @enderror
                        </div>

                        <div class="-mt-4 flex justify-end">
                            <a href="{{ route('password.request') }}" class="text-sm text-accent hover:underline">Forgot password?</a>
                        </div>

                        <label class="flex items-center gap-3 text-sm text-muted">
                            <input type="checkbox" name="remember" value="1" class="h-4 w-4 accent-[#d90429]" @checked(old('remember'))>
                            Keep me signed in
                        </label>

                        <button type="submit" class="btn btn--accent w-full">
                            Sign In
                            <span class="btn__strip" aria-hidden="true"></span>
                        </button>
                    </form>
                </div>

                <p class="mt-8 text-center text-sm text-muted">
                    New here?
                    <a href="{{ route('register') }}" class="text-accent hover:underline">Create an account</a>
                    — purchases made with your email address appear in your dashboard.
                </p>
            </div>
        </div>
    </section>
@endsection
