@php
    use App\Models\Setting;

    $email = Setting::value('contact_email', 'hello@agapeinfosys.com');
    $phone = Setting::value('contact_phone', '+91 00000 00000');
    $address = Setting::value('office_address', "Level 4, Tech Park, Innovation Drive\nBengaluru, Karnataka 560103");

    $showForm = (bool) ($settings['show_form'] ?? true);
    $swap = (bool) ($settings['swap_sides'] ?? false);
    $sectionBg = $settings['background'] ?? 'ink';

    $detailsClass = 'order-1 lg:order-'.($swap ? '2' : '1');
    $formClass = 'order-2 lg:order-'.($swap ? '1' : '2');

    $items = [];

    if (! empty($settings['show_office'])) {
        $items[] = [
            'label' => 'Our Office',
            'value' => Setting::value('office_address', "Level 4, Tech Park, Innovation Drive\nBengaluru, Karnataka 560103"),
        ];
    }

    if (! empty($settings['show_mailing'])) {
        $items[] = [
            'label' => 'Mailing Address',
            'value' => Setting::value('mailing_address', "PO Box 4021, Knowledge City\nBengaluru, Karnataka 560001"),
        ];
    }

    $items[] = ['label' => 'Email', 'value' => $email, 'href' => 'mailto:'.$email];
    $items[] = ['label' => 'Phone', 'value' => $phone, 'href' => 'tel:'.preg_replace('/[^0-9+]/', '', $phone)];

    if (! empty($settings['show_studio'])) {
        $items[] = ['label' => 'Studio', 'value' => $address, 'pre' => true];
    }

    $hours = trim((string) ($settings['hours_body'] ?? ''));

    if (! empty($settings['show_hours']) && ! empty($settings['hours_from_business'])) {
        $business = Setting::structured('business', []);
        $hours = trim((string) ($business['support_hours'] ?? '')) !== '' ? $business['support_hours'] : $hours;
    }

    $disclosures = [];

    if (! empty($settings['show_business'])) {
        $business = Setting::structured('business', []);
        $disclosures = array_filter([
            'Legal entity' => $business['legal_name'] ?? '',
            'Registration' => $business['registration_number'] ?? '',
            'GSTIN' => $business['tax_number'] ?? '',
            'Website' => $business['website'] ?? '',
            'Founded' => $business['founded_year'] ?? '',
            'Industry' => $business['industry'] ?? '',
        ]);
    }
@endphp

<x-builder.partials.section :settings="$settings">
    <div class="shell grid-2 gap-16">
        @if ($showForm)
            <div class="{{ $formClass }}">
                <div class="border border-line {{ $sectionBg === 'surface' ? 'bg-ink' : 'bg-surface' }} p-8 sm:p-10">
                    @if (($settings['form_heading'] ?? '') !== '')
                        <h4 class="mb-8 !text-2xl">
                            @php
                                $heading = $settings['form_heading'];
                                $firstWord = \Illuminate\Support\Str::before($heading, ' ');
                            @endphp

                            @if ($firstWord !== $heading)
                                <span class="text-accent">{{ $firstWord }}</span>
                                {{ \Illuminate\Support\Str::after($heading, $firstWord) }}
                            @else
                                <span class="text-accent">{{ $heading }}</span>
                            @endif
                        </h4>
                    @endif

                    @include('partials.enquiry-form', ['compact' => (bool) ($settings['form_compact'] ?? true)])
                </div>
            </div>
        @endif

        <div class="{{ $detailsClass }}">
            <x-builder.partials.head
                :title="$settings['title'] ?? ''"
                :subtitle="$settings['subtitle'] ?? ''"
                :mark="$settings['mark'] ?? ''"
            />

            @if (($settings['lead'] ?? '') !== '')
                <p class="mb-10 max-w-lg text-body">{{ $settings['lead'] }}</p>
            @endif

            <ul class="space-y-8">
                @foreach ($items as $index => $item)
                    <li class="{{ $index === count($items) - 1 ? '' : 'border-b border-line pb-6' }}">
                        <span class="block font-display text-xs tracking-[0.24em] text-accent uppercase">
                            {{ $item['label'] }}
                        </span>

                        @if (! empty($item['pre']))
                            <p class="mt-3 whitespace-pre-line text-body">{!! nl2br(e($item['value'])) !!}</p>
                        @elseif (! empty($item['href']))
                            <a href="{{ $item['href'] }}"
                               class="mt-3 block text-lg text-white hover:text-accent">
                                {{ $item['value'] }}
                            </a>
                        @else
                            <p class="mt-3 whitespace-pre-line text-body">{!! nl2br(e($item['value'])) !!}</p>
                        @endif
                    </li>
                @endforeach
            </ul>

            @if (! empty($settings['show_hours']))
                <div class="mt-10 border border-line bg-ink p-6">
                    <span class="block font-display text-xs tracking-[0.24em] text-accent uppercase">
                        {{ $settings['hours_label'] ?? 'Working Hours' }}
                    </span>
                    <p class="mt-3 text-body">{!! nl2br(e($hours)) !!}</p>
                </div>
            @endif

            @if ($disclosures !== [])
                <div class="mt-10 border border-line bg-ink p-6">
                    <span class="block font-display text-xs tracking-[0.24em] text-accent uppercase">
                        {{ $settings['business_heading'] ?? 'Business Details' }}
                    </span>

                    <dl class="mt-4 space-y-3 text-sm">
                        @foreach ($disclosures as $label => $value)
                            <div class="flex justify-between gap-6">
                                <dt class="shrink-0 text-muted">{{ $label }}</dt>
                                <dd class="text-right break-all text-white">
                                    @if ($label === 'Website')
                                        <a href="{{ $value }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="hover:text-accent">
                                            {{ parse_url($value, PHP_URL_HOST) ?: $value }}
                                        </a>
                                    @else
                                        {{ $value }}
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif
        </div>
    </div>
</x-builder.partials.section>
