@php
    $product = $product ?? null;
    $isEdit = $product !== null;
    $method = $isEdit ? 'PUT' : 'POST';
    $action = $isEdit ? route('admin.products.update', $product) : route('admin.products.store');
    $galleryLines = old('gallery') ?? implode("\n", (array) ($product?->gallery ?? []));
@endphp

@if ($isEdit)
    <div class="admin-card mb-6 space-y-4 !p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap gap-8">
                <div>
                    <h3 class="font-display text-sm tracking-[0.2em] text-white uppercase">Product detail page</h3>
                    <a
                        href="{{ route('shop.show', $product) }}"
                        target="_blank"
                        rel="noopener"
                        class="mt-1 block text-sm text-accent hover:underline"
                    >
                        {{ route('shop.show', $product) }}
                    </a>
                </div>

                <div>
                    <h3 class="font-display text-sm tracking-[0.2em] text-white uppercase">Public checkout URL</h3>
                    <a
                        href="{{ url('/'.$product->slug) }}"
                        target="_blank"
                        rel="noopener"
                        class="mt-1 block text-sm text-accent hover:underline"
                    >
                        {{ url('/'.$product->slug) }}
                    </a>
                </div>
            </div>

            <form action="{{ route('admin.products.rotate', $product) }}" method="POST"
                  onsubmit="return confirm('Rotate the API credentials? The product must be updated with the new secret.');">
                @csrf
                <button type="submit" class="tag hover:border-accent hover:text-accent">Rotate API credentials</button>
            </form>
        </div>

        <div class="border-t border-line pt-4">
            <h3 class="font-display text-sm tracking-[0.2em] text-white uppercase">API credentials</h3>

            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <dt class="w-24 text-muted">Key</dt>
                    <dd class="break-all font-mono text-white">{{ $product->api_key }}</dd>
                </div>

                @if (session('rotated_secret'))
                    <div class="flex flex-wrap items-start gap-2">
                        <dt class="w-24 text-muted">Secret</dt>
                        <dd class="break-all font-mono text-accent">{{ session('rotated_secret') }}</dd>
                    </div>
                    <p class="text-xs text-muted">Copy this secret now — it is never shown again.</p>
                @else
                    <div class="flex items-center gap-2">
                        <dt class="w-24 text-muted">Secret</dt>
                        <dd class="font-mono text-muted">•••••••••••• (hidden — rotate to reveal)</dd>
                    </div>
                @endif
            </dl>

            <p class="mt-4 text-xs text-muted">
                Products sign <code class="text-white">X-Agape-Key</code> / <code class="text-white">X-Agape-Timestamp</code> /
                <code class="text-white">X-Agape-Signature</code> headers with HMAC-SHA256 over
                <code class="text-white">timestamp.method.path</code>.
            </p>
        </div>
    </div>
