@props([
    'subtitle' => null,
    'title' => null,
    'mark' => null,
    'align' => 'left',
])

@if ($title)
    <x-section-head
        :title="$title"
        :subtitle="$subtitle"
        :align="$align"
        :mark="$mark ?: null"
    />
@endif
