@php
    $policies = [
        ['label' => 'Privacy Policy', 'route' => 'legal.privacy'],
        ['label' => 'Terms & Conditions', 'route' => 'legal.terms'],
        ['label' => 'Cancellation & Refunds', 'route' => 'legal.refunds'],
        ['label' => 'Delivery Policy', 'route' => 'legal.delivery'],
    ];
@endphp

<section class="relative overflow-hidden pt-24 pb-14 lg:pb-28">
    <div
        class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_25%_20%,rgba(217,4,41,0.20),transparent_55%)]"
        aria-hidden="true"
    ></div>

    <div class="shell relative">
        <span class="eyebrow">Legal</span>

        <h1 class="max-w-4xl !text-4xl sm:!text-5xl">{!! $title !!}</h1>

        <p class="mt-6 max-w-2xl text-lg text-body">
            {{ $lead }}
        </p>

        <p class="mt-4 text-sm text-muted">Last updated {{ $updated }}</p>

        <nav class="mt-8 flex flex-wrap gap-2" aria-label="Legal pages">
            @foreach ($policies as $policy)
                <a
                    href="{{ route($policy['route']) }}"
                    class="tag {{ request()->routeIs($policy['route']) ? 'border-accent text-accent' : 'hover:border-accent hover:text-accent' }}"
                >
                    {{ $policy['label'] }}
                </a>
            @endforeach
        </nav>
    </div>

    <x-scroll-cue />
</section>
