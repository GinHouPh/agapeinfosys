@props(['href', 'active' => false, 'label', 'icon' => null])

<a href="{{ $href }}" @class(['admin-nav-link', 'is-active' => $active]) title="{{ $label }}">
    <x-admin-icon :name="$icon" />
    <span class="admin-nav-link__label">{{ $label }}</span>
</a>
