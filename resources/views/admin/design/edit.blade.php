@extends('layouts.admin')

@section('title', $theme->name)
@section('heading', $theme->name)

@section('content')
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.design.index') }}" class="tag hover:border-accent hover:text-accent">All themes</a>
        <span class="badge {{ $theme->is_active ? 'badge--on' : 'badge--off' }}">
            {{ $theme->is_active ? 'Live' : 'Draft' }}
        </span>
    </div>

    <form action="{{ route('admin.design.update', $theme) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
            <div class="space-y-8">
                @foreach (['dark' => 'Dark scheme', 'light' => 'Light scheme'] as $scheme => $label)
                    <section class="admin-card">
                        <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">{{ $label }}</h2>

                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach ($palette as $name => $nameLabel)
                                @php($value = old("tokens.{$scheme}.{$name}", $theme->token($name, $scheme) ?? ($defaults[$scheme][$name] ?? '')))

                                <div>
                                    <label class="field__label" for="token-{{ $scheme }}-{{ $name }}">
                                        {{ $nameLabel }}
                                        <span class="font-mono text-xs text-muted">{{ $name }}</span>
                                    </label>

                                    <div class="flex items-center gap-3">
                                        <input type="color"
                                               class="h-10 w-12 shrink-0 cursor-pointer border border-line bg-transparent"
                                               id="token-{{ $scheme }}-{{ $name }}"
                                               value="{{ \App\Support\Colour::toHex($value) }}"
                                               oninput="this.nextElementSibling.value = this.value">
                                        <input class="input font-mono text-sm"
                                               type="text"
                                               name="tokens[{{ $scheme }}][{{ $name }}]"
                                               id="token-text-{{ $scheme }}-{{ $name }}"
                                               value="{{ $value }}"
                                               data-token="{{ $scheme }}:{{ $name }}">
                                    </div>

                                    @error("tokens.{$scheme}.{$name}") <span class="field__error">{{ $message }}</span> @enderror
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <section class="admin-card">
                    <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Typography</h2>

                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($fonts as $slot => $choices)
                            <div>
                                <label class="field__label" for="font-{{ $slot }}">
                                    {{ ucfirst($slot) }} font
                                </label>
                                <select class="input" id="font-{{ $slot }}" name="tokens[font][{{ $slot }}]">
                                    @foreach ($choices as $fontName => $fontLabel)
                                        <option value="{{ $fontName }}"
                                            @selected(old("tokens.font.{$slot}", $theme->font($slot)) === $fontName)>
                                            {{ $fontLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                @error("tokens.font.{$slot}") <span class="field__error">{{ $message }}</span> @enderror
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="admin-card">
                    <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Surfaces</h2>

                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($scales as $scale => $steps)
                            <div>
                                <label class="field__label" for="{{ $scale }}-card">{{ ucfirst($scale) }}</label>
                                <select class="input" id="{{ $scale }}-card" name="tokens[{{ $scale }}][card]">
                                    @foreach ($steps as $step => $stepValue)
                                        <option value="{{ $step }}"
                                            @selected(old("tokens.{$scale}.card", $theme->tokens[$scale]['card'] ?? 'none') === $step)>
                                            {{ ucfirst($step) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error("tokens.{$scale}.card") <span class="field__error">{{ $message }}</span> @enderror
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <div class="space-y-8">
                <section class="admin-card">
                    <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Details</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="field__label" for="theme-name">Name *</label>
                            <input class="input" type="text" id="theme-name" name="name"
                                   value="{{ old('name', $theme->name) }}" required>
                            @error('name') <span class="field__error">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="field__label" for="theme-description">Description</label>
                            <input class="input" type="text" id="theme-description" name="description"
                                   value="{{ old('description', $theme->description) }}">
                            @error('description') <span class="field__error">{{ $message }}</span> @enderror
                        </div>

                        <label class="flex items-center gap-3 text-body">
                            <input type="checkbox" name="is_active" value="1" class="h-4 w-4 accent-[#d90429]"
                                   @checked(old('is_active', $theme->is_active))>
                            Live theme
                        </label>

                        <button type="submit" class="btn btn--accent w-full">
                            Save Theme
                            <span class="btn__strip" aria-hidden="true"></span>
                        </button>
                    </div>
                </section>

                <section class="admin-card">
                    <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Preview</h2>

                    <div class="space-y-3 text-sm">
                        <div class="border border-line p-4" data-preview-card>
                            <p class="font-display text-xl tracking-[0.1em] uppercase" data-preview-headline>
                                Headline
                            </p>
                            <p class="mt-1 text-body" data-preview-body>Body text on a surface.</p>
                            <p class="mt-2 text-xs text-muted" data-preview-muted>Muted text and a line.</p>
                            <span class="mt-3 inline-block px-3 py-1 text-xs tracking-[0.16em] uppercase" data-preview-accent>
                                Accent
                            </span>
                        </div>

                        <p class="text-xs text-muted">
                            The preview repaints as you edit. Save to apply it across the site.
                        </p>
                    </div>
                </section>

                @unless ($theme->is_active)
                    <section class="admin-card">
                        <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Danger zone</h2>

                        <form action="{{ route('admin.design.destroy', $theme) }}" method="POST"
                              onsubmit="return confirm('Delete this theme?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs tracking-[0.16em] text-muted uppercase hover:text-accent">
                                Delete Theme
                            </button>
                        </form>
                    </section>
                @endunless
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            (function () {
                var sheet = document.createElement('style');
                sheet.id = 'design-preview';
                document.head.appendChild(sheet);

                var preview = document.querySelector('[data-preview-card]');
                var headline = document.querySelector('[data-preview-headline]');
                var body = document.querySelector('[data-preview-body]');
                var muted = document.querySelector('[data-preview-muted]');
                var accent = document.querySelector('[data-preview-accent]');

                function paint() {
                    var tokens = {};
                    var fonts = {};

                    document.querySelectorAll('[data-token]').forEach(function (input) {
                        var parts = input.getAttribute('data-token').split(':');
                        tokens[parts[0] + ':' + parts[1]] = input.value.trim();
                    });

                    var css = ':root{';

                    Object.keys(tokens).forEach(function (key) {
                        var parts = key.split(':');

                        if (parts[0] === 'dark') {
                            css += '--color-' + parts[1] + ':' + tokens[key] + ';';
                        }
                    });

                    var display = document.getElementById('font-display');
                    var bodyFont = document.getElementById('font-body');

                    if (display) css += "--font-display:'" + display.value + "',ui-sans-serif,sans-serif;";
                    if (bodyFont) css += "--font-body:'" + bodyFont.value + "',ui-sans-serif,sans-serif;";

                    css += '}';

                    sheet.textContent = css;

                    if (preview) {
                        preview.style.background = 'var(--color-surface)';
                        preview.style.borderColor = 'var(--color-line)';
                    }
                    if (headline) headline.style.color = 'var(--color-headline)';
                    if (body) body.style.color = 'var(--color-body)';
                    if (muted) muted.style.color = 'var(--color-muted)';
                    if (accent) {
                        accent.style.background = 'var(--color-accent)';
                        accent.style.color = 'var(--color-on-accent)';
                    }
                }

                document.querySelectorAll('[data-token], #font-display, #font-body').forEach(function (input) {
                    input.addEventListener('input', paint);
                    input.addEventListener('change', paint);
                });

                paint();
            })();
        </script>
    @endpush
@endsection
