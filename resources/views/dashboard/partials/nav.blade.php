@php
    $tabs = [
        ['label' => 'Overview', 'route' => 'dashboard.index', 'active' => 'dashboard.index'],
        ['label' => 'Orders', 'route' => 'dashboard.orders.index', 'active' => 'dashboard.orders.*'],
        ['label' => 'Subscriptions', 'route' => 'dashboard.subscriptions.index', 'active' => 'dashboard.subscriptions.*'],
        ['label' => 'Access', 'route' => 'dashboard.access.index', 'active' => 'dashboard.access.*'],
        ['label' => 'Profile', 'route' => 'dashboard.profile.edit', 'active' => 'dashboard.profile.*'],
    ];
@endphp

<section class="relative overflow-hidden pt-40 pb-14">
    <div
        class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_25%_20%,rgba(217,4,41,0.20),transparent_55%)]"
        aria-hidden="true"
    ></div>

    <div class="shell relative">
        <span class="eyebrow">Client dashboard</span>

        <h1 class="max-w-4xl !text-4xl sm:!text-5xl">
            {{ $title }}
        </h1>

        <p class="mt-6 max-w-2xl text-lg text-body">
            {{ $lead }}
        </p>
    </div>
</section>

<div class="shell pb-24">
    <nav class="mb-10 flex flex-wrap items-center gap-2 border-b border-line pb-5" aria-label="Client dashboard">
        @foreach ($tabs as $tab)
            <a
                href="{{ route($tab['route']) }}"
                class="tag {{ request()->routeIs($tab['active']) ? 'border-accent text-accent' : 'hover:border-accent hover:text-accent' }}"
            >
                {{ $tab['label'] }}
            </a>
        @endforeach

        <span class="grow"></span>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="tag hover:border-accent hover:text-accent">Sign out</button>
        </form>
    </nav>

    @if (session('status'))
        <p class="form-status mb-8" role="status">{{ session('status') }}</p>
    @endif
