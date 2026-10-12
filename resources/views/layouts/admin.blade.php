<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Dashboard') — {{ config('app.name') }} Admin</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="admin-shell">
    <div class="flex min-h-screen">
        <aside class="sticky top-0 hidden h-screen w-64 shrink-0 flex-col border-r border-line bg-ink lg:flex">
            <a href="{{ route('home') }}" class="flex h-20 items-center gap-3 border-b border-line px-6">
                <span class="grid h-9 w-9 place-items-center border border-line font-display text-base text-accent">A</span>
                <span class="font-display text-sm tracking-[0.2em] text-white uppercase">
                    Agape<span class="text-accent">Infosys</span>
                </span>
            </a>

            <nav class="flex-1 space-y-1 overflow-y-auto p-4" aria-label="Admin">
                <x-admin-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" label="Dashboard" />

                <p class="px-4 pt-6 pb-2 font-display text-[11px] tracking-[0.24em] text-muted uppercase">Content</p>
                <x-admin-link :href="route('admin.services.index')" :active="request()->routeIs('admin.services.*')" label="Services" />
                <x-admin-link :href="route('admin.projects.index')" :active="request()->routeIs('admin.projects.*')" label="Case Studies" />
                <x-admin-link :href="route('admin.posts.index')" :active="request()->routeIs('admin.posts.*')" label="Articles" />
                <x-admin-link :href="route('admin.testimonials.index')" :active="request()->routeIs('admin.testimonials.*')" label="Testimonials" />

                <p class="px-4 pt-6 pb-2 font-display text-[11px] tracking-[0.24em] text-muted uppercase">Inbox</p>
                <x-admin-link :href="route('admin.messages.index')" :active="request()->routeIs('admin.messages.*')" label="Messages" />

                <p class="px-4 pt-6 pb-2 font-display text-[11px] tracking-[0.24em] text-muted uppercase">Commerce</p>
                <x-admin-link :href="route('admin.products.index')" :active="request()->routeIs('admin.products.*')" label="Products" />
                <x-admin-link :href="route('admin.plans.index')" :active="request()->routeIs('admin.plans.*')" label="Plans" />
                <x-admin-link :href="route('admin.orders.index')" :active="request()->routeIs('admin.orders.*')" label="Orders" />
                <x-admin-link :href="route('admin.subscriptions.index')" :active="request()->routeIs('admin.subscriptions.*')" label="Subscriptions" />
                <x-admin-link :href="route('admin.gateways.index')" :active="request()->routeIs('admin.gateways.*')" label="Gateways" />
            </nav>

            <div class="border-t border-line p-4">
                <p class="px-4 pb-3 text-sm text-muted">{{ auth()->user()?->name }}</p>

                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="admin-nav-link w-full text-left">
                        <span class="h-1.5 w-1.5 rounded-full bg-accent" aria-hidden="true"></span>
                        Sign out
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-40 flex h-20 items-center justify-between gap-4 border-b border-line bg-surface/95 px-5 backdrop-blur sm:px-8">
                <div class="flex items-center gap-4">
                    <a href="{{ route('home') }}" class="lg:hidden">
                        <span class="grid h-9 w-9 place-items-center border border-line font-display text-base text-accent">A</span>
                    </a>
                    <h1 class="font-display text-lg tracking-[0.1em] text-white capitalize">
                        @yield('heading', 'Dashboard')
                    </h1>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="tag hover:border-accent hover:text-accent">
                        View site
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