@endif

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-8">
    @csrf
    @method($method)

    <div class="grid gap-6 xl:grid-cols-3">
        {{-- Main Content Column --}}
        <div class="space-y-6 xl:col-span-2">
            <div class="admin-card space-y-6">
                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="field">
                        <label class="field__label" for="name">Name *</label>
                        <input class="input" type="text" id="name" name="name" value="{{ old('name', $product?->name) }}" required>
                        @error('name') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="slug">Slug</label>
                        <input class="input" type="text" id="slug" name="slug" value="{{ old('slug', $product?->slug) }}" placeholder="tongdon">
                        @error('slug') <span class="field__error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="field">
                        <label class="field__label" for="domain">Domain</label>
                        <input class="input" type="text" id="domain" name="domain" value="{{ old('domain', $product?->domain) }}" placeholder="tongdon.com">
                        @error('domain') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="preview_url">Live preview URL</label>
                        <input class="input" type="url" id="preview_url" name="preview_url" value="{{ old('preview_url', $product?->preview_url) }}" placeholder="https://demo.tongdon.com">
                        @error('preview_url') <span class="field__error">{{ $message }}</span> @enderror
                        <span class="mt-1 block text-xs text-muted">Used by the Live Preview button (falls back to Domain if empty).</span>
                    </div>
                </div>

                <div class="field">
                    <label class="field__label" for="description">Short summary</label>
                    <textarea class="input" id="description" name="description" rows="2" placeholder="One or two sentences shown on marketplace cards and SEO meta.">{{ old('description', $product?->description) }}</textarea>
                    @error('description') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                {{-- Markdown Product Details Editor with Live Preview & Media Picker --}}
                <div class="field" data-md-editor>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <label class="field__label !mb-0" for="body">Product details (Markdown — images, links &amp; formatted text)</label>

                        <div class="flex items-center gap-2">
                            <button type="button" class="tag border-accent text-accent" data-md-tab="write">Write</button>
                            <button type="button" class="tag hover:border-accent hover:text-accent" data-md-tab="preview">Preview</button>
                        </div>
                    </div>

                    <div class="mt-2 flex flex-wrap items-center gap-2 border border-line bg-ink px-3 py-2" data-md-toolbar>
                        <button type="button" class="tag !py-1 !text-xs" data-md-insert="## " data-md-mode="line">H2</button>
                        <button type="button" class="tag !py-1 !text-xs" data-md-insert="### " data-md-mode="line">H3</button>
                        <button type="button" class="tag !py-1 !text-xs" data-md-wrap="**">Bold</button>
                        <button type="button" class="tag !py-1 !text-xs" data-md-wrap="_">Italic</button>
                        <button type="button" class="tag !py-1 !text-xs" data-md-insert="- " data-md-mode="line">List</button>
                        <button type="button" class="tag !py-1 !text-xs" data-md-insert="> " data-md-mode="line">Quote</button>
                        <button type="button" class="tag !py-1 !text-xs" data-md-link>Link</button>
                        <button type="button" class="tag !py-1 !text-xs hover:border-accent hover:text-accent" data-media-open="body">Insert Image</button>
                    </div>

                    <textarea
                        class="input !rounded-none border-t-0 !min-h-80 font-mono !text-sm"
                        id="body"
                        name="body"
                        rows="14"
                        data-md-input
                        placeholder="## Key Features&#10;&#10;- **Feature one**: Describe the capability&#10;- [Live Admin Demo](https://demo.example.com)&#10;&#10;![Dashboard preview](https://example.com/preview.png)"
                    >{{ old('body', $product?->body) }}</textarea>

                    <div class="prose-dark hidden min-h-80 border border-t-0 border-line bg-surface p-6" data-md-preview></div>

                    @error('body') <span class="field__error">{{ $message }}</span> @enderror
                    <span class="mt-1 block text-xs text-muted">Supports Markdown headings, lists, links, blockquotes, and images from your Media Library.</span>
                </div>
            </div>

            {{-- Screenshots / Gallery --}}
            <div class="admin-card space-y-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="font-display text-sm tracking-[0.2em] text-white uppercase">Screenshots Gallery</h3>
                        <p class="mt-1 text-xs text-muted">Displayed in the Screenshots section on the product detail page.</p>
                    </div>

                    <button type="button" class="tag hover:border-accent hover:text-accent" data-media-open="gallery">
                        + Pick from Media Library
                    </button>
                </div>

                <div class="field">
                    <label class="field__label" for="gallery">Image URLs or paths (one per line)</label>
                    <textarea class="input font-mono !text-xs" id="gallery" name="gallery" rows="4" data-gallery-input placeholder="https://example.com/screenshot-1.png&#10;/storage/media/dashboard.png">{{ $galleryLines }}</textarea>
                    @error('gallery') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="gallery_files">Upload new screenshots</label>
                    <input class="input !text-sm" type="file" id="gallery_files" name="gallery_files[]" accept="image/*" multiple>
                    @error('gallery_files') <span class="field__error">{{ $message }}</span> @enderror
                    @error('gallery_files.*') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                @if (! empty($product?->gallery))
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach ($product->gallery as $shot)
                            @php
                                $shotUrl = is_string($shot) && (str_starts_with($shot, 'http://') || str_starts_with($shot, 'https://') || str_starts_with($shot, '/'))
                                    ? $shot
                                    : (is_string($shot) ? Storage::url($shot) : null);
                            @endphp
                            @if ($shotUrl)
                                <img src="{{ $shotUrl }}" alt="Screenshot preview" class="aspect-[16/10] w-full border border-line object-cover">
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Right Sidebar: Media, Category, Webhook & Status --}}
        <div class="space-y-6">
            <div class="admin-card space-y-6">
                <div class="field">
                    <label class="field__label" for="logo">Hero / Shop image</label>
                    <input class="input !text-sm" type="file" id="logo" name="logo" accept="image/*">
                    @error('logo') <span class="field__error">{{ $message }}</span> @enderror
                    <span class="mt-1 block text-xs text-muted">Shown on the shop &amp; hero preview — JPG, PNG or WebP up to 4 MB.</span>

                    @if ($product?->imageUrl())
                        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="mt-3 aspect-[16/9] w-full border border-line object-cover">
                    @endif
                </div>

                <div class="field">
                    <label class="field__label" for="category_id">Marketplace category</label>
                    <select class="input" id="category_id" name="category_id">
                        <option value="">Uncategorised</option>
                        @foreach ($categories as $id => $label)
                            <option value="{{ $id }}" @selected((int) old('category_id', $product?->category_id) === $id)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="callback_url">Webhook callback URL</label>
                    <input class="input" type="url" id="callback_url" name="callback_url" value="{{ old('callback_url', $product?->callback_url) }}" placeholder="https://tongdon.com/webhooks/agape">
                    @error('callback_url') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="sort_order">Sort order</label>
                    <input class="input" type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $product?->sort_order ?? 0) }}" min="0">
                    @error('sort_order') <span class="field__error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="admin-card space-y-4">
                <label class="flex items-center gap-3 text-sm text-white">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="h-4 w-4 accent-[#d90429]" @checked(old('is_active', $product?->is_active ?? true))>
                    Active (checkout reachable)
                </label>
            </div>

            <div class="flex flex-col gap-3">
                <button type="submit" class="btn btn--accent w-full justify-center">
                    {{ $isEdit ? 'Save changes' : 'Create product' }}
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>

                <a href="{{ route('admin.products.index') }}" class="btn btn--ghost w-full justify-center">Cancel</a>
            </div>
        </div>
    </div>
