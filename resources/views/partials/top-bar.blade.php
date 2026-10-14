@php
    $email = \App\Models\Setting::value('contact_email', 'hello@agapeinfosys.com');
    $phone = \App\Models\Setting::value('contact_phone', '+91 00000 00000');

    $account = auth()->check()
        ? ['label' => 'Dashboard', 'route' => 'dashboard.index']
        : ['label' => 'Client Login', 'route' => 'login'];

    /**
     * Dark is rendered as pressed: the markup ships dark and the layout's
     * bootstrap script flips it before first paint, so the control stays
     * honest even when JavaScript never runs.
     */
    $themeChoices = [
        ['mode' => 'dark', 'label' => 'Dark theme', 'title' => 'Dark'],
        ['mode' => 'light', 'label' => 'Light theme', 'title' => 'Light'],
        ['mode' => 'system', 'label' => 'Screen theme', 'title' => 'Screen (follow the system)'],
    ];
@endphp

<div class="top-bar">
    <div class="top-bar__inner">
        <div class="flex items-center gap-4">
            <div class="theme-toggle" role="group" aria-label="Colour theme">
                @foreach ($themeChoices as $choice)
                    <button
                        type="button"
                        class="theme-toggle__btn"
                        data-theme-choice="{{ $choice['mode'] }}"
                        aria-pressed="{{ $choice['mode'] === 'dark' ? 'true' : 'false' }}"
                        aria-label="{{ $choice['label'] }}"
                        title="{{ $choice['title'] }}"
                    >
                        @if ($choice['mode'] === 'dark')
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                            </svg>
                        @elseif ($choice['mode'] === 'light')
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="4"/>
                                <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
                            </svg>
                        @else
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="2" y="3" width="20" height="14" rx="2"/>
                                <path d="M8 21h8M12 17v4"/>
                            </svg>
                        @endif
                    </button>
                @endforeach
            </div>

            <div class="hidden items-center gap-5 text-muted xl:flex">
                <a href="mailto:{{ $email }}" class="inline-flex items-center gap-2 transition-colors duration-300 hover:text-accent">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent" aria-hidden="true"></span>
                    {{ $email }}
                </a>

                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="transition-colors duration-300 hover:text-accent">
                    {{ $phone }}
                </a>
            </div>
        </div>

        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3 justify-self-center" aria-label="{{ config('app.name') }} home">
            <span class="grid h-9 w-9 shrink-0 place-items-center border border-line font-display text-base tracking-normal text-accent">
                A
            </span>
            <span class="hidden font-display text-lg tracking-[0.22em] text-white uppercase sm:inline">
                Agape<span class="text-accent">Infosys</span>
            </span>
        </a>

        <div class="flex items-center justify-end gap-4">
            <a
                href="{{ route('requirements.create') }}"
                class="font-display text-[11px] tracking-[0.24em] text-white uppercase transition-colors duration-300 hover:text-accent sm:text-xs {{ request()->routeIs('requirements.*') ? 'underline underline-offset-4' : '' }}"
            >
                Get a Quote
            </a>

            <span class="hidden h-3 w-px bg-line md:block" aria-hidden="true"></span>

            <a
                href="{{ route($account['route']) }}"
                class="hidden text-xs tracking-[0.12em] text-muted uppercase transition-colors duration-300 hover:text-accent md:block {{ request()->routeIs($account['route']) || request()->routeIs('dashboard.*') ? 'text-accent' : '' }}"
            >
                {{ $account['label'] }}
            </a>
        </div>
    </div>

    <span data-scroll-progress class="top-bar__progress" aria-hidden="true"></span>
</div>
