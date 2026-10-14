@php
    $authoring = $authoring ?? false;
    $children = $children ?? [];

    $stack = (bool) ($settings['stack'] ?? true);

    // Full class literals so Tailwind's scanner picks every variant up. Each
    // tier owns a complete list rather than a prefix that gets concatenated
    // onto a value, because a class assembled at runtime is invisible to the
    // scanner and would silently ship as no CSS at all.
    $cols = [
        'mobile' => [1 => 'grid-cols-1', 2 => 'grid-cols-2', 3 => 'grid-cols-3', 4 => 'grid-cols-4'],
        'tablet' => [1 => 'md:grid-cols-1', 2 => 'md:grid-cols-2', 3 => 'md:grid-cols-3', 4 => 'md:grid-cols-4'],
        'desktop' => [1 => 'lg:grid-cols-1', 2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3', 4 => 'lg:grid-cols-4'],
    ];
    $gaps = [
        'mobile' => ['none' => 'gap-0', 'sm' => 'gap-4', 'md' => 'gap-8', 'lg' => 'gap-12', 'xl' => 'gap-16'],
        'tablet' => ['none' => 'md:gap-0', 'sm' => 'md:gap-4', 'md' => 'md:gap-8', 'lg' => 'md:gap-12', 'xl' => 'md:gap-16'],
        'desktop' => ['none' => 'lg:gap-0', 'sm' => 'lg:gap-4', 'md' => 'lg:gap-8', 'lg' => 'lg:gap-12', 'xl' => 'lg:gap-16'],
    ];
    $aligns = [
        'mobile' => ['stretch' => 'items-stretch', 'start' => 'items-start', 'center' => 'items-center', 'end' => 'items-end'],
        'tablet' => ['stretch' => 'md:items-stretch', 'start' => 'md:items-start', 'center' => 'md:items-center', 'end' => 'md:items-end'],
        'desktop' => ['stretch' => 'lg:items-stretch', 'start' => 'lg:items-start', 'center' => 'lg:items-center', 'end' => 'lg:items-end'],
    ];

    // Gap and alignment fall back to desktop when a tier has no override of
    // its own. Columns fall back to the stack toggle instead: that toggle *is*
    // the mobile default, and an explicit mobile count simply outranks it.
    $desktopCols = max(1, min(4, (int) ($settings['columns'] ?? 1)));
    $tabletCols = max(1, min(4, (int) (\App\Builder\Device::read($settings, 'columns', 'tablet') ?? $desktopCols)));
    $mobileOverride = \App\Builder\Device::read($settings, 'columns', 'mobile');
    $mobileCols = $mobileOverride === null
        ? ($stack && $desktopCols > 1 ? 1 : $desktopCols)
        : max(1, min(4, (int) $mobileOverride));

    $desktopGap = \App\Builder\Device::read($settings, 'gap', 'desktop') ?? 'lg';
    $tabletGap = \App\Builder\Device::read($settings, 'gap', 'tablet') ?? $desktopGap;
    $mobileGap = \App\Builder\Device::read($settings, 'gap', 'mobile') ?? $desktopGap;

    $desktopAlign = \App\Builder\Device::read($settings, 'align', 'desktop') ?? 'stretch';
    $tabletAlign = \App\Builder\Device::read($settings, 'align', 'tablet') ?? $desktopAlign;
    $mobileAlign = \App\Builder\Device::read($settings, 'align', 'mobile') ?? $desktopAlign;

    // Mobile-first: the unprefixed class carries the mobile value, and each
    // wider tier is only written when it differs from the tier that already
    // covers those widths. Emitting a tier that matches the one below it would
    // just restate the same declaration further down the cascade.
    $emit = static function (array $map, string $mobile, string $tablet, string $desktop): array {
        $classes = [$map['mobile'][$mobile]];

        if ($tablet !== $mobile) {
            $classes[] = $map['tablet'][$tablet];
        }

        if ($desktop !== $tablet) {
            $classes[] = $map['desktop'][$desktop];
        }

        return $classes;
    };

    $grid = array_merge(
        ['grid'],
        $emit($gaps, $mobileGap, $tabletGap, $desktopGap),
        $emit($aligns, $mobileAlign, $tabletAlign, $desktopAlign),
        $emit($cols, (string) $mobileCols, (string) $tabletCols, (string) $desktopCols),
    );
@endphp

{{-- The editor paints empty drop zones and fills them from its own block tree;
     the live page renders the children straight into the same columns. The
     desktop count owns the structure — it is the count `columnsOf()` quotes in
     the panel — while the smaller tiers only restyle how those columns wrap. --}}
<x-builder.partials.section :settings="$settings" :container="true">
    <div class="shell">
        <div @class($grid)>
            @for ($column = 0; $column < $desktopCols; $column++)
                @if ($authoring)
                    <div class="pb-col" data-pb-col="{{ $column }}"></div>
                @else
                    <div>{!! $children[$column] ?? '' !!}</div>
                @endif
            @endfor
        </div>
    </div>
</x-builder.partials.section>
