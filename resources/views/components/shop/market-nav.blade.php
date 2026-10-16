@props(['markets'])

<nav class="market-nav" data-market-nav aria-label="Browse markets">
    <div class="market-nav__bar">
        <button
            type="button"
            class="market-nav__all"
            data-market-toggle="all"
            aria-expanded="false"
            aria-controls="market-panel-all"
        >
            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true">
                <path d="M3 5h14M3 10h14M3 15h14"/>
            </svg>
            All Markets
        </button>

        <ul class="market-nav__list" data-market-list>
            @php($current = request()->route('category'))

            @foreach ($markets as $market)
                @php($isActive = $current instanceof \App\Models\Category && $current->isDescendantOf($market))

                <li>
                    @if ($market->children->isNotEmpty())
                        <button
                            type="button"
                            class="market-nav__item {{ $isActive ? 'is-active' : '' }}"
                            data-market-toggle="{{ $market->slug }}"
                            aria-expanded="false"
                            aria-controls="market-panel-{{ $market->slug }}"
                        >
                            {{ $market->name }}
                            <svg class="market-nav__chevron" width="12" height="12" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m5 8 5 5 5-5"/>
                            </svg>
                        </button>
                    @else
                        <a
                            href="{{ route('shop.category', $market) }}"
                            class="market-nav__item {{ $isActive ? 'is-active' : '' }}"
                        >
                            {{ $market->name }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>

    <div id="market-panels" class="market-nav__panels">
        <div
            id="market-panel-all"
            class="market-nav__panel"
            data-market-panel="all"
            hidden
        >
            <div class="shell">
                <div class="market-nav__grid">
                    @foreach ($markets as $market)
                        <div class="market-nav__node">
                            <a href="{{ route('shop.category', $market) }}" class="market-nav__node-link">
                                <span>{{ $market->name }}</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @foreach ($markets as $market)
            @if ($market->children->isNotEmpty())
                <div
                    id="market-panel-{{ $market->slug }}"
                    class="market-nav__panel"
                    data-market-panel="{{ $market->slug }}"
                    hidden
                >
                    <div class="shell">
                        <div class="market-nav__grid">
                            @foreach ($market->children as $child)
                                <div class="market-nav__node" data-market-node data-market-key="{{ $child->slug }}">
                                    <a href="{{ route('shop.category', $child) }}" class="market-nav__node-link">
                                        <span>{{ $child->name }}</span>
                                        @if ($child->children->isNotEmpty())
                                            <svg width="11" height="11" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="m8 5 5 5-5 5"/>
                                            </svg>
                                        @endif
                                    </a>
                                </div>
                            @endforeach

                            @foreach ($market->children as $child)
                                @if ($child->children->isNotEmpty())
                                    <div class="market-nav__sub" data-market-sub="{{ $child->slug }}" hidden>
                                        <a href="{{ route('shop.category', $child) }}" class="market-nav__sub-title">
                                            All {{ $child->name }}
                                        </a>

                                        <ul class="market-nav__sub-list">
                                            @foreach ($child->children as $grandchild)
                                                <li>
                                                    <a href="{{ route('shop.category', $grandchild) }}" class="market-nav__sub-link">
                                                        {{ $grandchild->name }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</nav>
