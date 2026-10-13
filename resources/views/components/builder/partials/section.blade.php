@props([
    'settings' => [],
    'spacing' => null,
])

@php
    $background = $settings['background'] ?? 'ink';
    $spacing ??= $settings['spacing'] ?? 'default';

    $classes = [];

    if ($background === 'surface') {
        $classes[] = 'section--surface';
    }

    if ($spacing === 'tight') {
        $classes[] = 'section--tight';
    }

    if ($spacing === 'flush') {
        $classes[] = 'section--tight';
        $classes[] = 'pt-0';
    }
@endphp

<section @class(['section', ...$classes])>
    {{ $slot }}
</section>
