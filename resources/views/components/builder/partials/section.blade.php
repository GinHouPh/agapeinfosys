@props([
    'settings' => [],
    'spacing' => null,
    'container' => false,
])

@php
    $background = $settings['background'] ?? 'ink';
    $spacing ??= $settings['spacing'] ?? 'default';

    $classes = ['relative'];

    if ($background === 'surface') {
        $classes[] = 'section--surface';
    }

    // "none" means no band at all: the wrapper contributes no vertical rhythm,
    // so whatever sits inside owns its own padding.
    if ($spacing !== 'none') {
        $classes[] = 'section';

        if ($spacing === 'tight' || $spacing === 'flush') {
            $classes[] = 'section--tight';
        }

        if ($spacing === 'flush') {
            $classes[] = 'pt-0';
        }
    }

    // Marks a band that holds other blocks: CSS collapses the rhythm of the
    // widgets inside it, but keeps its own.
    if ($container) {
        $classes[] = 'section--container';
    }
@endphp

<section @class($classes)>
    {{ $slot }}
</section>