</form>

{{-- Media Library Picker Modal --}}
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-ink/80 p-4" data-media-modal aria-hidden="true">
    <div class="admin-card flex max-h-[85vh] w-full max-w-3xl flex-col !p-6">
        <div class="flex items-center justify-between gap-4 border-b border-line pb-4">
            <h3 class="font-display text-lg text-white">Select Image from Media Library</h3>
            <button type="button" class="tag hover:border-accent hover:text-accent" data-media-close>Close</button>
        </div>

        <div class="mt-4">
            <input type="search" class="input" placeholder="Search images..." data-media-search>
        </div>

        <div class="mt-4 grid flex-1 grid-cols-2 gap-4 overflow-y-auto sm:grid-cols-4" data-media-grid>
            <p class="col-span-full text-sm text-muted">Loading images...</p>
        </div>
    </div>
</div>

<script>
    (function () {
        var editor = document.querySelector('[data-md-editor]');
        if (!editor) {
            return;
        }

        var input = editor.querySelector('[data-md-input]');
        var preview = editor.querySelector('[data-md-preview]');
        var toolbar = editor.querySelector('[data-md-toolbar]');
        var tabs = editor.querySelectorAll('[data-md-tab]');
        var galleryInput = document.querySelector('[data-gallery-input]');

        function escapeHtml(str) {
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function renderMarkdown(md) {
            var lines = md.split(/\r?\n/);
            var html = [];
            var inList = false;

            function inlineFormat(text) {
                var safe = escapeHtml(text);
                safe = safe.replace(/!\[([^\]]*)\]\(([^)\s]+)\)/g, '<img src="$2" alt="$1" class="my-4 border border-line">');
                safe = safe.replace(/\[([^\]]+)\]\(([^)\s]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>');
                safe = safe.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
                safe = safe.replace(/_([^_]+)_/g, '<em>$1</em>');
                safe = safe.replace(/`([^`]+)`/g, '<code>$1</code>');
                return safe;
            }

            lines.forEach(function (line) {
                if (/^\s*[-*]\s+/.test(line)) {
                    if (!inList) {
                        html.push('<ul>');
                        inList = true;
                    }
                    html.push('<li>' + inlineFormat(line.replace(/^\s*[-*]\s+/, '')) + '</li>');
                    return;
                }

                if (inList) {
                    html.push('</ul>');
                    inList = false;
                }

                if (/^###\s+/.test(line)) {
                    html.push('<h3>' + inlineFormat(line.replace(/^###\s+/, '')) + '</h3>');
                } else if (/^##\s+/.test(line)) {
                    html.push('<h2>' + inlineFormat(line.replace(/^##\s+/, '')) + '</h2>');
                } else if (/^#\s+/.test(line)) {
                    html.push('<h2>' + inlineFormat(line.replace(/^#\s+/, '')) + '</h2>');
                } else if (/^>\s+/.test(line)) {
                    html.push('<blockquote><p>' + inlineFormat(line.replace(/^>\s+/, '')) + '</p></blockquote>');
                } else if (line.trim() === '') {
                    // skip blank lines
                } else {
                    html.push('<p>' + inlineFormat(line) + '</p>');
                }
            });

            if (inList) {
                html.push('</ul>');
            }

            return html.join('\n') || '<p class="text-muted">Nothing to preview yet.</p>';
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var mode = tab.dataset.mdTab;
                tabs.forEach(function (t) {
                    var active = t === tab;
                    t.classList.toggle('border-accent', active);
                    t.classList.toggle('text-accent', active);
                });

                var isPreview = mode === 'preview';
                input.classList.toggle('hidden', isPreview);
                toolbar.classList.toggle('hidden', isPreview);
                preview.classList.toggle('hidden', !isPreview);

                if (isPreview) {
                    preview.innerHTML = renderMarkdown(input.value);
                }
            });
        });

        function insertAtCursor(text) {
            var start = input.selectionStart || 0;
            var end = input.selectionEnd || 0;
            input.value = input.value.slice(0, start) + text + input.value.slice(end);
            input.selectionStart = input.selectionEnd = start + text.length;
            input.focus();
        }

        editor.querySelectorAll('[data-md-insert]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                insertAtCursor(btn.dataset.mdInsert);
            });
        });

        editor.querySelectorAll('[data-md-wrap]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var wrap = btn.dataset.mdWrap;
                var start = input.selectionStart || 0;
                var end = input.selectionEnd || 0;
                var selected = input.value.slice(start, end) || 'text';
                insertAtCursor(wrap + selected + wrap);
            });
        });

        var linkBtn = editor.querySelector('[data-md-link]');
        if (linkBtn) {
            linkBtn.addEventListener('click', function () {
                var start = input.selectionStart || 0;
                var end = input.selectionEnd || 0;
                var label = input.value.slice(start, end) || 'Link text';
                insertAtCursor('[' + label + '](https://)');
            });
        }

        // Media Library Modal
        var modal = document.querySelector('[data-media-modal]');
        var grid = document.querySelector('[data-media-grid]');
        var search = document.querySelector('[data-media-search]');
        var closeBtn = document.querySelector('[data-media-close]');
        var targetField = 'body';

        function loadMedia(query) {
            var url = @json(route('admin.media.library')) + (query ? '?q=' + encodeURIComponent(query) : '');
            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    var items = data.items || [];
                    if (items.length === 0) {
                        grid.innerHTML = '<p class="col-span-full text-sm text-muted">No images found in Media Library.</p>';
                        return;
                    }

                    grid.innerHTML = '';
                    items.forEach(function (item) {
                        var btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'group border border-line bg-ink p-2 text-left transition-colors hover:border-accent';
                        btn.innerHTML = '<img src="' + escapeHtml(item.url) + '" alt="' + escapeHtml(item.alt || item.name) + '" class="aspect-[16/10] w-full object-cover">' +
                            '<p class="mt-2 truncate text-xs text-white">' + escapeHtml(item.name) + '</p>';
                        btn.addEventListener('click', function () {
                            if (targetField === 'gallery' && galleryInput) {
                                var existing = galleryInput.value.trim();
                                galleryInput.value = existing ? existing + '\n' + item.url : item.url;
                            } else {
                                insertAtCursor('\n![' + (item.alt || item.name) + '](' + item.url + ')\n');
                            }
                            modal.classList.add('hidden');
                            modal.classList.remove('flex');
                        });
                        grid.appendChild(btn);
                    });
                });
        }

        document.querySelectorAll('[data-media-open]').forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                targetField = trigger.dataset.mediaOpen || 'body';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                loadMedia('');
            });
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
        }

        if (search) {
            var timer = null;
            search.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(function () {
                    loadMedia(search.value.trim());
                }, 250);
            });
        }
    })();
</script>
