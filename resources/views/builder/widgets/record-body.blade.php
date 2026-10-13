@php
    $record ??= null;

    $layout = $settings['layout'] ?? 'single';
    $isArticle = $record instanceof \App\Models\Post;

    $title = '';
    $excerpt = '';
    $image = null;
    $bodyHtml = '';
    $listItems = [];
    $listTitle = '';

    if ($record !== null) {
        $title = (string) ($record->title ?? '');
        $excerpt = trim((string) ($record->excerpt ?? ''));
        $body = trim((string) ($record->body ?? ''));

        $image = $record->cover_image ?? $record->image ?? null;
        $image = is_string($image) && $image !== '' ? $image : null;

        if ($isArticle) {
            $paragraphs = preg_split('/\R{2,}/', (string) $record->body) ?: [];
            $paragraphs = array_values(array_filter(array_map('trim', $paragraphs), fn (string $p) => $p !== ''));

            $bodyHtml = collect($paragraphs)
                ->map(fn (string $p) => '<p>'.e($p).'</p>')
                ->implode("\n");
        } elseif ($body !== '') {
            $bodyHtml = $body;
        } elseif ($excerpt !== '') {
            $bodyHtml = '<p class="text-lg text-body">'.e($excerpt).'</p>';
        }

        if ($record instanceof \App\Models\Service) {
            $listItems = array_values(array_filter((array) ($record->highlights ?? [])));
            $listTitle = 'What you get';
        } elseif ($record instanceof \App\Models\Project) {
            $listItems = array_values(array_filter((array) ($record->outcomes ?? [])));
            $listTitle = 'Outcomes';
        }
    }

    if (($settings['list_title'] ?? '') !== '') {
        $listTitle = $settings['list_title'];
    }

    $showList = ($settings['show_list'] ?? true) && $listItems !== [];
    $showPullquote = $layout === 'single' && ($settings['show_pullquote'] ?? true) && $excerpt !== '';
    $showBack = $layout === 'single' && ($settings['show_back'] ?? true);
@endphp

@if ($record === null)
    <x-builder.partials.section :settings="$settings">
        <div class="shell">
            <div class="border border-dashed border-line p-8 text-sm text-muted">
                This block renders the body of the record the page is visiting — its summary,
                article body, highlights, facts and call to action.
            </div>
        </div>
    </x-builder.partials.section>
@else
    @php
        $bodyColumn = '';

        if ($showPullquote) {
            $bodyColumn .= '<p class="mb-10 border-l-2 border-accent pl-6 text-lg text-white">'.e($excerpt).'</p>';
        }

        $bodyColumn .= '<div class="prose-dark">';

        if ($bodyHtml !== '') {
            $bodyColumn .= $bodyHtml;
        } else {
            $bodyColumn .= '<p class="text-muted">This section is being written.</p>';
        }

        $bodyColumn .= '</div>';

        if ($showList) {
            $bodyColumn .= '<div class="mt-10">';
            $bodyColumn .= '<h5 class="mb-6 text-white">'.e($listTitle).'</h5>';
            $bodyColumn .= '<ul class="space-y-4">';

            foreach ($listItems as $item) {
                $bodyColumn .= '<li class="flex items-start gap-4 border-b border-line pb-4">'
                    .'<span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>'
                    .'<span class="text-body">'.e((string) $item).'</span>'
                    .'</li>';
            }

            $bodyColumn .= '</ul></div>';
        }
    @endphp

    @if ($layout === 'media-cta')
        <x-builder.partials.section :settings="$settings">
            <div class="shell grid-2 items-start gap-16">
                <div class="lg:sticky lg:top-28">
                    <x-media
                        :src="$image !== null ? \Illuminate\Support\Facades\Storage::url($image) : null"
                        :label="$title"
                        ratio=""
                        aspect="475/560"
                        class="border border-line"
                    />

                    <div class="mt-8 border border-line p-7">
                        <h6 class="mb-4 text-base text-white uppercase">{{ $settings['cta_heading'] ?? '' }}</h6>

                        @if (($settings['cta_body'] ?? '') !== '')
                            <p class="mb-6 text-sm text-body">{!! nl2br(e($settings['cta_body'])) !!}</p>
                        @endif

                        <x-btn :href="$settings['cta_url'] ?? '/contact'" variant="accent" :label="$settings['cta_label'] ?? ''" />
                    </div>
                </div>

                <div>{!! $bodyColumn !!}</div>
            </div>
        </x-builder.partials.section>
    @elseif ($layout === 'facts')
        <x-builder.partials.section :settings="$settings">
            <div class="shell grid-2 items-start gap-16">
                <div>{!! $bodyColumn !!}</div>

                <aside class="border border-line bg-surface p-8 lg:sticky lg:top-28">
                    <dl class="space-y-6">
                        @foreach ([
                            'client' => 'Client',
                            'service_type' => 'Engagement',
                            'year' => 'Year',
                        ] as $attribute => $label)
                            @if (! empty($record->{$attribute}))
                                <div>
                                    <dt class="font-display text-xs tracking-[0.24em] text-muted uppercase">{{ $label }}</dt>
                                    <dd class="mt-1 text-white">{{ $record->{$attribute} }}</dd>
                                </div>
                            @endif
                        @endforeach

                        @if (! empty($record->stack))
                            <div>
                                <dt class="font-display text-xs tracking-[0.24em] text-muted uppercase">Stack</dt>
                                <dd class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($record->stack as $technology)
                                        <span class="tag">{{ $technology }}</span>
                                    @endforeach
                                </dd>
                            </div>
                        @endif
                    </dl>

                    @if (! empty($record->project_url))
                        <div class="mt-8 border-t border-line pt-6">
                            <a href="{{ $record->project_url }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="font-display text-xs tracking-[0.24em] text-accent uppercase">
                                {{ $settings['facts_visit_label'] ?? 'Visit project →' }}
                            </a>
                        </div>
                    @endif

                    <div class="mt-8 border-t border-line pt-6">
                        <x-btn :href="$settings['cta_url'] ?? '/contact'" variant="accent" :label="$settings['facts_cta_label'] ?? ''" />
                    </div>
                </aside>
            </div>
        </x-builder.partials.section>
    @else
        <x-builder.partials.section :settings="$settings">
            <div class="shell max-w-3xl">
                {!! $bodyColumn !!}

                @if ($showBack)
                    <div class="mt-14 border-t border-line pt-8">
                        <x-btn :href="$settings['back_url'] ?? '/blog'" variant="ghost" :label="$settings['back_label'] ?? 'Back'" />
                    </div>
                @endif
            </div>
        </x-builder.partials.section>
    @endif
@endif
