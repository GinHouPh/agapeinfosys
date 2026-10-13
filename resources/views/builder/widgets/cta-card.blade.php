<x-builder.partials.section :settings="$settings" spacing="tight">
    <div class="shell">
        <div class="admin-card flex flex-col items-start justify-between gap-6 md:flex-row md:items-center">
            <div>
                @if (($settings['heading'] ?? '') !== '')
                    <h2 class="font-display text-2xl tracking-wide text-white">
                        {{ $settings['heading'] }}
                    </h2>
                @endif

                @if (($settings['lead'] ?? '') !== '')
                    <p class="mt-3 max-w-2xl text-body">{{ $settings['lead'] }}</p>
                @endif
            </div>

            @if (($settings['button_label'] ?? '') !== '' && ($settings['button_url'] ?? '') !== '')
                <div class="shrink-0">
                    <x-btn
                        :href="$settings['button_url']"
                        :variant="$settings['variant'] ?? 'accent'"
                        :label="$settings['button_label']"
                    />
                </div>
            @endif
        </div>
    </div>
</x-builder.partials.section>
