@extends('layouts.admin')

@section('title', 'Builder — '.$page->title)
@section('heading', $page->title)

@push('head')
    <style>
        .pb-canvas-block { position: relative; cursor: pointer; outline: 1px solid transparent; transition: outline-color .15s; }
        .pb-canvas-block:hover { outline-color: var(--color-line); }
        .pb-canvas-block.is-selected { outline-color: var(--color-accent); }
        .pb-canvas-block.is-dragging { opacity: .4; }
        .pb-canvas-block.is-drop-before::before,
        .pb-canvas-block.is-drop-after::after {
            content: ''; position: absolute; left: 0; right: 0; height: 2px; background: var(--color-accent);
        }
        .pb-canvas-block.is-drop-before::before { top: -1px; }
        .pb-canvas-block.is-drop-after::after { bottom: -1px; }
        .pb-block-toolbar {
            position: absolute; top: 0; right: 0; z-index: 20; display: none; gap: 1px;
            background: var(--color-accent);
        }
        .pb-canvas-block:hover .pb-block-toolbar, .pb-canvas-block.is-selected .pb-block-toolbar { display: flex; }
        .pb-block-toolbar button {
            padding: 4px 8px; font-size: 10px; letter-spacing: .12em; text-transform: uppercase; color: #fff; background: #1b1b1b;
        }
        .pb-block-toolbar button:hover { background: var(--color-accent); }
        .pb-empty { border: 1px dashed var(--color-line); padding: 48px 24px; text-align: center; color: var(--color-muted); }
        .pb-palette-item { display: flex; gap: 10px; align-items: flex-start; width: 100%; padding: 10px 12px; border: 1px solid var(--color-line); text-align: left; transition: border-color .2s; }
        .pb-palette-item:hover { border-color: var(--color-accent); }
        .pb-panel .field { margin-bottom: 18px; }
        .pb-rep-row { border: 1px solid var(--color-line); padding: 12px; margin-bottom: 10px; }
        .pb-rep-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .pb-canvas { background: var(--color-ink); }
        /* Rendered content is inert so clicks and drags land on the block
           wrapper instead of links inside it. Nested wrappers opt back in so
           a block inside a section is still selectable, and empty column
           drop zones do too — otherwise they inherit the inertness above. */
        .pb-canvas .section { pointer-events: none; }
        .pb-canvas-block, .pb-col { pointer-events: auto; }
        .pb-col { position: relative; }
        .pb-col:empty { min-height: 76px; border: 1px dashed var(--color-line); }
        .pb-col.is-drop { border-color: var(--color-accent); background: rgba(217, 4, 41, .08); }
        .pb-block-tag {
            display: none; position: absolute; top: 0; left: 0; z-index: 20;
            padding: 4px 8px; font-size: 10px; letter-spacing: .12em; text-transform: uppercase;
            background: var(--color-line); color: #fff; pointer-events: auto;
        }
        .pb-canvas-block:hover > .pb-block-tag,
        .pb-canvas-block.is-selected > .pb-block-tag { display: block; }
        .pb-crumb { display: flex; flex-wrap: wrap; align-items: center; gap: 4px; margin-bottom: 14px; font-size: 10px; letter-spacing: .14em; text-transform: uppercase; color: var(--color-muted); }
        .pb-crumb button { font-size: 10px; letter-spacing: .14em; text-transform: uppercase; color: var(--color-muted); cursor: pointer; }
        .pb-crumb button:hover { color: var(--color-accent); }
        .pb-crumb button.is-current { color: var(--color-accent); }
        .pb-crumb span { color: var(--color-line); }
        .pb-hint { border: 1px dashed var(--color-line); padding: 10px 12px; margin-bottom: 16px; font-size: 11px; line-height: 1.5; color: var(--color-muted); }
        .pb-hint b { color: var(--color-headline); font-weight: 500; }
        /* Panel group divider. Shares the .field__label voice but is styled
           here, so the builder never leans on a shared stylesheet for its
           own panel furniture. */
        .pb-field-heading { margin: 22px 0 14px; padding-bottom: 8px; border-bottom: 1px solid var(--color-line); font-family: var(--font-display); font-size: 10px; letter-spacing: .22em; text-transform: uppercase; color: var(--color-headline); }
        .pb-field-heading:first-child { margin-top: 0; }
        /* Device tier switcher. Chooses which tier of a responsive control the
           panel reads and writes — it says nothing about how the canvas is
           drawn, which is a separate problem the canvas itself owns. */
        .pb-device { display: flex; margin-bottom: 16px; border: 1px solid var(--color-line); }
        .pb-device button { flex: 1; padding: 7px 4px; border-left: 1px solid var(--color-line); font-family: var(--font-display); font-size: 10px; letter-spacing: .16em; text-transform: uppercase; color: var(--color-muted); cursor: pointer; transition: color .15s, background .15s; }
        .pb-device button:first-child { border-left: 0; }
        .pb-device button:hover { color: var(--color-headline); }
        .pb-device button.is-on { background: var(--color-accent); color: #fff; }
        /* The canvas copy sits in a header, not a form column: no rhythm to
           keep, and a touch smaller so it doesn't outweigh the count. */
        .pb-device--preview { margin-bottom: 0; border-color: var(--color-line); }
        .pb-device--preview button { padding: 5px 10px; }
        /* A token picker: the select names the token, the square shows what it
           resolves to right now — which is the theme's answer, not a hex kept
           beside the name. */
        .pb-color { position: relative; }
        .pb-color select { padding-right: 34px; }
        .pb-color-swatch { position: absolute; top: 50%; right: 12px; width: 14px; height: 14px; border: 1px solid var(--color-line); pointer-events: none; }
        .pb-chip { display: inline-block; margin-top: 4px; padding: 1px 5px; border: 1px solid var(--color-line); font-size: 9px; letter-spacing: .16em; text-transform: uppercase; color: var(--color-accent); }
        .pb-history button { padding: 4px 9px; line-height: 1.4; transition: color .15s, border-color .15s; }
        .pb-history button[disabled] { opacity: .3; cursor: not-allowed; }
        .pb-history button:not([disabled]):hover { border-color: var(--color-accent); color: var(--color-accent); }
        /* Both overlays below are flex containers, so the `hidden` attribute
           needs a rule of its own to actually hide them. */
        .pb-toast[hidden], .pb-help[hidden], .pb-menu[hidden], .pb-tree[hidden] { display: none; }
        .pb-toast {
            position: fixed; right: 24px; bottom: 24px; z-index: 50;
            display: flex; align-items: center; gap: 16px;
            padding: 12px 16px; border: 1px solid var(--color-line); background: var(--color-surface);
            font-size: 12px; color: var(--color-body); box-shadow: 0 10px 30px rgba(0, 0, 0, .5);
        }
        .pb-toast button { font-size: 10px; letter-spacing: .18em; text-transform: uppercase; color: var(--color-accent); }
        .pb-toast button:hover { text-decoration: underline; }
        /* Right-click menu. Every row is drawn from the shortcut table, so
           the key that does the same job is printed beside it. */
        .pb-menu {
            position: fixed; z-index: 57;
            /* max-content keeps the key column from being squeezed when the
               menu opens hard against the right edge, where a shrink-to-fit
               box would otherwise run out of room and wrap its rows. */
            width: max-content; min-width: 250px;
            padding: 6px; border: 1px solid var(--color-line); background: var(--color-surface);
            box-shadow: 0 18px 50px rgba(0, 0, 0, .6);
        }
        .pb-menu button {
            display: flex; align-items: center; justify-content: space-between; gap: 28px;
            width: 100%; padding: 8px 10px; font-size: 12px; color: var(--color-body); text-align: left;
        }
        .pb-menu button:hover, .pb-menu button:focus-visible { background: rgba(217, 4, 41, .12); color: #fff; outline: none; }
        .pb-menu kbd { font-family: var(--font-display); font-size: 10px; letter-spacing: .12em; text-transform: uppercase; color: var(--color-muted); }
        /* Structure drawer. Read-only by design — reordering already lives on
           the canvas and in the right-click menu, and this exists to make
           nesting legible rather than to become a second place to edit it. */
        .pb-tree {
            position: fixed; top: 0; left: 0; bottom: 0; z-index: 56;
            display: flex; flex-direction: column;
            width: min(320px, 88vw);
            background: var(--color-surface); border-right: 1px solid var(--color-line);
            box-shadow: 0 0 60px rgba(0, 0, 0, .55);
        }
        .pb-tree__head {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px; border-bottom: 1px solid var(--color-line);
        }
        .pb-tree__head button { color: var(--color-muted); font-size: 20px; line-height: 1; }
        .pb-tree__head button:hover { color: var(--color-accent); }
        .pb-tree__rows { flex: 1; overflow-y: auto; padding: 8px 0; }
        .pb-tree__row {
            display: flex; align-items: center; gap: 8px;
            width: 100%; padding: 7px 16px; font-size: 12px;
            color: var(--color-body); text-align: left;
            border-left: 2px solid transparent;
        }
        .pb-tree__row:hover { background: rgba(255, 255, 255, .05); color: #fff; }
        .pb-tree__row.is-current { border-left-color: var(--color-accent); background: rgba(217, 4, 41, .10); color: #fff; }
        .pb-tree__empty { padding: 12px 16px; font-size: 12px; color: var(--color-muted); }
        .pb-help { position: fixed; inset: 0; z-index: 60; display: flex; align-items: center; justify-content: center; background: rgba(0, 0, 0, .65); }
        .pb-help__panel { width: min(440px, 92vw); border: 1px solid var(--color-line); background: var(--color-surface); padding: 20px; }
        .pb-help__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--color-line); }
        .pb-help__head button { color: var(--color-muted); font-size: 20px; line-height: 1; }
        .pb-help__head button:hover { color: var(--color-accent); }
        .pb-help__table { width: 100%; font-size: 12px; }
        .pb-help__table td { padding: 7px 0; color: var(--color-body); }
        .pb-help__table td:first-child { width: 128px; color: var(--color-accent); font-family: var(--font-display); letter-spacing: .1em; }

        /* The media picker: a modal over the canvas, listing the library. */
        .pb-media-modal { position: fixed; inset: 0; z-index: 80; display: flex; align-items: center; justify-content: center; padding: 24px; background: rgba(0, 0, 0, .72); }
        .pb-media-modal[hidden] { display: none; }
        .pb-media-modal__box { width: min(880px, 94vw); max-height: 86vh; overflow: auto; border: 1px solid var(--color-line); background: var(--color-surface); padding: 20px; }
        .pb-media-modal__grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; margin-top: 16px; }
        .pb-media-modal__item { display: block; border: 1px solid var(--color-line); text-align: left; transition: border-color .2s; }
        .pb-media-modal__item:hover { border-color: var(--color-accent); }
        .pb-media-modal__item img { display: block; width: 100%; height: 100px; object-fit: cover; }
        .pb-media-modal__name { display: block; padding: 6px 8px; font-size: 10px; color: var(--color-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    </style>
@endpush

@php
    $liveUrl = $page->template() !== null
        ? null
        : ($page->isFlagship() || $page->status->isPublished() ? $page->targetUrl() : null);
@endphp

<div class="mb-5 flex flex-wrap items-center justify-between gap-4">
    <a href="{{ route('admin.pages.index') }}" class="font-display text-xs tracking-[0.24em] text-accent uppercase">
        &larr; All pages
    </a>

    <div class="flex flex-wrap items-center gap-3">
        @if ($liveUrl)
            <a href="{{ $liveUrl }}" target="_blank" rel="noopener" class="tag hover:border-accent hover:text-accent">View live</a>
        @endif
        <div class="pb-history flex items-center gap-1">
            <button type="button" id="pb-undo" class="tag" title="Undo (Ctrl+Z)" disabled>&#8630;</button>
            <button type="button" id="pb-redo" class="tag" title="Redo (Ctrl+Shift+Z)" disabled>&#8631;</button>
            <button type="button" id="pb-help-open" class="tag" title="Keyboard shortcuts">?</button>
            <button type="button" id="pb-tree-open" class="tag" title="Page structure (Ctrl+Alt+O)">&#9776;</button>
        </div>
        <span id="pb-save-status" class="text-xs text-muted"></span>
        <button type="button" id="pb-save" class="btn btn--accent">
            Save
            <span class="btn__strip" aria-hidden="true"></span>
        </button>
    </div>
</div>

@isset($template)
    @if ($template)
        <div class="mb-5 border border-accent p-4 text-sm text-body">
            <span class="font-display text-xs tracking-[0.24em] text-accent uppercase">Record template</span>
            <p class="mt-2">
                Published, these sections replace every
                <span class="font-mono text-xs text-white">{{ $template['pattern'] }}</span>
                page. Each one renders them with its own record, so headings, body copy, covers and
                related links come from the page being visited.
            </p>
        </div>
    @endif
@endisset

<div class="grid gap-5 xl:grid-cols-[240px_minmax(0,1fr)_320px]">

    {{-- Palette --}}
    <aside class="admin-card h-fit">
        <h2 class="mb-4 border-b border-line pb-3 font-display text-xs tracking-[0.24em] text-white uppercase">Sections</h2>
        <div id="pb-palette" class="max-h-[70vh] space-y-4 overflow-y-auto pr-1"></div>

        <div id="pb-components-wrap" class="mt-6 hidden border-t border-line pt-5">
            <h3 class="mb-3 font-display text-[10px] tracking-[0.24em] text-muted uppercase">Saved components</h3>
            <div id="pb-components" class="max-h-[40vh] space-y-3 overflow-y-auto pr-1"></div>
        </div>
    </aside>

    {{-- Canvas --}}
    <div class="min-w-0">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-display text-xs tracking-[0.24em] text-muted uppercase">Canvas</h2>
            <div class="flex items-center gap-3">
                <div class="pb-device pb-device--preview m-0" role="group" aria-label="Preview at">
                    <button type="button" data-preview-device="desktop" aria-pressed="true" class="is-on" title="Full width">Desktop</button>
                    <button type="button" data-preview-device="tablet" aria-pressed="false" title="768px">Tablet</button>
                    <button type="button" data-preview-device="mobile" aria-pressed="false" title="390px">Mobile</button>
                </div>
                <span id="pb-count" class="text-xs text-muted"></span>
            </div>
        </div>
        <div id="pb-canvas" class="pb-canvas border border-line"></div>
    </div>

    {{-- Settings --}}
    <aside class="admin-card h-fit">
        <h2 class="mb-4 border-b border-line pb-3 font-display text-xs tracking-[0.24em] text-white uppercase">Settings</h2>
        <div id="pb-panel" class="pb-panel">
            <p class="text-sm text-muted">Select a section on the canvas to edit it, or add one from the palette.</p>
        </div>
    </aside>
</div>

{{-- Reversible actions announce themselves here; only irreversible ones ask --}}
<div id="pb-toast" class="pb-toast" hidden>
    <span id="pb-toast-msg"></span>
    <button type="button" id="pb-toast-action" hidden></button>
</div>

{{-- Right-click menu, rendered straight from the same shortcut table --}}
<div id="pb-menu" class="pb-menu" hidden></div>

{{-- Structure drawer: nesting reads as flat on a canvas stacked on a canvas --}}
<aside id="pb-tree" class="pb-tree" hidden>
    <div class="pb-tree__head">
        <span class="font-display text-xs tracking-[0.24em] text-white uppercase">Page structure</span>
        <button type="button" data-close aria-label="Close">&times;</button>
    </div>
    <div id="pb-tree-rows" class="pb-tree__rows"></div>
</aside>

{{-- Shortcut sheet, rendered straight from the shortcut table --}}
<div id="pb-help" class="pb-help" hidden>
    <div class="pb-help__panel">
        <div class="pb-help__head">
            <span class="font-display text-xs tracking-[0.24em] text-white uppercase">Keyboard shortcuts</span>
            <button type="button" data-close aria-label="Close">&times;</button>
        </div>
        <table class="pb-help__table"><tbody id="pb-help-rows"></tbody></table>
    </div>
</div>

{{-- Hidden form carrying the page meta + schema on save --}}
<form id="pb-save-form" action="{{ $page->adminRoute('admin.pages.update') }}" method="POST" class="hidden">
    @csrf
    @method('PUT')
    <input type="hidden" name="title" id="pb-f-title" value="{{ old('title', $page->title) }}">
    <input type="hidden" name="slug" id="pb-f-slug" value="{{ old('slug', $page->slug) }}">
    <input type="hidden" name="status" id="pb-f-status" value="{{ old('status', $page->status->value) }}">
    <input type="hidden" name="meta_description" id="pb-f-meta" value="{{ old('meta_description', $page->meta_description) }}">
    <input type="hidden" name="schema" id="pb-f-schema">
</form>

@push('scripts')
@php
    $builderConfig = [
        'meta' => [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'status' => $page->status->value,
            'metaDescription' => $page->meta_description,
            'maxDepth' => $maxDepth ?? 3,
        ],
        'palette' => $widgets,
        'initialBlocks' => $initialBlocks,
        'deviceTiers' => \App\Builder\Device::TIERS,
        'deviceSuffix' => \App\Builder\Device::SUFFIX,
        'colorTokens' => \App\Builder\DesignTokens::COLORS,
        'urls' => [
            'update' => $page->adminRoute('admin.pages.update'),
            'preview' => route('admin.builder.preview'),
            'schema' => route('admin.builder.widget-schema'),
            'upload' => route('admin.builder.upload'),
            'components' => route('admin.templates.components'),
            'insertComponent' => url('admin/templates/components'),
            'storeComponent' => route('admin.templates.components.store'),
            'media' => route('admin.media.library'),
            'csrf' => csrf_token(),
        ],
    ];
@endphp

{{-- The canvas reads its whole state from this JSON, so the script itself
     is a plain module with no Blade in it. --}}
<script type="application/json" id="pb-config">{!! json_encode($builderConfig) !!}</script>
@vite('resources/js/builder/canvas.js')
@endpush
