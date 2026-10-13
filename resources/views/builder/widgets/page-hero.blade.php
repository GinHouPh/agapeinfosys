@php
    use Illuminate\Support\Str;

    $source = $settings['source'] ?? 'static';
    $isRecord = $source === 'record' && $record !== null;
    $gradient = $settings['gradient'] ?? 'left';
    $spacing = $settings['spacing'] ?? 'default';

    $glow = match ($gradient) {
        'center' => 'circle_at_60%_15%',
        'right' => 'circle_at_75%_15%',
        default => 'circle_at_25%_15%',
    };

    $pad = match ($spacing) {
        'tight' => 'pt-32 pb-10',
        'none' => 'pt-24 pb-6',
        default => 'pt-40 pb-16',
    };

    $eyebrow = $settings['eyebrow'] ?? '';
    $lead = $settings['lead'] ?? '';
    $title = '';

    if ($isRecord) {
        $title = (string) ($record->title ?? '');

        if ($eyebrow === '') {
            $eyebrow = (string) ($record->eyebrow ?? $record->category ?? '');
        }

        if ($lead === '') {
            $lead = (string) ($record->summary ?? $record->excerpt ?? $record->description ?? '');
        }
    }
@endphp

<section class="relative overflow-hidden {{ $pad }}">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient({{ $glow }},rgba(217,4,41,0.18),transparent_55%)]"
         aria-hidden="true"></div>

    <div class="shell relative">
        @if ($source === 'record' && $record === null)
            <div class="border border-dashed border-line p-8 text-sm text-muted">
                This header renders the title of the page&rsquo;s record on the live site.
            </div>
        @else
            @if ($settings['show_breadcrumb'] ?? false)
                <nav class="mb-8 flex flex-wrap items-center gap-3 font-display text-xs tracking-[0.24em] text-muted uppercase"
                     aria-label="Breadcrumb">
                    <a href="{{ route('home') }}" class="hover:text-accent">Home</a>
                    <span aria-hidden="true">/</span>
                    <a href="{{ $settings['breadcrumb_parent_url'] ?? '/' }}" class="hover:text-accent">
                        {{ $settings['breadcrumb_parent_label'] ?? '' }}
                    </a>
                    <span aria-hidden="true">/</span>
                    <span class="text-body">{{ Str::limit($title, 40) }}</span>
                </nav>
            @endif

            @if ($eyebrow !== '')
                <span class="eyebrow">{{ $eyebrow }}</span>
            @endif

            @if ($isRecord)
                <h1 class="max-w-4xl">{{ $title }}</h1>
            @else
                <h1 class="max-w-5xl">
                    @if (($settings['title_before'] ?? '') !== '')
                        {{ $settings['title_before'] }}
                    @endif
                    @if (($settings['accent'] ?? '') !== '')
                        <span class="text-accent">{{ $settings['accent'] }}</span>
                    @endif
                    @if (($settings['title_after'] ?? '') !== '')
                        {{ $settings['title_after'] }}
                    @endif
                </h1>
            @endif

            @if ($isRecord && ($settings['show_meta'] ?? false))
                <div class="mt-6 flex flex-wrap items-center gap-4 text-xs text-muted">
                    @if (($record->category ?? null) !== null && $record->category !== '')
                        <span class="tag">{{ $record->category }}</span>
                    @endif

                    @if (isset($record->published_at) && $record->published_at)
                        <time datetime="{{ $record->published_at->toDateString() }}">
                            {{ $record->published_at->format('d M Y') }}
                        </time>
                    @endif

                    @if (($record->reading_time ?? null) !== null && $record->reading_time !== '')
                        <span>{{ $record->reading_time }}</span>
                    @endif
                </div>
            @endif

            @if ($isRecord && ($settings['show_author'] ?? false) && isset($record->author) && $record->author)
                <p class="mt-6 text-body">
                    By <span class="text-white">{{ $record->author->name }}</span>
                </p>
            @endif

            @if ($lead !== '')
                <p class="mt-8 max-w-2xl text-lg text-body">{!! nl2br(e($lead)) !!}</p>
            @endif
        @endif
    </div>
</section>
