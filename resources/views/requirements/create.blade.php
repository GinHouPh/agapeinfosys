@extends('layouts.app')

@section('title', 'Requirement Specification | Agape Infosys')

@section('description', 'Tell us what you want to build. Our team will review your requirement specification and respond with scope, estimates and a quotation.')

@section('content')
    <section class="relative overflow-hidden pt-40 pb-16">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_25%_15%,rgba(217,4,41,0.18),transparent_55%)]"
             aria-hidden="true"></div>

        <div class="shell relative">
            <span class="eyebrow">Requirement Specification</span>
            <h1 class="max-w-5xl">
                Tell us what to <span class="text-accent">build</span>.
            </h1>

            <p class="mt-8 max-w-2xl text-lg text-body">
                The more detail you share, the sharper our quotation. Fill in the brief below — it
                takes about five minutes, and every field helps us scope honestly.
            </p>
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

            <form action="{{ route('requirements.store') }}" method="POST" class="space-y-12" novalidate>
                @csrf

                {{-- 01 — Your details --}}
                <div class="space-y-6">
                    <div class="border-b border-line pb-4">
                        <h3 class="font-display text-sm tracking-[0.24em] text-white uppercase">
                            <span class="text-accent">01</span> — Your details
                        </h3>
                    </div>

                    <div class="grid gap-7 sm:grid-cols-2">
                        <div class="field">
                            <label class="field__label" for="name">Full Name *</label>
                            <input class="input" type="text" id="name" name="name" value="{{ old('name') }}"
                                   placeholder="Your full name" required autocomplete="name">
                            @error('name') <span class="field__error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field">
                            <label class="field__label" for="email">Email *</label>
                            <input class="input" type="email" id="email" name="email" value="{{ old('email') }}"
                                   placeholder="you@company.com" required autocomplete="email">
                            @error('email') <span class="field__error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field">
                            <label class="field__label" for="phone">Contact Number</label>
                            <input class="input" type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                                   placeholder="+91 00000 00000" autocomplete="tel">
                            @error('phone') <span class="field__error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field">
                            <label class="field__label" for="company">Company</label>
                            <input class="input" type="text" id="company" name="company" value="{{ old('company') }}"
                                   placeholder="Company name" autocomplete="organization">
                            @error('company') <span class="field__error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- 02 — The business --}}
                <div class="space-y-6">
                    <div class="border-b border-line pb-4">
                        <h3 class="font-display text-sm tracking-[0.24em] text-white uppercase">
                            <span class="text-accent">02</span> — The business
                        </h3>
                    </div>

                    <div class="field">
                        <label class="field__label" for="industry">Industry *</label>
                        <select class="input" id="industry" name="industry" required>
                            <option value="">Select your industry…</option>
                            @foreach ($industries as $option)
                                <option value="{{ $option }}" @selected(old('industry') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        @error('industry') <span class="field__error">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- 03 — The product --}}
                <div class="space-y-6">
                    <div class="border-b border-line pb-4">
                        <h3 class="font-display text-sm tracking-[0.24em] text-white uppercase">
                            <span class="text-accent">03</span> — The product
                        </h3>
                    </div>

                    <div class="field">
                        <label class="field__label" for="project_name">Project Name *</label>
                        <input class="input" type="text" id="project_name" name="project_name"
                               value="{{ old('project_name') }}" placeholder="What should we call this project?" required>
                        @error('project_name') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="brief">Project Overview *</label>
                        <textarea class="input" id="brief" name="brief" rows="4" required
                                  placeholder="What problem does it solve, who is it for, and what does success look like?">{{ old('brief') }}</textarea>
                        @error('brief') <span class="field__error">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- 04 — Solutions needed --}}
                <div class="space-y-6">
                    <div class="border-b border-line pb-4">
                        <h3 class="font-display text-sm tracking-[0.24em] text-white uppercase">
                            <span class="text-accent">04</span> — Solutions needed *
                        </h3>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($solutions as $option)
                            <label class="flex cursor-pointer items-center gap-3 border border-line p-4 transition-colors duration-300 hover:border-accent has-[:checked]:border-accent">
                                <input
                                    type="checkbox"
                                    name="solutions[]"
                                    value="{{ $option }}"
                                    @checked(in_array($option, (array) old('solutions', []), true))
                                    class="h-4 w-4 accent-accent"
                                >
                                <span class="text-sm text-white">{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('solutions') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                {{-- 05 — Core features --}}
                <div class="space-y-6">
                    <div class="border-b border-line pb-4">
                        <h3 class="font-display text-sm tracking-[0.24em] text-white uppercase">
                            <span class="text-accent">05</span> — Core features
                        </h3>
                    </div>

                    <div class="field">
                        <label class="field__label" for="features">Features Checklist *</label>
                        <textarea class="input" id="features" name="features" rows="6" required
                                  placeholder="List the must-have features — e.g. user registration, payments, admin dashboard, reports…">{{ old('features') }}</textarea>
                        @error('features') <span class="field__error">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- 06 — Integrations --}}
                <div class="space-y-6">
                    <div class="border-b border-line pb-4">
                        <h3 class="font-display text-sm tracking-[0.24em] text-white uppercase">
                            <span class="text-accent">06</span> — Integrations
                        </h3>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($integrations as $option)
                            <label class="flex cursor-pointer items-center gap-3 border border-line p-4 transition-colors duration-300 hover:border-accent has-[:checked]:border-accent">
                                <input
                                    type="checkbox"
                                    name="integrations[]"
                                    value="{{ $option }}"
                                    @checked(in_array($option, (array) old('integrations', []), true))
                                    class="h-4 w-4 accent-accent"
                                >
                                <span class="text-sm text-white">{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('integrations') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                {{-- 07 — Scale, budget & timeline --}}
                <div class="space-y-6">
                    <div class="border-b border-line pb-4">
                        <h3 class="font-display text-sm tracking-[0.24em] text-white uppercase">
                            <span class="text-accent">07</span> — Scale, budget &amp; timeline
                        </h3>
                    </div>

                    <div class="grid gap-7 sm:grid-cols-3">
                        <div class="field">
                            <label class="field__label" for="user_scale">Expected Users *</label>
                            <select class="input" id="user_scale" name="user_scale" required>
                                <option value="">Select…</option>
                                @foreach ($scales as $option)
                                    <option value="{{ $option }}" @selected(old('user_scale') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('user_scale') <span class="field__error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field">
                            <label class="field__label" for="budget">Budget Range *</label>
                            <select class="input" id="budget" name="budget" required>
                                <option value="">Select…</option>
                                @foreach ($budgets as $option)
                                    <option value="{{ $option }}" @selected(old('budget') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('budget') <span class="field__error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field">
                            <label class="field__label" for="timeline">Timeline *</label>
                            <select class="input" id="timeline" name="timeline" required>
                                <option value="">Select…</option>
                                @foreach ($timelines as $option)
                                    <option value="{{ $option }}" @selected(old('timeline') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('timeline') <span class="field__error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- 08 — Anything else --}}
                <div class="space-y-6">
                    <div class="border-b border-line pb-4">
                        <h3 class="font-display text-sm tracking-[0.24em] text-white uppercase">
                            <span class="text-accent">08</span> — Anything else
                        </h3>
                    </div>

                    <div class="field">
                        <label class="field__label" for="notes">Additional Notes</label>
                        <textarea class="input" id="notes" name="notes" rows="4"
                                  placeholder="Existing systems, compliance needs, references you like, or anything else worth knowing…">{{ old('notes') }}</textarea>
                        @error('notes') <span class="field__error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex flex-col items-start gap-4 border-t border-line pt-8 sm:flex-row sm:items-center">
                    <button type="submit" class="btn btn--accent">
                        Submit Requirement
                        <span class="btn__strip" aria-hidden="true"></span>
                    </button>

                    <p class="text-sm text-muted">
                        We usually respond within one business day. Prefer to talk?
                        <a href="{{ route('contact.create') }}" class="text-accent hover:underline">Contact us</a>.
                    </p>
                </div>
            </form>
        </div>
    </section>
@endsection
