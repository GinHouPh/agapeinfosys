@php
    $compact = $compact ?? false;
    $action = route('contact.store');
@endphp

@if (session('status'))
    <p class="form-status" role="status">
        {{ session('status') }}
    </p>
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

<form action="{{ $action }}" method="POST" class="space-y-7" novalidate>
    @csrf

    <div class="grid gap-7 sm:grid-cols-2">
        <div class="field">
            <label class="field__label" for="name">Full Name</label>
            <input
                class="input"
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                placeholder="Your full name"
                required
                autocomplete="name"
            >
            @error('name') <span class="field__error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label class="field__label" for="email">Email ID</label>
            <input
                class="input"
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="you@company.com"
                required
                autocomplete="email"
            >
            @error('email') <span class="field__error">{{ $message }}</span> @enderror
        </div>

        <div class="field">
            <label class="field__label" for="phone">Contact Number</label>
            <input
                class="input"
                type="tel"
                id="phone"
                name="phone"
                value="{{ old('phone') }}"
                placeholder="+91 00000 00000"
                autocomplete="tel"
            >
            @error('phone') <span class="field__error">{{ $message }}</span> @enderror
        </div>

        @unless ($compact)
            <div class="field">
                <label class="field__label" for="company">Company</label>
                <input
                    class="input"
                    type="text"
                    id="company"
                    name="company"
                    value="{{ old('company') }}"
                    placeholder="Company name"
                    autocomplete="organization"
                >
                @error('company') <span class="field__error">{{ $message }}</span> @enderror
            </div>

            <div class="field sm:col-span-2">
                <label class="field__label" for="subject">Subject</label>
                <input
                    class="input"
                    type="text"
                    id="subject"
                    name="subject"
                    value="{{ old('subject') }}"
                    placeholder="What is this about?"
                >
                @error('subject') <span class="field__error">{{ $message }}</span> @enderror
            </div>
        @endunless
    </div>

    <div class="field">
        <label class="field__label" for="message">Message</label>
        <textarea
            class="input"
            id="message"
            name="message"
            rows="5"
            placeholder="Tell us about the problem you are solving…"
            required
        >{{ old('message') }}</textarea>
        @error('message') <span class="field__error">{{ $message }}</span> @enderror
    </div>

    <button type="submit" class="btn btn--accent w-full sm:w-auto">
        Send Enquiry
        <span class="btn__strip" aria-hidden="true"></span>
    </button>
</form>
