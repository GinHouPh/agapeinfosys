@props(['paginator'])

@php
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();
    $window = 2;
    $pages = array_values(array_filter(
        range(max(1, $current - $window), min($last, $current + $window)),
        fn (int $page) => $page >= 1 && $page <= $last,
    ));
@endphp

@if ($last > 1)
    <nav class="mt-14 flex flex-wrap items-center justify-center gap-3" aria-label="Pagination">
        @if ($current > 1)
            <a href="{{ $paginator->url($current - 1) }}" class="tag hover:border-accent hover:text-accent">
                &larr; Prev
            </a>
        @endif

        @foreach ($pages as $page)
            <a
                href="{{ $paginator->url($page) }}"
                class="tag {{ $page === $current ? 'border-accent text-accent' : 'hover:border-accent hover:text-accent' }}"
                @if ($page === $current) aria-current="page" @endif
            >
                {{ $page }}
            </a>
        @endforeach

        @if ($current < $last)
            <a href="{{ $paginator->url($current + 1) }}" class="tag hover:border-accent hover:text-accent">
                Next &rarr;
            </a>
        @endif
    </nav>
@endif
