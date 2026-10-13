@php
    $record ??= null;
    $authoring ??= false;

    $image = null;
    $label = '';

    if ($record !== null) {
        $image = $record->cover_image ?? $record->image ?? null;
        $image = is_string($image) && $image !== '' ? $image : null;
        $label = (string) ($record->title ?? '');
    }
@endphp

@if ($image !== null)
    <x-builder.partials.section :settings="$settings">
        <div class="shell">
            <x-media
                :src="\Illuminate\Support\Facades\Storage::url($image)"
                :label="$label"
                ratio=""
                :aspect="$settings['aspect'] ?? '1300/560'"
                :decorative="! empty($settings['decorative'])"
                class="border border-line"
            />
        </div>
    </x-builder.partials.section>
@elseif ($record === null || $authoring)
    {{-- The record has no cover: hide entirely on the live site, but keep a
         selectable placeholder in the builder so the block stays reachable. --}}
    <x-builder.partials.section :settings="$settings">
        <div class="shell">
            <div class="border border-dashed border-line p-8 text-sm text-muted">
                This block renders the record&rsquo;s cover image on the live site.
            </div>
        </div>
    </x-builder.partials.section>
@endif
