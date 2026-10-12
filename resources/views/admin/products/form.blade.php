@php
    $product = $product ?? null;
    $isEdit = $product !== null;
    $method = $isEdit ? 'PUT' : 'POST';
    $action = $isEdit ? route('admin.products.update', $product) : route('admin.products.store');
@endphp

@if ($isEdit)
    <div class="admin-card mb-6 space-y-4 !p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
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
                <label class="field__label" for="logo">Shop image</label>
                <input class="input" type="file" id="logo" name="logo" accept="image/*">
                @error('logo') <span class="field__error">{{ $message }}</span> @enderror
                <span class="mt-1 block text-xs text-muted">Shown on the shop — JPG, PNG or WebP up to 4 MB.</span>
            </div>

            <div class="field flex items-end pb-2">
                @if ($product?->imageUrl())
                    <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="h-24 w-24 border border-line object-cover">
                @endif
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div class="field">
                <label class="field__label" for="domain">Domain</label>
                <input class="input" type="text" id="domain" name="domain" value="{{ old('domain', $product?->domain) }}" placeholder="tongdon.com">
                @error('domain') <span class="field__error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label class="field__label" for="callback_url">Webhook callback URL</label>
                <input class="input" type="url" id="callback_url" name="callback_url" value="{{ old('callback_url', $product?->callback_url) }}" placeholder="https://tongdon.com/webhooks/agape">
                @error('callback_url') <span class="field__error">{{ $message }}</span> @enderror
                <span class="mt-1 block text-xs text-muted">Where signed payment updates are pushed.</span>
            </div>
        </div>

        <div class="field">
            <label class="field__label" for="description">Description</label>
            <textarea class="input" id="description" name="description" rows="3">{{ old('description', $product?->description) }}</textarea>
            @error('description') <span class="field__error">{{ $message }}</span> @enderror
        </div>

        <div class="grid gap-6 sm:grid-cols-3">
            <div class="field">
                <label class="field__label" for="sort_order">Sort order</label>
                <input class="input" type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $product?->sort_order ?? 0) }}" min="0">
                @error('sort_order') <span class="field__error">{{ $message }}</span> @enderror
            </div>

            <div class="field flex items-end pb-2">
                <label class="flex items-center gap-3 text-sm text-white">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product?->is_active ?? true))>
                    Active (checkout reachable)
                </label>
            </div>
        </div>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="btn btn--accent">
            {{ $isEdit ? 'Save changes' : 'Create product' }}
            <span class="btn__strip" aria-hidden="true"></span>
        </button>

        <a href="{{ route('admin.products.index') }}" class="tag self-center hover:border-accent hover:text-accent">Cancel</a>
    </div>
</form>
