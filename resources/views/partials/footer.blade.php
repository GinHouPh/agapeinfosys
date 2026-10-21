@php
    $office = \App\Models\Setting::value('office_address', 'Level 4, Tech Park, Innovation Drive,\nBengaluru, Karnataka 560103');
    $mailing = \App\Models\Setting::value('mailing_address', 'PO Box 4021, Knowledge City,\nBengaluru, Karnataka 560001');
    $email = \App\Models\Setting::value('contact_email', 'hello@agapeinfosys.com');
    $phone = \App\Models\Setting::value('contact_phone', '+91 00000 00000');

    $business = \App\Models\Setting::structured('business', []);
    $brand = $business['brand_name'] ?? 'Agape Infosys';
    $socials = array_filter($business['socials'] ?? []);

    $menus = app(\App\Services\MenuService::class);
    $footerLinks = $menus->forLocation(\App\Enums\MenuLocation::Footer);
    $legalLinks = $menus->forLocation(\App\Enums\MenuLocation::Legal);
    $languages = app(\App\Services\LocaleService::class)->available();
    $currentLanguage = app(\App\Services\LocaleService::class)->current();
@endphp

<footer class="border-t border-line bg-ink">
    <div class="shell grid gap-12 py-20 md:grid-cols-2 lg:grid-cols-4">
        <div>
            <h6 class="mb-6 font-display text-sm tracking-[0.24em] text-white uppercase">Pages</h6>
            <ul class="space-y-3 text-body">
                @foreach ($footerLinks as $link)
                    <li><a href="{{ $link['url'] ?? '#' }}" class="hover:text-accent">{{ $link['label'] }}</a></li>
                @endforeach
                <li>
                    <a href="{{ route(auth()->check() ? (auth()->user()->isAdmin() ? 'admin.dashboard' : 'dashboard.index') : 'login') }}" class="hover:text-accent">
                        @if (! auth()->check())
                            Client Login
                        @elseif (auth()->user()->isAdmin())
                            Admin Panel
                        @else
                            Client Dashboard
                        @endif
                    </a>
                </li>
            </ul>
        </div>

        <div>
            <h6 class="mb-6 font-display text-sm tracking-[0.24em] text-white uppercase">Our Office</h6>
            <p class="whitespace-pre-line text-body">{!! nl2br(e($office)) !!}</p>
        </div>

        <div>
            <h6 class="mb-6 font-display text-sm tracking-[0.24em] text-white uppercase">Mailing Address</h6>
            <p class="whitespace-pre-line text-body">{!! nl2br(e($mailing)) !!}</p>
        </div>

        <div>
            <h6 class="mb-6 font-display text-sm tracking-[0.24em] text-white uppercase">Contact Us</h6>
            <ul class="space-y-3">
                <li>
                    <a href="mailto:{{ $email }}" class="inline-flex items-center gap-3 hover:text-accent">
                        <span class="h-1.5 w-1.5 rounded-full bg-accent" aria-hidden="true"></span>
                        {{ $email }}
                    </a>
                </li>
                <li>
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="inline-flex items-center gap-3 hover:text-accent">
                        <span class="h-1.5 w-1.5 rounded-full bg-accent" aria-hidden="true"></span>
                        {{ $phone }}
                    </a>
                </li>
            </ul>

            @if ($socials !== [])
                <div class="mt-6 flex gap-3">
                    @foreach ($socials as $network => $url)
                        <a
                            href="{{ $url }}"
                            rel="noopener noreferrer"
                            target="_blank"
                            class="grid h-10 w-10 place-items-center border border-line text-xs tracking-widest text-muted uppercase transition-colors duration-300 hover:border-accent hover:text-accent"
                            aria-label="{{ ucfirst($network) }}"
                        >
                            {{ strtoupper(substr($network, 0, 2)) }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="border-t border-line">
        <div class="shell flex flex-wrap items-center justify-center gap-x-8 gap-y-3 py-5 text-sm text-muted">
            @foreach ($legalLinks as $link)
                <a href="{{ $link['url'] ?? '#' }}" class="hover:text-accent">{{ $link['label'] }}</a>
            @endforeach
        </div>

            @if ($languages->count() > 1)
                <div class="border-t border-line">
                    <div class="shell flex flex-wrap items-center justify-center gap-x-6 gap-y-3 py-5 text-sm text-muted">
                        <span class="text-xs tracking-[0.16em] uppercase">Language</span>

                        @foreach ($languages as $language)
                            <a href="{{ route('locale.choose', $language->code) }}"
                               class="{{ $currentLanguage?->code === $language->code ? 'text-accent' : 'hover:text-accent' }}">
                                {{ $language->native_name ?? $language->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="border-t border-line">
                <div class="shell flex flex-col items-center justify-between gap-4 py-7 sm:flex-row">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <x-brand :show-mark="false" text-class="font-display text-base tracking-[0.22em] text-white uppercase" />
                </a>

                <p class="text-sm text-muted">
                    &copy; <span data-year>{{ date('Y') }}</span> {{ $brand }}. All Rights Reserved.
                </p>

                <a href="#main" class="font-display text-xs tracking-[0.24em] text-muted uppercase hover:text-accent">
                    Back to top
                </a>
            </div>
        </div>
    </div>
</footer>
