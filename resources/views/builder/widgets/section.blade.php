@php
    $authoring = $authoring ?? false;
    $children = $children ?? [];

    $columns = max(1, min(4, (int) ($settings['columns'] ?? 1)));
    $stack = (bool) ($settings['stack'] ?? true);

    // Full class literals so Tailwind's scanner picks every variant up.
    $gaps = ['none' => 'gap-0', 'sm' => 'gap-4', 'md' => 'gap-8', 'lg' => 'gap-12', 'xl' => 'gap-16'];
    $aligns = ['stretch' => 'items-stretch', 'start' => 'items-start', 'center' => 'items-center', 'end' => 'items-end'];
    $stacked = [2 => 'md:grid-cols-2', 3 => 'md:grid-cols-3', 4 => 'md:grid-cols-4'];
    $inline = [1 => 'grid-cols-1', 2 => 'grid-cols-2', 3 => 'grid-cols-3', 4 => 'grid-cols-4'];

    $grid = ['grid', $gaps[$settings['gap'] ?? 'lg'] ?? 'gap-12', $aligns[$settings['align'] ?? 'stretch'] ?? 'items-stretch'];

    if ($stack && $columns > 1) {
        $grid[] = 'grid-cols-1';
        $grid[] = $stacked[$columns] ?? 'md:grid-cols-2';
    } else {
        $grid[] = $inline[$columns] ?? 'grid-cols-1';
    }
@endphp

{{-- The editor paints empty drop zones and fills them from its own block tree;
     the live page renders the children straight into the same columns. --}}
<x-builder.partials.section :settings="$settings" :container="true">
    <div class="shell">
        <div @class($grid)>
            @for ($column = 0; $column < $columns; $column++)
                @if ($authoring)
                    <div class="pb-col" data-pb-col="{{ $column }}"></div>
                @else
                    <div>{!! $children[$column] ?? '' !!}</div>
                @endif
            @endfor
        </div>
    </div>
</x-builder.partials.section>
