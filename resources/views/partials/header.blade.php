@php
    $menus = app(\App\Services\MenuService::class);
    $navItems = $menus->forLocation(\App\Enums\MenuLocation::Header);

    $account = auth()->check()
        ? ['label' => 'Dashboard', 'route' => auth()->user()->isAdmin() ? 'admin.dashboard' : 'dashboard.index']
        : ['label' => 'Login', 'route' => 'login'];

    $cartCount = app(\App\Services\Shop\Cart::class)->count();
@endphp

<header data-site-header class="site-header">
    <div class="site-header__bar">
        <nav class="hidden items-center gap-2 xl:flex" aria-label="Primary">
            @foreach ($navItems as $item)
                <a
                    href="{{ $item['url'] ?? '#' }}"
                    class="nav-item {{ $menus->isActive($item) ? 'is-active' : '' }}"
                >
                    @if ($item['icon'])
                        <x-nav-icon :name="$item['icon']" />
                    @endif
                    <span class="nav-item__label">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <span class="grow"></span>

        <a href="{{ route('contact.create') }}" class="btn btn--solid hidden 2xl:inline-flex">
            Let's Talk
            <span class="btn__strip" aria-hidden="true"></span>
        </a>

        <button
            type="button"
            data-search-open
            class="icon-btn"
            aria-label="Search the site"
            aria-expanded="false"
            aria-controls="site-search"
        >
            <svg width="18" height="18" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <circle cx="9" cy="9" r="6" stroke="currentColor" stroke-width="1.5"/>
                <path d="m13.5 13.5 4 4" stroke="currentColor" stroke-width="1.5"/>
            </svg>
        </button>

        <a
            href="{{ route('shop.cart.index') }}"
            class="icon-btn {{ request()->routeIs('shop.cart.*') ? 'border-accent text-accent' : '' }}"
            aria-label="Cart{{ $cartCount > 0 ? " ({$cartCount} items)" : '' }}"
        >
            <svg width="18" height="18" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M3 4h1.5l1.8 8.5h8.4L16.5 6H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="7.5" cy="15.5" r="1.25" fill="currentColor"/>
                <circle cx="13.5" cy="15.5" r="1.25" fill="currentColor"/>
            </svg>

            @if ($cartCount > 0)
                <span class="absolute -top-1.5 -right-1.5 grid h-4 min-w-4 place-items-center bg-accent px-1 font-display text-[10px] leading-none text-on-accent">
                    {{ $cartCount }}
                </span>
            @endif
        </a>

        <a
            href="{{ route($account['route']) }}"
            class="icon-btn {{ request()->routeIs($account['route']) || request()->routeIs('dashboard.*') ? 'border-accent text-accent' : '' }}"
            aria-label="{{ $account['label'] }}"
        >
            <svg width="18" height="18" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <circle cx="10" cy="6.5" r="3" stroke="currentColor" stroke-width="1.5"/>
                <path d="M3.5 17a6.5 6.5 0 0 1 13 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </a>

        <button
            type="button"
            data-nav-toggle
            class="icon-btn xl:hidden"
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
        class="nav-panel"
    >
        @foreach ($navItems as $item)
            <a
                href="{{ $item['url'] ?? '#' }}"
                class="border-b border-line py-4 font-display text-2xl tracking-[0.1em] text-white capitalize {{ $menus->isActive($item) ? 'text-accent' : '' }}"
            >
                {{ $item['label'] }}
            </a>
        @endforeach

        <a href="{{ route($account['route']) }}" class="border-b border-line py-4 font-display text-2xl tracking-[0.1em] text-white capitalize {{ request()->routeIs($account['route']) ? 'text-accent' : '' }}">
            {{ $account['label'] }}
        </a>

        <a href="{{ route('contact.create') }}" class="btn btn--accent mt-6 self-start">
            Let's Talk
            <span class="btn__strip" aria-hidden="true"></span>
        </a>
    </div>

    <div id="site-search" data-search-panel class="search-overlay" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Search">
        <form method="GET" action="{{ route('search.index') }}" class="w-full max-w-2xl">
            <label for="site-search-input" class="field__label mb-4 block">Search the site</label>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <input
                    id="site-search-input"
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Services, products, articles…"
                    class="input !text-xl"
                    autocomplete="off"
                >

                <button type="submit" class="btn btn--accent shrink-0">
                    Search
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>
            </div>
        </form>

        <button type="button" data-search-close class="tag mt-8 hover:border-accent hover:text-accent">
            Close
        </button>
    </div>
</header>
