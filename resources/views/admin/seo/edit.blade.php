@extends('layouts.admin')

@section('title', 'SEO')
@section('heading', 'Search Metadata')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <p class="max-w-2xl text-sm text-muted">
            What search engines and social networks show for this record. Anything left blank
            falls back to the record's own title and excerpt.
        </p>

        <a href="{{ route('admin.seo.redirects') }}" class="tag self-start">Redirects</a>
    </div>

    <form action="{{ route('admin.seo.update', ['type' => $type, 'id' => $record->id]) }}" method="POST"
          class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        @csrf
        @method('PUT')

        <div class="space-y-8">
            <section class="admin-card">
                <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Search results</h2>

                <div class="space-y-4">
                    <div>
                        <label class="field__label" for="meta_title">Title</label>
                        <input class="input" type="text" id="meta_title" name="meta_title"
                               value="{{ old('meta_title', $meta?->meta_title) }}">
                        <p class="text-sm text-muted">Falls back to “{{ $resolved['title'] }}”.</p>
                        @error('meta_title') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="field__label" for="meta_description">Description</label>
                        <textarea class="input" id="meta_description" name="meta_description" rows="3">{{ old('meta_description', $meta?->meta_description) }}</textarea>
                        <p class="text-sm text-muted">Falls back to “{{ $resolved['description'] }}”.</p>
                        @error('meta_description') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="field__label" for="meta_keywords">Keywords</label>
                        <input class="input" type="text" id="meta_keywords" name="meta_keywords"
                               value="{{ old('meta_keywords', $meta?->meta_keywords) }}" placeholder="comma, separated">
                        @error('meta_keywords') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="field__label" for="canonical_url">Canonical URL</label>
                        <input class="input" type="url" id="canonical_url" name="canonical_url"
                               value="{{ old('canonical_url', $meta?->canonical_url) }}"
                               placeholder="https://agapeinfosys.com/...">
                        @error('canonical_url') <span class="field__error">{{ $message }}</span> @enderror
                    </div>
                </div>
            </section>

            <section class="admin-card">
                <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Social sharing</h2>

                <div class="space-y-4">
                    <div>
                        <label class="field__label" for="og_title">Social title</label>
                        <input class="input" type="text" id="og_title" name="og_title"
                               value="{{ old('og_title', $meta?->og_title) }}">
                        @error('og_title') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="field__label" for="og_description">Social description</label>
                        <textarea class="input" id="og_description" name="og_description" rows="2">{{ old('og_description', $meta?->og_description) }}</textarea>
                        @error('og_description') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="field__label" for="og_image">Social image URL</label>
                        <input class="input" type="text" id="og_image" name="og_image"
                               value="{{ old('og_image', $meta?->og_image) }}"
                               placeholder="/uploads/pages/hero.jpg">
                        @error('og_image') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="field__label" for="twitter_card">Twitter card</label>
                        <select class="input" id="twitter_card" name="twitter_card">
                            @foreach (['summary_large_image', 'summary', 'app', 'player'] as $card)
                                <option value="{{ $card }}" @selected(old('twitter_card', $meta?->twitter_card ?? 'summary_large_image') === $card)>
                                    {{ $card }}
                                </option>
                            @endforeach
                        </select>
                        @error('twitter_card') <span class="field__error">{{ $message }}</span> @enderror
                    </div>
                </div>
            </section>
        </div>

        <div class="space-y-8">
            <section class="admin-card">
                <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Indexing</h2>

                <div class="space-y-4">
                    <label class="flex items-center gap-3 text-body">
                        <input type="hidden" name="noindex" value="0">
                        <input type="checkbox" name="noindex" value="1" class="h-4 w-4 accent-[#d90429]"
                               @checked(old('noindex', $meta?->noindex))>
                        Hide from search engines
                    </label>

                    <div>
                        <label class="field__label" for="sitemap_priority">Sitemap priority</label>
                        <select class="input" id="sitemap_priority" name="sitemap_priority">
                            @foreach (['1.0', '0.9', '0.8', '0.7', '0.5', '0.3'] as $priority)
                                <option value="{{ $priority }}" @selected(old('sitemap_priority', $meta?->sitemap_priority ?? '0.7') === $priority)>
                                    {{ $priority }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="field__label" for="sitemap_changefreq">Change frequency</label>
                        <select class="input" id="sitemap_changefreq" name="sitemap_changefreq">
                            @foreach (['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly'] as $frequency)
                                <option value="{{ $frequency }}" @selected(old('sitemap_changefreq', $meta?->sitemap_changefreq ?? 'monthly') === $frequency)>
                                    {{ $frequency }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            <section class="admin-card">
                <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Save</h2>

                <button type="submit" class="btn btn--accent w-full">
                    Save Metadata
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>
            </section>
        </div>
    </form>
@endsection
