@extends('layouts.app')

@section('title', 'Forgot Password')
@section('description', 'Request a password reset link for your Agape Infosys client account.')

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
                    <span class="text-accent">Reset</span> password
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

                    <p class="mb-6 text-sm text-muted">
                        Enter the email address on your account and we will send you a link to choose a new password.
                    </p>

                    <form action="{{ route('password.email') }}" method="POST" class="space-y-7">
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

                        <button type="submit" class="btn btn--accent w-full">
                            Email Reset Link
                            <span class="btn__strip" aria-hidden="true"></span>
                        </button>
                    </form>
                </div>

                <p class="mt-8 text-center text-sm text-muted">
                    Remembered it?
                    <a href="{{ route('login') }}" class="text-accent hover:underline">Back to sign in</a>.
                </p>
            </div>
        </div>
    </section>
@endsection
