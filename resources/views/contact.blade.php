@extends('layouts.app')

@section('title', 'Contact Agape Infosys — Start A Project')

@section('description', 'Talk to the Agape Infosys team about custom software development, IT consultancy, cloud and digital transformation engagements.')

@section('content')
    <section class="relative overflow-hidden pt-24 pb-16 lg:pb-28">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_25%_20%,rgba(217,4,41,0.20),transparent_55%)]"
             aria-hidden="true"></div>

        <div class="shell relative">
            <span class="eyebrow">Contact</span>

            <h1 class="max-w-5xl">
                <span class="text-accent">Connect</span>
                with customers to your business — starting with ours.
            </h1>

            <p class="mt-8 max-w-2xl text-lg text-body">
                Send us the outline of your project, or just the problem you are stuck on.
                We reply to every enquiry within one business day.
            </p>
        </div>

        <x-scroll-cue />
    </section>

    <section class="section section--surface">
        <div class="shell grid-2 gap-16">
            <div class="order-2 lg:order-1">
                <div class="border border-line bg-ink p-8 sm:p-10">
                    <h4 class="mb-8 !text-2xl">
                        <span class="text-accent">Let's</span> talk business
                    </h4>

                    @include('partials.enquiry-form', ['compact' => false])
                </div>
            </div>

            <div class="order-1 lg:order-2">
                <x-section-head
                    title="Where To Find Us"
                    subtitle="Studio & mailing details"
                    mark="contact"
                />

                <ul class="space-y-8">
                    <li class="border-b border-line pb-6">
                        <span class="block font-display text-xs tracking-[0.24em] text-accent uppercase">Our Office</span>
                        <p class="mt-3 whitespace-pre-line text-body">
                            {!! nl2br(e(\App\Models\Setting::value('office_address', "Level 4, Tech Park, Innovation Drive\nBengaluru, Karnataka 560103"))) !!}
                        </p>
                    </li>

                    <li class="border-b border-line pb-6">
                        <span class="block font-display text-xs tracking-[0.24em] text-accent uppercase">Mailing Address</span>
                        <p class="mt-3 whitespace-pre-line text-body">
                            {!! nl2br(e(\App\Models\Setting::value('mailing_address', "PO Box 4021, Knowledge City\nBengaluru, Karnataka 560001"))) !!}
                        </p>
                    </li>

                    <li class="border-b border-line pb-6">
                        <span class="block font-display text-xs tracking-[0.24em] text-accent uppercase">Email</span>
                        <a href="mailto:{{ \App\Models\Setting::value('contact_email', 'hello@agapeinfosys.com') }}"
                           class="mt-3 block text-lg text-white hover:text-accent">
                            {{ \App\Models\Setting::value('contact_email', 'hello@agapeinfosys.com') }}
                        </a>
                    </li>

                    <li>
                        <span class="block font-display text-xs tracking-[0.24em] text-accent uppercase">Phone</span>
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', \App\Models\Setting::value('contact_phone', '+91 00000 00000')) }}"
                           class="mt-3 block text-lg text-white hover:text-accent">
                            {{ \App\Models\Setting::value('contact_phone', '+91 00000 00000') }}
                        </a>
                    </li>
                </ul>

                @php
                    $business = \App\Models\Setting::structured('business', []);
                    $supportHours = $business['support_hours'] ?? '';
                    $disclosures = array_filter([
                        'Legal entity' => $business['legal_name'] ?? '',
                        'Registration' => $business['registration_number'] ?? '',
                        'GSTIN' => $business['tax_number'] ?? '',
                        'Website' => $business['website'] ?? '',
                        'Founded' => $business['founded_year'] ?? '',
                        'Industry' => $business['industry'] ?? '',
                    ]);
                @endphp

                <div class="mt-10 border border-line bg-ink p-6">
                    <span class="block font-display text-xs tracking-[0.24em] text-accent uppercase">Working Hours</span>
                    <p class="mt-3 text-body">
                        {{ $supportHours !== '' ? $supportHours : 'Monday – Friday, 9:30 – 18:30 IST.' }}<br>
                        Critical support is staffed around the clock.
                    </p>
                </div>

                @if ($disclosures !== [])
                    <div class="mt-10 border border-line bg-ink p-6">
                        <span class="block font-display text-xs tracking-[0.24em] text-accent uppercase">Business Details</span>

                        <dl class="mt-4 space-y-3 text-sm">
                            @foreach ($disclosures as $label => $value)
                                <div class="flex justify-between gap-6">
                                    <dt class="shrink-0 text-muted">{{ $label }}</dt>
                                    <dd class="text-right break-all text-white">
                                        @if ($label === 'Website')
                                            <a href="{{ $value }}" target="_blank" rel="noopener" class="hover:text-accent">
                                                {{ parse_url($value, PHP_URL_HOST) ?: $value }}
                                            </a>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
