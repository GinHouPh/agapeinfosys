@props(['href', 'active' => false, 'label', 'icon' => null, 'permission' => null])

@if ($permission === null || auth()->user()?->hasPermission($permission))
    <a href="{{ $href }}" @class(['admin-nav-link', 'is-active' => $active]) title="{{ $label }}">
        <x-admin-icon :name="$icon" />
        <span class="admin-nav-link__label">{{ $label }}</span>
    </a>
@endif
