@props(['href', 'active' => false, 'label'])

<a href="{{ $href }}" @class(['admin-nav-link', 'is-active' => $active])>
    <span class="h-1.5 w-1.5 rounded-full {{ $active ? 'bg-accent' : 'bg-line' }}" aria-hidden="true"></span>
    {{ $label }}
</a>
