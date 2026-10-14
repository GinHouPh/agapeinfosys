@php
    $email = \App\Models\Setting::value('contact_email', 'hello@agapeinfosys.com');
    $phone = \App\Models\Setting::value('contact_phone', '+91 00000 00000');

    $account = auth()->check()
        ? ['label' => 'Dashboard', 'route' => 'dashboard.index']
        : ['label' => 'Client Login', 'route' => 'login'];
@endphp

<div class="top-bar">
    <div class="shell top-bar__inner">
        <div class="hidden items-center gap-5 text-muted sm:flex">
            <a href="mailto:{{ $email }}" class="inline-flex items-center gap-2 transition-colors duration-300 hover:text-accent">
                <span class="h-1.5 w-1.5 rounded-full bg-accent" aria-hidden="true"></span>
                {{ $email }}
            </a>

            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="transition-colors duration-300 hover:text-accent">
                {{ $phone }}
            </a>
        </div>

        <div class="ml-auto flex items-center gap-5">
            <a
                href="{{ route('requirements.create') }}"
                class="font-display text-xs tracking-[0.24em] text-accent uppercase {{ request()->routeIs('requirements.*') ? 'underline underline-offset-4' : '' }}"
            >
                Get a Quote
            </a>

            <span class="h-3 w-px bg-line" aria-hidden="true"></span>

            <a
                href="{{ route($account['route']) }}"
                class="text-xs tracking-[0.12em] text-muted uppercase transition-colors duration-300 hover:text-accent {{ request()->routeIs($account['route']) || request()->routeIs('dashboard.*') ? 'text-accent' : '' }}"
            >
                {{ $account['label'] }}
            </a>
        </div>
    </div>

    <span data-scroll-progress class="top-bar__progress" aria-hidden="true"></span>
</div>
