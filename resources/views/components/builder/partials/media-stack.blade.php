@props([
    'images' => [],
])

@php
    $images = array_values(array_filter($images, function ($image) {
        return ! empty($image['label']) || ! empty($image['image']);
    }));

    $lead = $images[0] ?? null;
    $pair = array_slice($images, 1, 2);
@endphp

@if ($lead)
    <x-media
        :src="! empty($lead['image']) ? \Illuminate\Support\Facades\Storage::url($lead['image']) : null"
        :label="$lead['label'] ?? ''"
        ratio=""
        aspect="749/457"
        class="border border-line"
    />
@endif

@if (count($pair))
    <div class="mt-6 grid grid-cols-2 gap-6">
        @foreach ($pair as $image)
            <x-media
                :src="! empty($image['image']) ? \Illuminate\Support\Facades\Storage::url($image['image']) : null"
                :label="$image['label'] ?? ''"
                ratio=""
                aspect="749/457"
                class="border border-line"
            />
        @endforeach
    </div>
@endif
