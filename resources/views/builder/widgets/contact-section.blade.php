@php
    use App\Models\Setting;

    $email = Setting::value('contact_email', 'hello@agapeinfosys.com');
    $phone = Setting::value('contact_phone', '+91 00000 00000');
    $address = Setting::value('office_address', "Level 4, Tech Park, Innovation Drive\nBengaluru, Karnataka 560103");
    $showForm = (bool) ($settings['show_form'] ?? true);
@endphp

<x-builder.partials.section :settings="$settings">
    <div class="shell grid-2 gap-16">
        <div>
            <x-builder.partials.head
                :title="$settings['title'] ?? ''"
                :subtitle="$settings['subtitle'] ?? ''"
                :mark="$settings['mark'] ?? ''"
            />

            @if ($settings['lead'] ?? '')
                <p class="mb-10 max-w-lg text-body">{{ $settings['lead'] }}</p>
            @endif

            <ul class="space-y-5">
                <li class="flex items-start gap-4 border-b border-line pb-5">
                    <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                    <div>
                        <span class="block font-display text-sm tracking-[0.2em] text-white uppercase">Email</span>
                        <a href="mailto:{{ $email }}" class="hover:text-accent">{{ $email }}</a>
                    </div>
                </li>
                <li class="flex items-start gap-4 border-b border-line pb-5">
                    <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                    <div>
                        <span class="block font-display text-sm tracking-[0.2em] text-white uppercase">Phone</span>
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="hover:text-accent">{{ $phone }}</a>
                    </div>
                </li>
                <li class="flex items-start gap-4">
                    <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                    <div>
                        <span class="block font-display text-sm tracking-[0.2em] text-white uppercase">Studio</span>
                        <p class="whitespace-pre-line">{{ $address }}</p>
                    </div>
                </li>
            </ul>
        </div>

        @if ($showForm)
            <div class="border border-line bg-surface p-8 sm:p-10">
                @include('partials.enquiry-form', ['compact' => true])
            </div>
        @endif
    </div>
</x-builder.partials.section>
