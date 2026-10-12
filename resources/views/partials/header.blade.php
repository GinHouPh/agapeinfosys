@php
    $navItems = [
        'home' => ['label' => 'Home', 'route' => 'home'],
        'about' => ['label' => 'About', 'route' => 'about'],
        'services' => ['label' => 'Services', 'route' => 'services.index'],
        'shop' => ['label' => 'Shop', 'route' => 'shop.index'],
        'portfolio' => ['label' => 'Portfolio', 'route' => 'portfolio.index'],
        'blog' => ['label' => 'Blog', 'route' => 'blog.index'],
        'contact' => ['label' => 'Contact', 'route' => 'contact.create'],
    ];
@endphp

<header data-site-header class="site-header">
    <div class="shell flex h-20 items-center justify-between gap-6">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3" aria-label="{{ config('app.name') }} home">
            <span class="grid h-10 w-10 place-items-center border border-line font-display text-lg tracking-normal text-accent">
                A
            </span>
            <span class="font-display text-lg tracking-[0.22em] text-white uppercase">
                Agape<span class="text-accent">Infosys</span>
            </span>
        </a>

        <nav class="hidden items-center gap-9 lg:flex" aria-label="Primary">
            @foreach ($navItems as $key => $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="nav-link {{ request()->routeIs($item['route']) ? 'is-active' : '' }}"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="hidden lg:block">
            <a href="{{ route('contact.create') }}" class="btn btn--solid">
                Let's Talk
                <span class="btn__strip" aria-hidden="true"></span>
            </a>
        </div>

        <button
            type="button"
            data-nav-toggle
            class="grid h-11 w-11 place-items-center border border-line text-white lg:hidden"
            aria-label="Toggle navigation"
            aria-expanded="false"
            aria-controls="mobile-nav"
        >
            <svg width="20" height="14" viewBox="0 0 20 14" fill="none" aria-hidden="true">
                <path d="M0 1h20M0 7h20M0 13h20" stroke="currentColor" stroke-width="1.5"/>
            </svg>
        </button>
    </div>

    <div
        id="mobile-nav"
        data-nav-panel
        class="fixed inset-0 -z-10 flex flex-col gap-2 bg-ink px-6 pt-24 opacity-0 invisible transition-all duration-300 lg:hidden"
    >
        @foreach ($navItems as $key => $item)
            <a
                href="{{ route($item['route']) }}"
                class="border-b border-line py-4 font-display text-2xl tracking-[0.1em] text-white capitalize {{ request()->routeIs($item['route']) ? 'text-accent' : '' }}"
            >
                {{ $item['label'] }}
            </a>
        @endforeach

        <a href="{{ route('contact.create') }}" class="btn btn--accent mt-6 self-start">
            Let's Talk
            <span class="btn__strip" aria-hidden="true"></span>
        </a>
    </div>
</header>
