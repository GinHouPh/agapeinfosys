{{--
    A small bar chart for a daily series. No charting library: the series is
    a handful of numbers, and a few SVG rectangles beat a dependency.
--}}
@php
    $values = array_map(fn (array $day): int => (int) $day['total'], $series);
    $peak = $values === [] ? 0 : max($values);
    $width = 1000;
    $height = 180;
    $count = max(1, count($values));
    $slot = $width / $count;
    $barWidth = max(1.5, $slot * 0.62);
@endphp

@if ($peak > 0)
    <svg viewBox="0 0 {{ $width }} {{ $height }}" class="w-full" role="img" aria-label="Revenue by day">
        @foreach ($values as $index => $value)
            @php
                $barHeight = ($value / $peak) * ($height - 20);
                $x = $index * $slot + (($slot - $barWidth) / 2);
                $y = $height - $barHeight;
            @endphp
            <rect x="{{ round($x, 2) }}" y="{{ round($y, 2) }}" width="{{ round($barWidth, 2) }}"
                  height="{{ round($barHeight, 2) }}" fill="currentColor" opacity="{{ $index === $count - 1 ? 1 : 0.55 }}">
                <title>{{ $series[$index]['date'] }} — {{ $series[$index]['count'] }} orders</title>
            </rect>
        @endforeach
    </svg>
@else
    <p class="py-10 text-center text-sm text-muted">No revenue in this period.</p>
@endif
