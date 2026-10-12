@props([
    'title',
    'subtitle' => null,
    'align' => 'left',
    'mark' => null,
    'tag' => 'h2',
])

@php
    $words = preg_split('/\s+/', trim($title), 2) ?: [];
    $lead = $words[0] ?? '';
    $rest = $words[1] ?? '';
    $isCenter = $align === 'center';
@endphp

<div {{ $attributes->class(['section-head', $isCenter ? 'section-head--center' : null]) }}>
    @if ($mark)
        <span class="section-mark {{ $isCenter ? 'section-mark--right' : '' }}" aria-hidden="true">
            {{ $mark }}
        </span>
    @endif

    <{{ $tag }} class="section-head__title">
        <span class="lead">{{ $lead }}</span> {{ $rest }}
    </{{ $tag }}>

    @if ($subtitle)
        <span class="section-head__subtitle">{{ $subtitle }}</span>
    @endif
</div>
