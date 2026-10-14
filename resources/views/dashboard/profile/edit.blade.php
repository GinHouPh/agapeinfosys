@extends('layouts.app')

@section('title', 'My Profile')
@section('description', 'Update your client account details and password.')

@section('content')
    @include('dashboard.partials.nav', [
        'title' => 'My Profile',
        'lead' => 'Your account details. Orders attach to the email address on file, so keep it the one you check out with.',
    ])

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="admin-card !p-8">
            <h2 class="mb-6 !text-xl text-white">Account details</h2>

            <form action="{{ route('dashboard.profile.update') }}" method="POST" class="space-y-7">
                @csrf
                @method('PUT')

                <div class="field">
                    <label class="field__label" for="name">Full name</label>
                    <input class="input" type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">
                    @error('name') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="email">Email</label>
                    <input class="input" type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
                    @error('email') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="phone">Phone</label>
                    <input class="input" type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel">
                    @error('phone') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn btn--accent w-full">
                    Save Changes
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>
            </form>
        </section>

        <section class="admin-card !p-8">
            <h2 class="mb-6 !text-xl text-white">Change password</h2>

            <form action="{{ route('dashboard.password.update') }}" method="POST" class="space-y-7">
                @csrf
                @method('PUT')

                <div class="field">
                    <label class="field__label" for="current_password">Current password</label>
                    <input class="input" type="password" id="current_password" name="current_password" required autocomplete="current-password">
                    @error('current_password') <span class="field__error">{{ $message }}</span> @enderror
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
                    Change Password
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>
            </form>
        </section>
    </div>

    @include('dashboard.partials.end')
@endsection
