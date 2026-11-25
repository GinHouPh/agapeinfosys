@extends('layouts.app')

@section('title', $product->name)
@section('description', $product->description ?: 'Choose a plan for '.$product->name.' and check out securely through Agape Infosys.')

@section('content')
    <x-shop.market-nav :markets="$markets" />

    <section class="section">
        <div class="shell">
            <nav class="mb-8 flex flex-wrap items-center gap-2 font-display text-xs tracking-[0.2em] text-muted uppercase" aria-label="Breadcrumb">
                <a href="{{ route('shop.index') }}" class="transition-colors duration-300 hover:text-accent">Marketplace</a>

                @foreach ($product->category?->ancestors() ?? collect() as $ancestor)
                    <span aria-hidden="true">/</span>
                    <a href="{{ route('shop.category', $ancestor) }}" class="transition-colors duration-300 hover:text-accent">
                        {{ $ancestor->name }}
                    </a>
                @endforeach

                @if ($product->category)
                    <span aria-hidden="true">/</span>
                    <a href="{{ route('shop.category', $product->category) }}" class="transition-colors duration-300 hover:text-accent">
                        {{ $product->category->name }}
                    </a>
                @endif

                <span aria-hidden="true">/</span>
                <span class="text-white">{{ $product->name }}</span>
            </nav>

            <div class="flex flex-wrap items-start justify-between gap-6 border-b border-line pb-8">
                <div class="flex flex-wrap items-start gap-6">
                    @if ($product->imageUrl())
                        <img
                            src="{{ $product->imageUrl() }}"
                            alt="{{ $product->name }}"
                            class="h-20 w-20 shrink-0 border border-line object-cover sm:h-24 sm:w-24"
                        >
                    @else
                        <span class="grid h-20 w-20 shrink-0 place-items-center border border-line font-display text-4xl text-accent sm:h-24 sm:w-24" aria-hidden="true">
                            {{ strtoupper(substr($product->name, 0, 1)) }}
                        </span>
                    @endif

                    <div class="min-w-0 flex-1">
                        @if ($product->category)
                            <a href="{{ route('shop.category', $product->category) }}" class="eyebrow">
                                {{ $product->category->name }}
                            </a>
                        @endif

                        <h1 class="text-4xl text-white sm:text-5xl">{{ $product->name }}</h1>

                        <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-muted">
                            @if ($product->domain)
                                <a
                                    href="https://{{ $product->domain }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-2 transition-colors duration-300 hover:text-accent"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-accent" aria-hidden="true"></span>
                                    {{ $product->domain }}
                                </a>
                            @endif

                            @if ($plans->isNotEmpty())
                                <span class="tag">{{ $plans->count() }} {{ \Illuminate\Support\Str::plural('plan', $plans->count()) }}</span>
                            @endif

                            <span class="tag">Instant digital delivery</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 grid gap-12 lg:grid-cols-[minmax(0,1fr)_380px] lg:gap-16">
                <div class="min-w-0">
                    {{-- Hero Showcase & Primary Action Bar --}}
                    <div class="border border-line bg-surface">
                        @if ($product->imageUrl())
                            <div class="relative aspect-[16/9] overflow-hidden bg-ink">
                                <img
                                    src="{{ $product->imageUrl() }}"
                                    alt="{{ $product->name }} preview"
                                    class="h-full w-full object-cover"
                                >
                            </div>
                        @else
                            <div class="grid aspect-[16/9] place-items-center bg-ink/60 p-8 text-center">
                                <div>
                                    <span class="font-display text-6xl text-accent sm:text-7xl">{{ strtoupper(substr($product->name, 0, 2)) }}</span>
                                    <p class="mt-3 font-display text-xs tracking-[0.24em] text-muted uppercase">{{ $product->category?->name ?? 'Digital Product' }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="flex flex-wrap items-center justify-between gap-4 border-t border-line px-6 py-4">
                            <div class="flex flex-wrap items-center gap-3">
                                @if ($product->domain)
                                    <a
                                        href="https://{{ $product->domain }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="btn btn--accent"
                                    >
                                        Live Preview
                                        <span class="btn__strip" aria-hidden="true"></span>
                                    </a>
                                @endif

                                @if ($plans->isNotEmpty())
                                    <a href="#plans" class="btn btn--ghost">
                                        Compare Plans
                                        <span class="btn__strip" aria-hidden="true"></span>
                                    </a>
                                @endif
                            </div>

                            @if ($product->updated_at)
                                <span class="font-display text-xs tracking-[0.2em] text-muted uppercase">
                                    Updated {{ $product->updated_at->format('M d, Y') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Section Navigation Tabs --}}
                    <nav class="mt-10 flex flex-wrap gap-2 border-b border-line pb-4" aria-label="Product sections">
                        <a href="#overview" class="tag border-accent text-accent">Overview</a>
                        @if ($plans->isNotEmpty())
                            <a href="#plans" class="tag hover:border-accent hover:text-accent">Plans &amp; Licensing ({{ $plans->count() }})</a>
                        @endif
                        <a href="#support-delivery" class="tag hover:border-accent hover:text-accent">Support &amp; Delivery</a>
                    </nav>

                    {{-- Overview --}}
                    <section id="overview" class="mt-10 scroll-mt-28">
                        <h2 class="text-2xl text-white">About {{ $product->name }}</h2>

                        @if ($product->description)
                            <div class="prose-dark mt-5 max-w-3xl">
                                <p>{{ $product->description }}</p>
                            </div>
                        @else
                            <p class="mt-4 text-sm text-muted">Full product documentation and release notes are provided in your client dashboard after purchase.</p>
                        @endif

                        <div class="mt-8 grid gap-4 sm:grid-cols-3">
                            <div class="admin-card !p-5">
                                <p class="font-display text-xs tracking-[0.2em] text-accent uppercase">01 / Licensing</p>
                                <h3 class="mt-2 font-display text-lg text-white">Verified Key</h3>
                                <p class="mt-2 text-sm text-muted">Instant license key generation and domain management from your client dashboard.</p>
                            </div>

                            <div class="admin-card !p-5">
                                <p class="font-display text-xs tracking-[0.2em] text-accent uppercase">02 / Delivery</p>
                                <h3 class="mt-2 font-display text-lg text-white">Immediate Access</h3>
                                <p class="mt-2 text-sm text-muted">Downloads and entitlements unlock automatically as soon as checkout completes.</p>
                            </div>

                            <div class="admin-card !p-5">
                                <p class="font-display text-xs tracking-[0.2em] text-accent uppercase">03 / Updates</p>
                                <h3 class="mt-2 font-display text-lg text-white">Maintained Releases</h3>
                                <p class="mt-2 text-sm text-muted">Active plans include release updates, security patches, and email support.</p>
                            </div>
                        </div>
                    </section>

                    {{-- Plans & Licensing Grid --}}
                    <section id="plans" class="mt-14 scroll-mt-28">
                        @if ($plans->isNotEmpty())
                            <h2 class="text-2xl text-white">Choose your plan</h2>
                            <p class="mt-2 text-sm text-muted">Pick a plan below — it is added to your cart and activated after checkout.</p>

                            <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach ($plans as $plan)
                                    @php
                                        $inCart = $cartLines->contains(fn (array $line): bool => $line['plan']->getKey() === $plan->getKey());
                                    @endphp

                                    <article class="admin-card flex flex-col !p-6 {{ $inCart ? 'ring-1 ring-accent' : '' }}">
                                        <h3 class="font-display text-xl tracking-wide text-white">{{ $plan->name }}</h3>

                                        <p class="mt-3 font-display text-3xl text-accent">{{ $plan->formattedPrice() }}</p>

                                        <p class="mt-2 text-sm text-muted">
                                            @if ($plan->isRecurring())
                                                Billed every {{ \App\Enums\BillingType::intervalLabel($plan->interval_days ?? 30) }}
                                            @elseif ($plan->isLifetime())
                                                One-time — lifetime access
                                            @else
                                                One-time — {{ $plan->validity_days ?? 365 }} days of access
                                            @endif
                                        </p>

                                        @if ($plan->description)
                                            <p class="mt-4 flex-1 text-sm text-body">{{ $plan->description }}</p>
                                        @else
                                            <div class="flex-1"></div>
                                        @endif

                                        @if ($inCart)
                                            <a href="{{ route('shop.cart.index') }}" class="btn btn--ghost mt-6 w-full justify-center">
                                                In cart — view cart
                                                <span class="btn__strip" aria-hidden="true"></span>
                                            </a>
                                        @else
                                            <form action="{{ route('shop.cart.store') }}" method="POST" class="mt-6">
                                                @csrf
                                                <input type="hidden" name="product" value="{{ $product->slug }}">
                                                <input type="hidden" name="plan" value="{{ $plan->code }}">
                                                <button type="submit" class="btn btn--accent w-full justify-center">
                                                    Add to cart
                                                    <span class="btn__strip" aria-hidden="true"></span>
                                                </button>
                                            </form>
                                        @endif
                                    </article>
                                @endforeach
                            </div>

                            @error('plan')
                                <p class="field__error mt-6">{{ $message }}</p>
                            @enderror
                        @else
                            <p class="text-muted">No plans are available for {{ $product->name }} right now. Please check back soon.</p>
                        @endif
                    </section>

                    {{-- Support & Delivery --}}
                    <section id="support-delivery" class="mt-14 scroll-mt-28 border-t border-line pt-10">
                        <h2 class="text-2xl text-white">Support &amp; Delivery</h2>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <div class="border border-line bg-surface p-6">
                                <h3 class="font-display text-lg text-white">What is included</h3>
                                <ul class="mt-4 space-y-2.5 text-sm text-body">
                                    <li class="flex items-start gap-3">
                                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                                        <span>Verified release archive &amp; checksums</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                                        <span>License key &amp; domain binding controls</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                                        <span>Bug fixes &amp; compatibility updates during active term</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="border border-line bg-surface p-6">
                                <h3 class="font-display text-lg text-white">Delivery &amp; Refunds</h3>
                                <p class="mt-4 text-sm text-body">
                                    Access is provisioned immediately upon payment confirmation. Review our
                                    <a href="{{ route('legal.delivery') }}" class="text-accent underline-offset-4 hover:underline">delivery policy</a>
                                    and
                                    <a href="{{ route('legal.refunds') }}" class="text-accent underline-offset-4 hover:underline">refund terms</a>
                                    for full details.
                                </p>
                            </div>
                        </div>
                    </section>
                </div>

                {{-- Right Sticky Sidebar: Pricing & License + Technical Metadata --}}
                <aside class="space-y-6 lg:sticky lg:top-28 lg:self-start">
                    @if ($plans->isNotEmpty())
                        @php
                            $defaultPlan = $plans->first();
                            $defaultInCart = $cartLines->contains(fn (array $line): bool => $line['plan']->getKey() === $defaultPlan->getKey());
                        @endphp

                        <div class="admin-card !p-6" data-license-sidebar>
                            <div class="flex items-baseline justify-between gap-4 border-b border-line pb-4">
                                <h2 class="font-display text-sm tracking-[0.2em] text-muted uppercase">Pricing &amp; License</h2>
                                <span class="font-display text-3xl text-accent" data-sidebar-price>{{ $defaultPlan->formattedPrice() }}</span>
                            </div>

                            <div class="mt-5">
                                <label for="sidebar-plan-select" class="field__label">Select License / Plan</label>
                                <select id="sidebar-plan-select" class="input mt-2" data-sidebar-plan-select>
                                    @foreach ($plans as $plan)
                                        @php
                                            $planInCart = $cartLines->contains(fn (array $line): bool => $line['plan']->getKey() === $plan->getKey());
                                            $cadence = $plan->isRecurring()
                                                ? 'Billed every '.\App\Enums\BillingType::intervalLabel($plan->interval_days ?? 30)
                                                : ($plan->isLifetime() ? 'One-time — lifetime access' : 'One-time — '.($plan->validity_days ?? 365).' days of access');
                                        @endphp
                                        <option
                                            value="{{ $plan->code }}"
                                            data-price="{{ $plan->formattedPrice() }}"
                                            data-cadence="{{ $cadence }}"
                                            data-description="{{ $plan->description ?? '' }}"
                                            data-in-cart="{{ $planInCart ? '1' : '0' }}"
                                        >
                                            {{ $plan->name }} — {{ $plan->formattedPrice() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <p class="mt-3 text-xs tracking-[0.12em] text-muted uppercase" data-sidebar-cadence>
                                @if ($defaultPlan->isRecurring())
                                    Billed every {{ \App\Enums\BillingType::intervalLabel($defaultPlan->interval_days ?? 30) }}
                                @elseif ($defaultPlan->isLifetime())
                                    One-time — lifetime access
                                @else
                                    One-time — {{ $defaultPlan->validity_days ?? 365 }} days of access
                                @endif
                            </p>

                            <p class="mt-3 text-sm text-body {{ $defaultPlan->description ? '' : 'hidden' }}" data-sidebar-description>
                                {{ $defaultPlan->description }}
                            </p>

                            <ul class="mt-5 space-y-2 border-t border-line pt-4 text-sm text-body">
                                <li class="flex items-center gap-2.5">
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                                    <span>Quality checked by Agape Infosys</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                                    <span>Future updates &amp; release downloads</span>
                                </li>
                                <li class="flex items-center gap-2.5">
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                                    <span>Client dashboard license management</span>
                                </li>
                            </ul>

                            <form action="{{ route('shop.cart.store') }}" method="POST" class="mt-6 {{ $defaultInCart ? 'hidden' : '' }}" data-sidebar-cart-form>
                                @csrf
                                <input type="hidden" name="product" value="{{ $product->slug }}">
                                <input type="hidden" name="plan" value="{{ $defaultPlan->code }}" data-sidebar-plan-input>
                                <button type="submit" class="btn btn--accent w-full justify-center">
                                    Add to cart
                                    <span class="btn__strip" aria-hidden="true"></span>
                                </button>
                            </form>

                            <a href="{{ route('shop.cart.index') }}" class="btn btn--ghost mt-6 w-full justify-center {{ $defaultInCart ? '' : 'hidden' }}" data-sidebar-in-cart-link>
                                In cart — view cart
                                <span class="btn__strip" aria-hidden="true"></span>
                            </a>

                            <a href="{{ route('shop.cart.index') }}" class="btn btn--ghost mt-3 w-full justify-center">
                                View cart ({{ $cartLines->count() }})
                                <span class="btn__strip" aria-hidden="true"></span>
                            </a>
                        </div>
                    @endif

                    <div class="admin-card !p-6">
                        <h2 class="font-display text-sm tracking-[0.2em] text-muted uppercase">Product Details</h2>

                        <dl class="mt-5 space-y-4 text-sm">
                            @if ($product->category)
                                <div class="flex items-start justify-between gap-4 border-b border-line pb-3">
                                    <dt class="text-muted">Category</dt>
                                    <dd class="text-right text-white">
                                        <a href="{{ route('shop.category', $product->category) }}" class="transition-colors duration-300 hover:text-accent">
                                            {{ $product->category->name }}
                                        </a>
                                    </dd>
                                </div>
                            @endif

                            @if ($product->domain)
                                <div class="flex items-start justify-between gap-4 border-b border-line pb-3">
                                    <dt class="text-muted">Website</dt>
                                    <dd class="text-right text-white">{{ $product->domain }}</dd>
                                </div>
                            @endif

                            @if ($product->updated_at)
                                <div class="flex items-start justify-between gap-4 border-b border-line pb-3">
                                    <dt class="text-muted">Last Update</dt>
                                    <dd class="text-right text-white">{{ $product->updated_at->format('d M Y') }}</dd>
                                </div>
                            @endif

                            <div class="flex items-start justify-between gap-4 border-b border-line pb-3">
                                <dt class="text-muted">Delivery</dt>
                                <dd class="text-right text-white">Instant Digital</dd>
                            </div>

                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-muted">Plans</dt>
                                <dd class="text-right text-white">{{ $plans->count() }}</dd>
                            </div>
                        </dl>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="section section--surface border-t border-line">
            <div class="shell">
                <x-section-head
                    title="Related products"
                    subtitle="More from the same corner of the marketplace."
                    mark="More"
                />

                <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <article class="admin-card flex flex-col !p-6">
                            <div class="flex items-start gap-4">
                                @if ($item->imageUrl())
                                    <img src="{{ $item->imageUrl() }}" alt="{{ $item->name }}" class="h-14 w-14 shrink-0 border border-line object-cover">
                                @else
                                    <span class="grid h-14 w-14 shrink-0 place-items-center border border-line font-display text-xl text-accent" aria-hidden="true">
                                        {{ strtoupper(substr($item->name, 0, 1)) }}
                                    </span>
                                @endif

                                <h3 class="font-display text-lg tracking-wide text-white">{{ $item->name }}</h3>
                            </div>

                            @if ($item->description)
                                <p class="mt-4 flex-1 text-sm text-body">{{ \Illuminate\Support\Str::limit($item->description, 120) }}</p>
                            @else
                                <div class="flex-1"></div>
                            @endif

                            <div class="mt-6 flex items-center justify-between gap-4 border-t border-line pt-4">
                                <span class="text-sm text-muted">
                                    @if ($item->plans->isNotEmpty())
                                        From {{ $item->plans->sortBy('price')->first()->formattedPrice() }}
                                    @else
                                        Plans coming soon
                                    @endif
                                </span>

                                <a href="{{ route('shop.show', $item) }}" class="tag hover:border-accent hover:text-accent">
                                    View plans
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <script>
        (function () {
            var select = document.querySelector('[data-sidebar-plan-select]');
            if (!select) {
                return;
            }

            var priceEl = document.querySelector('[data-sidebar-price]');
            var cadenceEl = document.querySelector('[data-sidebar-cadence]');
            var descEl = document.querySelector('[data-sidebar-description]');
            var planInput = document.querySelector('[data-sidebar-plan-input]');
            var cartForm = document.querySelector('[data-sidebar-cart-form]');
            var inCartLink = document.querySelector('[data-sidebar-in-cart-link]');

            select.addEventListener('change', function () {
                var option = select.options[select.selectedIndex];
                if (!option) {
                    return;
                }

                if (priceEl) {
                    priceEl.textContent = option.dataset.price || '';
                }
                if (cadenceEl) {
                    cadenceEl.textContent = option.dataset.cadence || '';
                }
                if (descEl) {
                    var desc = option.dataset.description || '';
                    descEl.textContent = desc;
                    descEl.classList.toggle('hidden', desc === '');
                }
                if (planInput) {
                    planInput.value = option.value;
                }

                var isInCart = option.dataset.inCart === '1';
                if (cartForm) {
                    cartForm.classList.toggle('hidden', isInCart);
                }
                if (inCartLink) {
                    inCartLink.classList.toggle('hidden', !isInCart);
                }
            });
        })();
    </script>
@endsection
