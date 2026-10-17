@extends('layouts.admin')

@section('title', 'Business Details')
@section('heading', 'Business Details')

@section('content')
    <p class="mb-6 max-w-3xl text-sm text-muted">
        The single source of truth for your business entity. These values feed the footer, the
        contact page and every frontend surface that discloses company information.
    </p>

    <form action="{{ route('admin.business.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="admin-card space-y-6">
            <h3 class="border-b border-line pb-4 font-display text-sm tracking-[0.24em] text-white uppercase">Brand</h3>

            <div class="grid gap-6 sm:grid-cols-2">
                <div class="field">
                    <label class="field__label" for="brand_name">Brand name *</label>
                    <input class="input" type="text" id="brand_name" name="brand_name"
                           value="{{ old('brand_name', $business['brand_name'] ?? '') }}" required>
                    @error('brand_name') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="legal_name">Legal entity name</label>
                    <input class="input" type="text" id="legal_name" name="legal_name"
                           value="{{ old('legal_name', $business['legal_name'] ?? '') }}" placeholder="Agape Infosys Private Limited">
                    @error('legal_name') <span class="field__error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="field">
                <label class="field__label" for="tagline">Tagline</label>
                <input class="input" type="text" id="tagline" name="tagline" maxlength="240"
                       value="{{ old('tagline', $business['tagline'] ?? '') }}" placeholder="Software products and engineering services, built to last.">
                @error('tagline') <span class="field__error">{{ $message }}</span> @enderror
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div class="field">
                    <label class="field__label" for="founded_year">Founded year</label>
                    <input class="input" type="number" id="founded_year" name="founded_year" min="1900" max="{{ now()->year }}"
                           value="{{ old('founded_year', $business['founded_year'] ?? '') }}">
                    @error('founded_year') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="industry">Industry</label>
                    <input class="input" type="text" id="industry" name="industry"
                           value="{{ old('industry', $business['industry'] ?? '') }}" placeholder="Software Development & Digital Products">
                    @error('industry') <span class="field__error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="admin-card space-y-6">
            <h3 class="border-b border-line pb-4 font-display text-sm tracking-[0.24em] text-white uppercase">Logo</h3>

            <p class="text-xs text-muted">
                Your logo appears in the site header, footer, admin sidebar and login. Configure a
                different mark for the dark and light themes, or keep the built-in wordmark. A square
                icon sits beside the brand name; a full logo image replaces both the mark and the text.
                SVG, PNG, WEBP or JPG up to 4&nbsp;MB.
            </p>

            <div class="grid gap-8 lg:grid-cols-2">
                @foreach (['dark' => 'Dark theme', 'light' => 'Light theme'] as $theme => $label)
                    @php
                        $logo = $brand_logos[$theme] ?? ['mode' => 'default', 'full' => null, 'icon' => null];
                        $mode = old("logos.$theme.mode", $logo['mode']);
                        $full = $logo['full'];
                        $icon = $logo['icon'];
                    @endphp

                    <div class="space-y-4 border border-line p-5" data-logo-group>
                        <div class="flex items-center justify-between gap-3">
                            <h4 class="font-display text-xs tracking-[0.24em] text-white uppercase">{{ $label }}</h4>
                            @if ($theme === 'dark')
                                <span class="tag">Default theme</span>
                            @endif
                        </div>

                        <div class="field">
                            <label class="field__label" for="logo_mode_{{ $theme }}">Logo style</label>
                            <select class="input" id="logo_mode_{{ $theme }}" name="logos[{{ $theme }}][mode]" data-logo-mode>
                                <option value="default" @selected($mode === 'default')>Built-in wordmark</option>
                                <option value="full" @selected($mode === 'full')>Full logo image</option>
                                <option value="icon" @selected($mode === 'icon')>Square icon + text</option>
                            </select>
                            @error("logos.$theme.mode") <span class="field__error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field" data-logo-field="full" @if ($mode !== 'full') hidden @endif>
                            <label class="field__label" for="logo_full_{{ $theme }}">Full logo image</label>
                            <input class="input" type="file" id="logo_full_{{ $theme }}" name="logos[{{ $theme }}][full]" accept="image/*">
                            @if ($full)
                                <span class="mt-2 block text-xs text-muted">
                                    Current:
                                    <img src="{{ Storage::url($full) }}" alt="" class="ml-1 inline-block h-6 w-auto align-middle object-contain">
                                </span>
                            @endif
                            @error("logos.$theme.full") <span class="field__error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field" data-logo-field="icon" @if ($mode !== 'icon') hidden @endif>
                            <label class="field__label" for="logo_icon_{{ $theme }}">Square icon</label>
                            <input class="input" type="file" id="logo_icon_{{ $theme }}" name="logos[{{ $theme }}][icon]" accept="image/*">
                            @if ($icon)
                                <span class="mt-2 block text-xs text-muted">
                                    Current:
                                    <img src="{{ Storage::url($icon) }}" alt="" class="ml-1 inline-block h-8 w-8 align-middle object-contain">
                                </span>
                            @endif
                            @error("logos.$theme.icon") <span class="field__error">{{ $message }}</span> @enderror
                        </div>

                        <p class="text-xs text-muted" data-logo-field="default" @if ($mode !== 'default') hidden @endif>
                            Using the built-in <strong class="text-white">{{ $business['brand_name'] ?? 'Agape Infosys' }}</strong> wordmark.
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="admin-card space-y-6">
            <h3 class="border-b border-line pb-4 font-display text-sm tracking-[0.24em] text-white uppercase">Registration</h3>

            <div class="grid gap-6 sm:grid-cols-2">
                <div class="field">
                    <label class="field__label" for="registration_number">Registration / CIN</label>
                    <input class="input" type="text" id="registration_number" name="registration_number"
                           value="{{ old('registration_number', $business['registration_number'] ?? '') }}" placeholder="U72900KA2014PTC000000">
                    @error('registration_number') <span class="field__error">{{ $message }}</span> @enderror
                    <span class="mt-1 block text-xs text-muted">Left blank, it is not disclosed on the frontend.</span>
                </div>

                <div class="field">
                    <label class="field__label" for="tax_number">GSTIN / Tax ID</label>
                    <input class="input" type="text" id="tax_number" name="tax_number"
                           value="{{ old('tax_number', $business['tax_number'] ?? '') }}" placeholder="29AABCA0000A1Z5">
                    @error('tax_number') <span class="field__error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="admin-card space-y-6">
            <h3 class="border-b border-line pb-4 font-display text-sm tracking-[0.24em] text-white uppercase">Contact</h3>

            <div class="grid gap-6 sm:grid-cols-2">
                <div class="field">
                    <label class="field__label" for="contact_email">Contact email *</label>
                    <input class="input" type="email" id="contact_email" name="contact_email"
                           value="{{ old('contact_email', $contact_email) }}" required>
                    @error('contact_email') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="contact_phone">Contact phone</label>
                    <input class="input" type="text" id="contact_phone" name="contact_phone"
                           value="{{ old('contact_phone', $contact_phone) }}" placeholder="+91 00000 00000">
                    @error('contact_phone') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="website">Website</label>
                    <input class="input" type="url" id="website" name="website"
                           value="{{ old('website', $business['website'] ?? '') }}" placeholder="https://agapeinfosys.com">
                    @error('website') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="support_hours">Support hours</label>
                    <input class="input" type="text" id="support_hours" name="support_hours"
                           value="{{ old('support_hours', $business['support_hours'] ?? '') }}" placeholder="Mon – Fri, 9:30 – 18:30 IST">
                    @error('support_hours') <span class="field__error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div class="field">
                    <label class="field__label" for="office_address">Office address</label>
                    <textarea class="input" id="office_address" name="office_address" rows="3">{{ old('office_address', $office_address) }}</textarea>
                    @error('office_address') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="mailing_address">Mailing address</label>
                    <textarea class="input" id="mailing_address" name="mailing_address" rows="3">{{ old('mailing_address', $mailing_address) }}</textarea>
                    @error('mailing_address') <span class="field__error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="admin-card space-y-6">
            <h3 class="border-b border-line pb-4 font-display text-sm tracking-[0.24em] text-white uppercase">Social Profiles</h3>

            <p class="text-xs text-muted">
                Footer social icons appear only for the profiles you fill in here.
            </p>

            <div class="grid gap-6 sm:grid-cols-2">
                @php
                    $socials = old('socials', $business['socials'] ?? []);
                @endphp

                @foreach (['linkedin' => 'LinkedIn', 'twitter' => 'X / Twitter', 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube'] as $network => $label)
                    <div class="field {{ $network === 'youtube' ? 'sm:col-span-2' : '' }}">
                        <label class="field__label" for="social_{{ $network }}">{{ $label }}</label>
                        <input class="input" type="url" id="social_{{ $network }}" name="socials[{{ $network }}]"
                               value="{{ $socials[$network] ?? '' }}" placeholder="https://{{ $network }}.com/your-handle">
                        @error('socials.'.$network) <span class="field__error">{{ $message }}</span> @enderror
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="btn btn--accent">
                Save Business Details
                <span class="btn__strip" aria-hidden="true"></span>
            </button>

            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="tag self-center hover:border-accent hover:text-accent">
                Preview site
            </a>
        </div>
    </form>
@endsection
