@props([
    'href',
    'variant' => 'solid',
    'label' => null,
])

<a
    {{ $attributes->merge(['class' => "btn btn--$variant"]) }}
    href="{{ $href }}"
>
    {{ $label ?? $slot }}
    <span class="btn__strip" aria-hidden="true"></span>
</a>
