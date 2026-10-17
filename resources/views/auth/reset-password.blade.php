@extends('layouts.app')

@section('title', 'Choose a New Password')
@section('description', 'Set a new password for your Agape Infosys client account.')

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
                    New <span class="text-accent">password</span>
                </h1>

                <div class="border border-line bg-surface p-8 sm:p-10">
                    @if ($errors->any())
                        <div class="form-status mb-8" role="alert">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('password.update') }}" method="POST" class="space-y-7">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="field">
                            <label class="field__label" for="email">Email</label>
                            <input
                                class="input"
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email', $email) }}"
                                required
                                autocomplete="username"
                            >
                            @error('email') <span class="field__error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field">
                            <label class="field__label" for="password">New password</label>
                            <input class="input" type="password" id="password" name="password" required autocomplete="new-password">
                            @error('password') <span class="field__error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field">
                            <label class="field__label" for="password_confirmation">Confirm new password</label>
                            <input class="input" type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                        </div>

                        <button type="submit" class="btn btn--accent w-full">
                            Reset Password
                            <span class="btn__strip" aria-hidden="true"></span>
                        </button>
                    </form>
                </div>

                <p class="mt-8 text-center text-sm text-muted">
                    <a href="{{ route('login') }}" class="text-accent hover:underline">Back to sign in</a>
                </p>
            </div>
        </div>
    </section>
@endsection
