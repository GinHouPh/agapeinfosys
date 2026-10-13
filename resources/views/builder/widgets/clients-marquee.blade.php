@php
    use App\Models\Client;

    $clients = Client::query()->published()->ordered()->get();
@endphp

<x-builder.partials.section :settings="$settings" spacing="tight">
    <div class="shell mb-12">
        <x-builder.partials.head
            :title="$settings['title'] ?? ''"
            :subtitle="$settings['subtitle'] ?? ''"
            :mark="$settings['mark'] ?? ''"
            :align="$settings['align'] ?? 'center'"
        />
    </div>

    @if ($clients->isEmpty())
        <div class="shell">
            <p class="text-muted">Client logos will appear here once they are published.</p>
        </div>
    @else
        <div class="marquee logo-marquee border-y border-line py-8">
            <div class="marquee__track">
                @foreach (range(1, 2) as $pass)
                    @foreach ($clients as $client)
                        <span class="marquee__item">
                            @if ($client->logo)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($client->logo) }}" alt="{{ $client->name }}" class="h-10 w-auto opacity-60 grayscale transition hover:opacity-100 hover:grayscale-0">
                            @else
                                {{ $client->name }}
                            @endif
                        </span>
                    @endforeach
                @endforeach
            </div>
        </div>
    @endif
</x-builder.partials.section>
