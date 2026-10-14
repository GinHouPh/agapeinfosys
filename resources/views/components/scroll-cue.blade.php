{{-- Decorative scroll marker docked at the bottom centre of a hero
     section: a label above a fading accent hairline. Desktop only — it
     hints at the content below the fold. --}}
<div {{ $attributes->merge(['class' => 'absolute bottom-8 left-1/2 hidden -translate-x-1/2 flex-col items-center gap-3 lg:flex']) }}>
    <span class="font-display text-[11px] tracking-[0.3em] text-muted uppercase">Scroll</span>
    <span class="h-14 w-px bg-linear-to-b from-accent to-transparent" aria-hidden="true"></span>
</div>
