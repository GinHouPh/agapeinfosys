<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Dashboard') — {{ config('app.name') }} Admin</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        // Apply the persisted sidebar state before first paint to avoid a flash.
        try {
            if (localStorage.getItem('admin-sidebar-collapsed') === '1') {
                document.documentElement.setAttribute('data-admin-sidebar', 'collapsed');
            }
        } catch (e) {}
    </script>

    @stack('head')
</head>
<body class="admin-shell">
    <div class="flex min-h-screen">
        <aside data-admin-sidebar class="admin-sidebar sticky top-0 hidden h-screen w-64 shrink-0 flex-col border-r border-line bg-ink lg:flex">
            <a href="{{ route('home') }}" class="admin-sidebar__brand flex h-20 items-center gap-3 border-b border-line px-6" title="AgapeInfosys">
                <x-brand text-class="admin-sidebar__text font-display text-sm tracking-[0.2em] text-white uppercase" />
            </a>

            <nav class="admin-sidebar__nav flex-1 space-y-1 overflow-y-auto p-4" aria-label="Admin">
                <x-admin-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" label="Dashboard" icon="dashboard" permission="dashboard.view" />
                <x-admin-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')" label="Reports" icon="reports" permission="dashboard.view" />
                <x-admin-link :href="route('admin.traffic.index')" :active="request()->routeIs('admin.traffic.*')" label="Traffic" icon="traffic" permission="dashboard.view" />

                <p class="admin-sidebar__heading px-4 pt-6 pb-2 font-display text-[11px] tracking-[0.24em] text-muted uppercase">Content</p>
                <x-admin-link :href="route('admin.pages.index')" :active="request()->routeIs('admin.pages.*')" label="Pages" icon="pages" permission="pages.manage" />
                <x-admin-link :href="route('admin.navigation.index')" :active="request()->routeIs('admin.navigation.*')" label="Navigation" icon="navigation" permission="navigation.manage" />
                <x-admin-link :href="route('admin.services.index')" :active="request()->routeIs('admin.services.*')" label="Services" icon="services" permission="services.manage" />
                <x-admin-link :href="route('admin.projects.index')" :active="request()->routeIs('admin.projects.*')" label="Case Studies" icon="cases" permission="projects.manage" />
                <x-admin-link :href="route('admin.posts.index')" :active="request()->routeIs('admin.posts.*')" label="Articles" icon="articles" permission="posts.manage" />
                <x-admin-link :href="route('admin.testimonials.index')" :active="request()->routeIs('admin.testimonials.*')" label="Testimonials" icon="testimonials" permission="testimonials.manage" />
                <x-admin-link :href="route('admin.forms.index')" :active="request()->routeIs('admin.forms.*')" label="Forms" icon="forms" permission="forms.manage" />
                <x-admin-link :href="route('admin.media.index')" :active="request()->routeIs('admin.media.*')" label="Media Library" icon="media" permission="media.manage" />
                <x-admin-link :href="route('admin.locales.index')" :active="request()->routeIs('admin.locales.*')" label="Languages" icon="locales" permission="locales.manage" />
                <x-admin-link :href="route('admin.collections.index')" :active="request()->routeIs('admin.collections.*')" label="Collections" icon="collections" permission="collections.manage" />
                <x-admin-link :href="route('admin.design.index')" :active="request()->routeIs('admin.design.*')" label="Design System" icon="design" permission="design.manage" />
                <x-admin-link :href="route('admin.seo.redirects')" :active="request()->routeIs('admin.seo.*')" label="SEO & Redirects" icon="seo" permission="seo.manage" />

                <p class="admin-sidebar__heading px-4 pt-6 pb-2 font-display text-[11px] tracking-[0.24em] text-muted uppercase">Showcase</p>
                <x-admin-link :href="route('admin.showcase-projects.index')" :active="request()->routeIs('admin.showcase-projects.*')" label="Projects" icon="showcase" permission="showcase.manage" />

                <p class="admin-sidebar__heading px-4 pt-6 pb-2 font-display text-[11px] tracking-[0.24em] text-muted uppercase">Inbox</p>
                <x-admin-link :href="route('admin.messages.index')" :active="request()->routeIs('admin.messages.*')" label="Messages" icon="messages" permission="messages.manage" />
                <x-admin-link :href="route('admin.submissions.index')" :active="request()->routeIs('admin.submissions.*')" label="Submissions" icon="submissions" permission="submissions.manage" />

                <p class="admin-sidebar__heading px-4 pt-6 pb-2 font-display text-[11px] tracking-[0.24em] text-muted uppercase">Clients</p>
                <x-admin-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" label="Client Accounts" icon="users" permission="users.manage" />
                <x-admin-link :href="route('admin.roles.index')" :active="request()->routeIs('admin.roles.*')" label="Roles & Access" icon="roles" permission="roles.manage" />
                <x-admin-link :href="route('admin.activity.index')" :active="request()->routeIs('admin.activity.*')" label="Activity Log" icon="activity" permission="activity.view" />

                <p class="admin-sidebar__heading px-4 pt-6 pb-2 font-display text-[11px] tracking-[0.24em] text-muted uppercase">Commerce</p>
                <x-admin-link :href="route('admin.products.index')" :active="request()->routeIs('admin.products.*')" label="Products" icon="products" permission="products.manage" />
                <x-admin-link :href="route('admin.categories.index')" :active="request()->routeIs('admin.categories.*')" label="Categories" icon="categories" permission="categories.manage" />
                <x-admin-link :href="route('admin.plans.index')" :active="request()->routeIs('admin.plans.*')" label="Plans" icon="plans" permission="plans.manage" />
                <x-admin-link :href="route('admin.orders.index')" :active="request()->routeIs('admin.orders.*')" label="Orders" icon="orders" permission="orders.view" />
                <x-admin-link :href="route('admin.shop-orders.index')" :active="request()->routeIs('admin.shop-orders.*')" label="Shop Orders" icon="shop-orders" permission="orders.view" />
                <x-admin-link :href="route('admin.subscriptions.index')" :active="request()->routeIs('admin.subscriptions.*')" label="Subscriptions" icon="subscriptions" permission="subscriptions.view" />
                <x-admin-link :href="route('admin.gateways.index')" :active="request()->routeIs('admin.gateways.*')" label="Gateways" icon="gateways" permission="gateways.manage" />

                <p class="admin-sidebar__heading px-4 pt-6 pb-2 font-display text-[11px] tracking-[0.24em] text-muted uppercase">Settings</p>
                <x-admin-link :href="route('admin.profile.edit')" :active="request()->routeIs('admin.profile.*')" label="My Profile" icon="profile" />
                <x-admin-link :href="route('admin.business.edit')" :active="request()->routeIs('admin.business.*')" label="Business Details" icon="business" permission="business.manage" />
                <x-admin-link :href="route('admin.system.index')" :active="request()->routeIs('admin.system.*')" label="System" icon="system" permission="system.manage" />
            </nav>

            <div class="border-t border-line p-4">
                <a href="{{ route('admin.profile.edit') }}" class="admin-nav-link" title="My profile">
                    <x-admin-icon name="profile" />
                    <span class="admin-nav-link__label">{{ auth()->user()?->name }}</span>
                </a>

                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="admin-nav-link w-full text-left" title="Sign out">
                        <x-admin-icon name="sign-out" />
                        <span class="admin-nav-link__label">Sign out</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-40 flex h-20 items-center justify-between gap-4 border-b border-line bg-surface/95 px-5 backdrop-blur sm:px-8">
                <div class="flex items-center gap-4">
                    <button type="button" data-admin-sidebar-toggle class="admin-icon-btn hidden lg:grid" aria-label="Toggle sidebar" aria-expanded="true">
                        <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="M2 4h12M2 8h12M2 12h12" stroke-linecap="round"/>
                        </svg>
                    </button>
                    <a href="{{ route('home') }}" class="lg:hidden">
                        <x-brand :show-text="false" />
                    </a>
                    <h1 class="font-display text-lg tracking-[0.1em] text-white capitalize">
                        @yield('heading', 'Dashboard')
                    </h1>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="tag hover:border-accent hover:text-accent">
                        View site
                    </a>
                    <a href="{{ route('admin.profile.edit') }}" class="tag hover:border-accent hover:text-accent" @class(['!border-accent !text-accent' => request()->routeIs('admin.profile.*')])>
                        My Profile
                    </a>
                    <form action="{{ route('admin.logout') }}" method="POST" class="lg:hidden">
                        @csrf
                        <button type="submit" class="tag hover:border-accent hover:text-accent">Sign out</button>
                    </form>
                </div>
            </header>

            <main class="flex-1 px-5 py-8 sm:px-8">
                @if (session('status'))
                    <p class="form-status" role="status">{{ session('status') }}</p>
                @endif

                @if (session('error'))
                    <p class="form-status" role="alert">{{ session('error') }}</p>
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

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
