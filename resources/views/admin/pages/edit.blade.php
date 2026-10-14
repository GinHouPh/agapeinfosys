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
<form id="pb-save-form" action="{{ route('admin.pages.update', $page) }}" method="POST" class="hidden">
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
    $builderMeta = [
        'id' => $page->id,
        'title' => $page->title,
        'slug' => $page->slug,
        'status' => $page->status->value,
        'metaDescription' => $page->meta_description,
        'maxDepth' => $maxDepth ?? 3,
    ];
    $builderUrls = [
        'update' => route('admin.pages.update', $page),
        'preview' => route('admin.builder.preview'),
        'schema' => route('admin.builder.widget-schema'),
        'upload' => route('admin.builder.upload'),
        'csrf' => csrf_token(),
    ];
@endphp
<script>
(() => {
    'use strict';

    const meta = @json($builderMeta);
    const palette = @json($widgets);
    const initialBlocks = @json($initialBlocks);
    const urls = @json($builderUrls);

    // --- State -------------------------------------------------------------
    const paletteByName = {};
    palette.forEach((w) => { paletteByName[w.name] = w; });
    const isContainer = (name) => !!(paletteByName[name] && paletteByName[name].container);
    const widgetTitle = (name) => (paletteByName[name] || {}).title || name;
    const maxDepth = Number(meta.maxDepth) || 3;

    /** Rebuild a node from the server's tree, giving containers a children list. */
    function hydrateBlock(b) {
        const node = { id: b.id, widget: b.widget, settings: b.settings || {}, html: b.html || '' };
        if (b.column !== undefined && b.column !== null) node.column = Number(b.column) || 0;
        node.children = Array.isArray(b.children) ? b.children.map(hydrateBlock) : (isContainer(node.widget) ? [] : undefined);
        return node;
    }

    let blocks = initialBlocks.map(hydrateBlock);
    let selectedId = null;
    // Column the palette drops into when a multi-column section is selected.
    let activeColumn = 0;
    // Which device the editor is working at. It picks the tier of a responsive
    // control the panel edits *and* the width the canvas is held to, so what
    // you set is what you are looking at — two faces of one choice, never able
    // to disagree. It stays put as you move between blocks, and is remembered
    // across sessions the way any workspace preference would be.
    const deviceTiers = @json(\App\Builder\Device::TIERS);
    const deviceSuffix = @json(\App\Builder\Device::SUFFIX);
    const savedDevice = (() => { try { return localStorage.getItem('pb-device'); } catch (_) { return null; } })();
    let device = deviceTiers.indexOf(savedDevice) === -1 ? deviceTiers[0] : savedDevice;
    const schemaCache = {};
    let flashTimer = null;

    const canvas = document.getElementById('pb-canvas');
    const panel = document.getElementById('pb-panel');
    const paletteEl = document.getElementById('pb-palette');
    const countEl = document.getElementById('pb-count');
    const saveStatus = document.getElementById('pb-save-status');
    let previewTimer = null;
    let saveTimer = null;

    const uid = () => Math.random().toString(36).slice(2, 12);
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    // --- Tree helpers ------------------------------------------------------
    /** Find a block anywhere in the tree, with its list, index and parent. */
    function locate(id, list, parent, depth) {
        if (!id) return null;
        list = list || blocks;
        parent = parent || null;
        depth = depth || 0;
        for (let i = 0; i < list.length; i++) {
            const b = list[i];
            if (b.id === id) return { list, index: i, block: b, parent, depth };
            const kids = b.children || [];
            if (kids.length) {
                const found = locate(id, kids, b, depth + 1);
                if (found) return found;
            }
        }
        return null;
    }

    const byId = (id) => { const loc = locate(id); return loc ? loc.block : null; };

    /** Whether `block` already holds `id` — you can't drop a section into itself. */
    function containsBlock(block, id) {
        return (block.children || []).some((c) => c.id === id || (isContainer(c.widget) && containsBlock(c, id)));
    }

    function cloneBlock(b) {
        const copy = { id: uid(), widget: b.widget, settings: JSON.parse(JSON.stringify(b.settings || {})), html: b.html || '' };
        if (b.column !== undefined) copy.column = b.column;
        if (isContainer(b.widget)) copy.children = (b.children || []).map(cloneBlock);
        return copy;
    }

    function countBlocks(list) {
        return (list || blocks).reduce((n, b) => n + 1 + (b.children ? countBlocks(b.children) : 0), 0);
    }

    /** Root-to-node chain for the panel's breadcrumb. */
    function pathTo(id) {
        const path = [];
        (function walk(list) {
            for (const b of list) {
                if (b.id === id) { path.push(b); return true; }
                if (b.children && walk(b.children)) { path.push(b); return true; }
            }
            return false;
        })(blocks);
        return path.reverse();
    }

    /** A container at `depth` may hold children while the result stays inside MAX_DEPTH. */
    const canNest = (depth) => depth + 1 < maxDepth;
    const columnsOf = (settings) => Math.max(1, Math.min(4, Number((settings || {}).columns) || 1));
    const insertColumn = (block) => Math.min(Math.max(activeColumn, 0), columnsOf(block.settings) - 1);

    function flash(msg) {
        saveStatus.textContent = msg;
        clearTimeout(flashTimer);
        flashTimer = setTimeout(() => { saveStatus.textContent = ''; }, 2600);
    }

    // --- Toast -------------------------------------------------------------
    /**
     * Reversible actions announce themselves instead of asking first. The
     * prompt only comes back once there is nothing to undo to.
     */
    let toastTimer = null;

    function toast(msg, actionLabel, action) {
        const el = document.getElementById('pb-toast');
        const btn = document.getElementById('pb-toast-action');
        document.getElementById('pb-toast-msg').textContent = msg;
        btn.textContent = actionLabel || '';
        btn.hidden = !actionLabel;
        btn.onclick = actionLabel && action ? () => { hideToast(); action(); } : null;
        el.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(hideToast, 6000);
    }

    function hideToast() {
        clearTimeout(toastTimer);
        document.getElementById('pb-toast').hidden = true;
    }

    // --- History -----------------------------------------------------------
    /**
     * Undo is snapshot-per-step. Our tree is plain JSON, so cloning it is
     * cheaper than the inverse-command bookkeeping a model-graph editor
     * needs, and it makes every future mutation undoable for free.
     *
     * `steps[0]` is the tree as it loaded; each later step is the tree after
     * one action. `index` is an implicit playhead rather than a stack top,
     * so jumping to an arbitrary entry time-travels instead of stepping +-1.
     */
    const undoStack = { steps: [], index: -1, limit: 100 };
    let coalesceTimer = null;
    let coalesceLabel = null;
    let groupDepth = 0;

    function snapshot() {
        return { blocks: JSON.parse(JSON.stringify(blocks)), selectedId, activeColumn };
    }

    function updateHistoryButtons() {
        const undoBtn = document.getElementById('pb-undo');
        const redoBtn = document.getElementById('pb-redo');
        if (undoBtn) undoBtn.disabled = undoStack.index <= 0;
        if (redoBtn) redoBtn.disabled = undoStack.index >= undoStack.steps.length - 1;
    }

    /** Record the tree as it stands, unless it already matches the live step. */
    function pushStep(label) {
        const live = undoStack.steps[undoStack.index];
        if (live && JSON.stringify(live.blocks) === JSON.stringify(blocks)) return false;

        // Acting after an undo throws the abandoned future away.
        undoStack.steps = undoStack.steps.slice(0, undoStack.index + 1);
        undoStack.steps.push(Object.assign(snapshot(), { label }));
        if (undoStack.steps.length > undoStack.limit) undoStack.steps.shift();
        undoStack.index = undoStack.steps.length - 1;
        updateHistoryButtons();
        return true;
    }

    /**
     * The funnel every mutation goes through: run it, record the result as
     * one step, queue a save. Anything touching `blocks` without coming
     * through here is invisible to undo — and before this funnel existed,
     * palette adds were invisible to the save timer as well.
     */
    function commit(label, mutate) {
        flushCoalesce();
        mutate();
        queueSave();
        return pushStep(label);
    }

    /** A gesture that may end in several splices still lands as one step. */
    function withGroup(label, mutate) {
        if (groupDepth > 0) { mutate(); return false; }
        groupDepth++;
        try {
            flushCoalesce();
            mutate();
        } finally { groupDepth--; }
        queueSave();
        return pushStep(label);
    }

    /**
     * Typing fires far too often to record each keystroke. A burst is held
     * open and folded into a single step once it goes quiet, so a slider
     * drag or a headline rewrite undoes as one action.
     */
    function coalesce(label) {
        coalesceLabel = label;
        clearTimeout(coalesceTimer);
        coalesceTimer = setTimeout(() => {
            coalesceTimer = null;
            pushStep(coalesceLabel);
            coalesceLabel = null;
        }, 800);
    }

    function flushCoalesce() {
        if (!coalesceTimer) return;
        clearTimeout(coalesceTimer);
        coalesceTimer = null;
        pushStep(coalesceLabel);
        coalesceLabel = null;
    }

    function undo() {
        flushCoalesce();
        if (undoStack.index <= 0) return;
        undoStack.index--;
        restoreStep();
    }

    function redo() {
        flushCoalesce();
        if (undoStack.index >= undoStack.steps.length - 1) return;
        undoStack.index++;
        restoreStep();
    }

    function restoreStep() {
        const step = undoStack.steps[undoStack.index];
        if (!step) return;
        blocks = JSON.parse(JSON.stringify(step.blocks));
        // The block the user was on may not have survived the trip.
        selectedId = locate(step.selectedId) ? step.selectedId : null;
        activeColumn = step.activeColumn;
        renderCanvas();
        renderPanel();
        refreshMissingPreviews();
        queueSave();
        updateHistoryButtons();
    }

    /** A block snapshotted before its preview landed would restore as an empty shell. */
    function refreshMissingPreviews(list) {
        (list || blocks).forEach((b) => {
            if (!b.html) refreshPreview(b);
            if (b.children && b.children.length) refreshMissingPreviews(b.children);
        });
    }

    // --- Palette -----------------------------------------------------------
    function renderPalette() {
        const groups = {};
        palette.forEach((w) => { (groups[w.category] = groups[w.category] || []).push(w); });
        paletteEl.innerHTML = Object.entries(groups).map(([cat, items]) => `
            <div>
                <p class="mb-2 font-display text-[10px] tracking-[0.24em] text-muted uppercase">${esc(cat)}</p>
                <div class="space-y-2">
                    ${items.map((w) => `
                        <button type="button" class="pb-palette-item" data-widget="${esc(w.name)}">
                            <svg viewBox="0 0 24 24" class="mt-0.5 h-4 w-4 shrink-0 text-accent" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="${esc(w.icon)}"/></svg>
                            <span>
                                <span class="block font-display text-xs tracking-[0.1em] text-white uppercase">${esc(w.title)}</span>
                                <span class="block text-[11px] leading-snug text-muted">${esc(w.description)}</span>
                                ${w.container ? '<span class="pb-chip">Container</span>' : ''}
                            </span>
                        </button>`).join('')}
                </div>
            </div>`).join('');
    }

    paletteEl.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-widget]');
        if (btn) addBlock(btn.dataset.widget);
    });

    // --- Schema fetch ------------------------------------------------------
    async function loadSchema(widget) {
        if (schemaCache[widget]) return schemaCache[widget];
        const res = await fetch(urls.schema, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': urls.csrf },
            body: JSON.stringify({ widget }),
        });
        if (!res.ok) throw new Error('schema');
        const data = await res.json();
        schemaCache[widget] = data;
        return data;
    }

    // --- Structure drawer --------------------------------------------------
    // Read-only on purpose. Reordering already lives on the canvas and in the
    // right-click menu; three levels of sections still read as flat when all
    // you can see is one canvas stacked on another, and this exists to make
    // the shape of the page legible — not to become a second place to edit it.
    const treeEl = document.getElementById('pb-tree');

    function renderTree() {
        const rows = [];
        (function walk(list, depth) {
            for (const b of list) {
                rows.push(
                    `<button type="button" class="pb-tree__row${b.id === selectedId ? ' is-current' : ''}"` +
                    ` data-tree-id="${esc(b.id)}" style="padding-left:${16 + depth * 14}px">` +
                    `${esc(widgetTitle(b.widget))}</button>`
                );
                if (b.children && b.children.length) walk(b.children, depth + 1);
            }
        })(blocks, 0);

        document.getElementById('pb-tree-rows').innerHTML = rows.length
            ? rows.join('')
            : '<p class="pb-tree__empty">Nothing on this page yet.</p>';
    }

    function toggleTree() {
        treeEl.hidden = !treeEl.hidden;
        if (!treeEl.hidden) renderTree();
    }

    treeEl.addEventListener('click', (e) => {
        if (e.target.closest('[data-close]')) { treeEl.hidden = true; return; }
        const row = e.target.closest('[data-tree-id]');
        // Jumping to a section, nothing more: the drawer stays open so the
        // whole page can be walked without reopening it each time.
        if (row) selectBlock(row.dataset.treeId);
    });

    // --- Device preview ----------------------------------------------------
    /**
     * What the canvas is held at for each device, and the variants that have to
     * come off the markup for it to read correctly at that width.
     *
     * The canvas is a div in the admin page, so no media query ever notices how
     * wide it is. Tailwind is mobile-first though, and so is our own markup: a
     * bare utility is the narrow tier, `md:` starts at 768, `lg:` at 1024. The
     * classes therefore already say which tier each one belongs to, so dropping
     * the ones that would not have matched leaves exactly what a real viewport
     * of that width would render.
     *
     * Desktop is left alone for the opposite reason: the real viewport is
     * already driving it, and `md:`/`lg:` are doing their genuine job.
     *
     * One honest gap: `max-*` variants answer to the real viewport, not to the
     * canvas, so a `max-md:` rule cannot be *switched on* for the mobile view
     * by removing classes. Where the real viewport is wide they are already
     * correctly off for tablet; they simply stay off.
     */
    const PREVIEW = {
        desktop: { width: null, drop: [], dropMax: [] },
        tablet: { width: 768, drop: ['lg:', 'xl:', '2xl:'], dropMax: ['max-sm:', 'max-md:'] },
        mobile: { width: 390, drop: ['sm:', 'md:', 'lg:', 'xl:', '2xl:'], dropMax: [] },
    };

    /** Section rhythm follows the width, so the canvas is told which tier to inherit. */
    function rhythmFor(tier) {
        const token = tier === 'tablet' ? '--spacing-section-tablet' : '--spacing-section-mobile';
        return getComputedStyle(document.documentElement).getPropertyValue(token).trim();
    }

    /**
     * Hold the canvas at the current device: its width, the vertical rhythm that
     * goes with that width, and the responsive variants the markup sheds there.
     *
     * Purely a display transform. The canvas holds a copy of each block's
     * server-rendered HTML — `block.html` is never touched — so nothing here
     * can leak into a save.
     */
    function applyDevicePreview() {
        const cfg = PREVIEW[device] || PREVIEW.desktop;

        canvas.style.width = cfg.width ? `${cfg.width}px` : '';
        canvas.style.maxWidth = cfg.width ? '100%' : '';
        canvas.style.marginInline = cfg.width ? 'auto' : '';

        const rhythm = cfg.width ? rhythmFor(device) : '';
        if (rhythm) canvas.style.setProperty('--section-y', rhythm);
        else canvas.style.removeProperty('--section-y');

        const drop = cfg.drop.concat(cfg.dropMax);
        if (!drop.length) return;

        canvas.querySelectorAll('[class]').forEach((el) => {
            const current = el.getAttribute('class');
            if (!current) return;
            const parts = current.split(/\s+/).filter(Boolean);
            const kept = parts.filter((c) => !drop.some((p) => c.startsWith(p)));
            if (kept.length !== parts.length) el.setAttribute('class', kept.join(' '));
        });
    }

    /**
     * Switch the device the editor is working at. The panel retargets the tier
     * it edits and the canvas takes the width that tier renders at; both faces
     * read the same state, so neither can drift from the other.
     */
    function setDevice(tier) {
        if (device === tier || deviceTiers.indexOf(tier) === -1) return;
        device = tier;
        try { localStorage.setItem('pb-device', tier); } catch (_) { /* private mode */ }

        syncPreviewButtons();

        // The transform strips classes, so it can only ever be applied to
        // fresh markup. Rebuilding the canvas recopies each block's
        // server-rendered HTML, which is what puts the variants back when you
        // widen out again.
        renderCanvas();

        // The panel only needs rebuilding when it has a tier to show; a widget
        // with nothing responsive has no reason to lose the field you're in.
        if (panel.querySelector('.pb-device')) renderPanel();
    }

    /**
     * Put the canvas switcher in step with the state. The panel's copy is
     * rebuilt by renderPanel(), but this one is part of the page and only
     * exists once, so it is painted here.
     */
    function syncPreviewButtons() {
        document.querySelectorAll('[data-preview-device]').forEach((btn) => {
            const on = btn.dataset.previewDevice === device;
            btn.classList.toggle('is-on', on);
            btn.setAttribute('aria-pressed', String(on));
        });
    }

    // --- Canvas ------------------------------------------------------------
    function renderCanvas() {
        // The drawer reads the same tree, so it is kept in step here rather
        // than at every call site — including the empty-page path below,
        // which returns early.
        renderTree();

        const total = countBlocks();
        countEl.textContent = total === 1 ? '1 section' : `${total} sections`;
        if (blocks.length === 0) {
            canvas.innerHTML = '<div class="pb-empty">No sections yet — add one from the palette on the left.</div>';
            applyDevicePreview();
            return;
        }
        canvas.innerHTML = '';
        blocks.forEach((b) => canvas.appendChild(buildBlockEl(b)));
        // Last, because it rewrites classes the block bodies just brought in.
        applyDevicePreview();
    }

    function buildBlockEl(block) {
        const info = paletteByName[block.widget] || {};
        const holds = isContainer(block.widget);
        const el = document.createElement('div');
        el.className = 'pb-canvas-block' + (block.id === selectedId ? ' is-selected' : '');
        el.dataset.id = block.id;
        el.draggable = true;
        el.innerHTML =
            (holds ? `<span class="pb-block-tag">${esc(info.title || block.widget)}</span>` : '') +
            '<div class="pb-block-toolbar">' +
            '<button type="button" data-act="up" title="Move up">↑</button>' +
            '<button type="button" data-act="down" title="Move down">↓</button>' +
            '<button type="button" data-act="dup" title="Duplicate">⧉</button>' +
            '<button type="button" data-act="del" title="Delete">✕</button>' +
            '</div>' +
            `<div class="pb-block-body">${block.html || ''}</div>`;

        if (holds) fillChildren(el, block);

        return el;
    }

    /**
     * Park every child in the column the server's HTML carved out for it.
     * Until a section's preview arrives there are no slots to fill, so fall
     * back to one — the block still has to be selectable and editable.
     */
    function fillChildren(wrap, block) {
        const body = wrap.querySelector(':scope > .pb-block-body');
        if (!body) return;

        let slots = Array.prototype.slice.call(body.querySelectorAll('[data-pb-col]'));

        if (slots.length === 0) {
            const fallback = document.createElement('div');
            fallback.className = 'pb-col';
            fallback.dataset.pbCol = '0';
            body.appendChild(fallback);
            slots = [fallback];
        }

        (block.children || []).forEach((child) => {
            const wanted = Number(child.column) || 0;
            slots[Math.min(Math.max(wanted, 0), slots.length - 1)].appendChild(buildBlockEl(child));
        });
    }

    canvas.addEventListener('click', (e) => {
        const actBtn = e.target.closest('[data-act]');
        const wrap = e.target.closest('.pb-canvas-block');
        if (!wrap) return;
        const id = wrap.dataset.id;
        if (actBtn) {
            e.stopPropagation();
            handleAction(id, actBtn.dataset.act);
            return;
        }
        const slot = e.target.closest('[data-pb-col]');
        selectBlock(id, slot ? Number(slot.dataset.pbCol) : undefined);
    });

    function handleAction(id, act) {
        const loc = locate(id);
        if (!loc) return;
        const list = loc.list;
        const labels = { up: 'Moved up', down: 'Moved down', dup: 'Duplicated', del: 'Deleted' };
        const title = widgetTitle(loc.block.widget);

        const changed = commit(labels[act] || 'Edited', () => {
            if (act === 'up' || act === 'down') {
                // Step within the column the block sits in, so a sibling in
                // another column doesn't get shuffled past it.
                const col = Number(loc.block.column) || 0;
                const steps = list
                    .map((b, i) => ((Number(b.column) || 0) === col ? i : -1))
                    .filter((i) => i >= 0);
                const at = steps.indexOf(loc.index);
                const to = at + (act === 'up' ? -1 : 1);

                if (to >= 0 && to < steps.length) {
                    const a = loc.index;
                    const b = steps[to];
                    [list[a], list[b]] = [list[b], list[a]];
                }
            } else if (act === 'dup') {
                const copy = cloneBlock(loc.block);
                list.splice(loc.index + 1, 0, copy);
                selectedId = copy.id;
            } else if (act === 'del') {
                list.splice(loc.index, 1);
                if (!locate(selectedId)) selectedId = null;
            }
        });

        if (!changed) return;
        renderCanvas();
        if (act === 'del') {
            renderPanel();
            // Undo is the safety net, so delete never asks first.
            toast(title + ' deleted', 'Undo', undo);
        }
    }

    // --- Clipboard ----------------------------------------------------------
    /**
     * Held in memory only, like the undo stack. The tree is already the
     * durable copy once it saves, and a block carries `html` the server
     * rendered — parking that in `localStorage` would give anyone who can
     * write to this origin a way to inject markup the save path never saw.
     */
    let clipboard = null;

    /** How tall `block` stands, counting its own root as 0. */
    function subtreeDepth(block) {
        if (!isContainer(block.widget)) return 0;
        const kids = block.children || [];
        if (!kids.length) return 0;
        return 1 + Math.max.apply(null, kids.map(subtreeDepth));
    }

    function copyBlock() {
        const loc = locate(selectedBlock());
        if (!loc) return;
        clipboard = cloneBlock(loc.block);
        flash(widgetTitle(loc.block.widget) + ' copied');
    }

    /** Copy and remove together, so one undo puts the block back. */
    function cutBlock() {
        const loc = locate(selectedBlock());
        if (!loc) return;

        clipboard = cloneBlock(loc.block);
        const title = widgetTitle(loc.block.widget);

        const changed = commit('Cut section', () => {
            loc.list.splice(loc.index, 1);
            if (!locate(selectedId)) selectedId = null;
        });
        if (!changed) return;

        renderCanvas();
        renderPanel();
        // Undo is the safety net, so cutting never asks first either.
        toast(title + ' cut', 'Undo', undo);
    }

    function pasteBlock() {
        if (!clipboard) { flash('Nothing copied yet'); return; }

        // Re-clone on every paste: `cloneBlock` mints fresh ids throughout,
        // so the same block can land on the page twice without colliding.
        const copy = cloneBlock(clipboard);
        let target = insertTarget(isContainer(copy.widget));

        // `insertTarget` only knows how deep the insertion point is, not how
        // tall the thing being inserted is — a copied section can carry
        // children of its own. When the two together outrun the budget, drop
        // it at the page root rather than let the sanitizer trip over it.
        if (target.depth + subtreeDepth(copy) > maxDepth - 1) {
            target = { list: blocks, index: blocks.length, depth: 0, flattened: true };
        }
        if (target.column !== undefined) copy.column = target.column;
        else delete copy.column;

        const changed = commit('Pasted section', () => {
            target.list.splice(target.index, 0, copy);
            activeColumn = target.column === undefined ? 0 : target.column;
            selectedId = copy.id;
        });
        if (!changed) return;

        renderCanvas();
        renderPanel();
        if (target.flattened) flash('Section added to the page — nesting limit reached.');
    }

    // --- Drag to reorder ---------------------------------------------------
    let dragId = null;

    const clearDropMarks = () => canvas
        .querySelectorAll('.pb-canvas-block, .pb-col')
        .forEach((el) => el.classList.remove('is-dragging', 'is-drop-before', 'is-drop-after', 'is-drop'));

    /** A section can't be dragged into itself or into one of its own children. */
    function isBlockedTarget(ownerId, dragged) {
        if (!ownerId) return false;
        if (ownerId === dragged.block.id) return true;
        return isContainer(dragged.block.widget) && containsBlock(dragged.block, ownerId);
    }

    canvas.addEventListener('dragstart', (e) => {
        const wrap = e.target.closest('.pb-canvas-block');
        if (!wrap) return;
        // A pending keystroke burst must not be folded into the move.
        flushCoalesce();
        dragId = wrap.dataset.id;
        wrap.classList.add('is-dragging');
        e.dataTransfer.effectAllowed = 'move';
        try { e.dataTransfer.setData('text/plain', dragId); } catch (_) {}
    });

    canvas.addEventListener('dragover', (e) => {
        if (!dragId) return;
        e.preventDefault();
        clearDropMarks();

        const dragged = locate(dragId);
        if (!dragged) return;

        const slot = e.target.closest('[data-pb-col]');
        const wrap = e.target.closest('.pb-canvas-block');

        // Hovering the empty part of a column: offering to drop inside it.
        if (slot && (!wrap || !slot.contains(wrap))) {
            const owner = slot.closest('.pb-canvas-block');
            if (!owner || isBlockedTarget(owner.dataset.id, dragged)) return;
            slot.classList.add('is-drop');
            return;
        }

        if (!wrap || wrap.dataset.id === dragId) return;
        if (isBlockedTarget(wrap.dataset.id, dragged)) return;

        const rect = wrap.getBoundingClientRect();
        const before = (e.clientY - rect.top) < rect.height / 2;
        wrap.classList.add(before ? 'is-drop-before' : 'is-drop-after');
    });

    canvas.addEventListener('dragleave', (e) => {
        const el = e.target.closest('.pb-canvas-block, .pb-col');
        if (el) el.classList.remove('is-drop-before', 'is-drop-after', 'is-drop');
    });

    canvas.addEventListener('drop', (e) => {
        e.preventDefault();
        clearDropMarks();
        if (!dragId) return;

        const dragged = locate(dragId);
        dragId = null;
        if (!dragged) return;

        // One gesture, one step — however many splices it takes.
        withGroup('Moved', () => {
            const slot = e.target.closest('[data-pb-col]');
            const wrap = e.target.closest('.pb-canvas-block');

            // Drop into a column: append after whatever already sits there.
            if (slot && (!wrap || !slot.contains(wrap))) {
                const owner = slot.closest('.pb-canvas-block');
                const ownerId = owner && owner.dataset.id;
                if (!ownerId || isBlockedTarget(ownerId, dragged)) return;
                const ownerLoc = locate(ownerId);
                if (!ownerLoc) return;

                const column = Number(slot.dataset.pbCol) || 0;
                const moved = dragged.list.splice(dragged.index, 1)[0];
                moved.column = column;

                const kids = ownerLoc.block.children || (ownerLoc.block.children = []);
                let at = kids.length;
                kids.forEach((c, i) => { if ((Number(c.column) || 0) === column) at = i + 1; });
                kids.splice(at, 0, moved);
                renderCanvas();
                return;
            }

            if (!wrap || wrap.dataset.id === dragId) return;
            if (isBlockedTarget(wrap.dataset.id, dragged)) return;
            if (!locate(wrap.dataset.id)) return;

            const rect = wrap.getBoundingClientRect();
            const before = (e.clientY - rect.top) < rect.height / 2;

            // Splice first, then re-locate: removing the block shifts the target's
            // index whenever the two share a list.
            const moved = dragged.list.splice(dragged.index, 1)[0];
            const landed = locate(wrap.dataset.id);
            if (!landed) { dragged.list.splice(dragged.index, 0, moved); return; }

            if (landed.parent) moved.column = Number(landed.block.column) || 0;
            else delete moved.column;

            landed.list.splice(landed.index + (before ? 0 : 1), 0, moved);
            renderCanvas();
        });
    });

    canvas.addEventListener('dragend', () => {
        clearDropMarks();
        dragId = null;
    });

    // --- Add / select ------------------------------------------------------
    /** Where the palette should land: inside the selected section, else the page. */
    function insertTarget(wantsContainer) {
        const sel = selectedId ? locate(selectedId) : null;
        if (!sel) return { list: blocks, index: blocks.length, depth: 0 };

        if (isContainer(sel.block.widget) && canNest(sel.depth)) {
            const column = insertColumn(sel.block);
            if (!Array.isArray(sel.block.children)) sel.block.children = [];
            return { list: sel.block.children, index: sel.block.children.length, column, depth: sel.depth + 1 };
        }

        if (!wantsContainer || canNest(sel.depth)) {
            return {
                list: sel.list,
                index: sel.index + 1,
                column: sel.depth > 0 ? (Number(sel.block.column) || 0) : undefined,
                depth: sel.depth,
            };
        }

        // Nesting budget spent — never silently drop the block on the floor.
        return { list: blocks, index: blocks.length, depth: 0, flattened: true };
    }

    async function addBlock(widget) {
        try {
            const schema = await loadSchema(widget);
            const container = isContainer(widget);
            const target = insertTarget(container);

            const block = { id: uid(), widget, settings: JSON.parse(JSON.stringify(schema.defaults || {})), html: '' };
            if (container) block.children = [];
            if (target.column !== undefined) block.column = target.column;

            commit('Added section', () => {
                target.list.splice(target.index, 0, block);
                activeColumn = target.column === undefined ? 0 : target.column;
                selectedId = block.id;
            });

            if (target.flattened) flash('Section added to the page — nesting limit reached.');

            renderCanvas();
            renderPanel();
            await refreshPreview(block);
        } catch (_) { /* ignore */ }
    }

    function selectBlock(id, slotColumn) {
        selectedId = id;
        const loc = locate(id);

        if (loc && loc.depth > 0) activeColumn = Number(loc.block.column) || 0;
        else if (slotColumn !== undefined && !Number.isNaN(slotColumn)) activeColumn = slotColumn;
        else activeColumn = 0;

        renderCanvas();
        renderPanel();
    }

    // --- Preview -----------------------------------------------------------
    async function refreshPreview(block) {
        try {
            const res = await fetch(urls.preview, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': urls.csrf },
                body: JSON.stringify({ widget: block.widget, settings: block.settings, page: meta.slug }),
            });
            if (!res.ok) return;
            const data = await res.json();
            block.html = data.html || '';

            // A section's own HTML is where its column slots live, so rebuild
            // the canvas rather than patching one body in place.
            if (isContainer(block.widget)) { renderCanvas(); return; }

            const wrap = canvas.querySelector(`[data-id="${CSS.escape(block.id)}"] > .pb-block-body`);
            if (wrap) wrap.innerHTML = block.html;

            // Fresh markup from the server carries its full responsive set
            // again, so put the preview back over it.
            applyDevicePreview();
        } catch (_) { /* ignore */ }
    }

    function queuePreview(block) {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(() => refreshPreview(block), 350);
    }

    // --- Settings panel ----------------------------------------------------
    async function renderPanel() {
        const block = byId(selectedId);
        if (!block) {
            panel.innerHTML = '<p class="text-sm text-muted">Select a section on the canvas to edit it, or add one from the palette.</p>';
            return;
        }
        let schema;
        try { schema = await loadSchema(block.widget); } catch (_) { panel.innerHTML = '<p class="text-sm text-accent">Could not load controls.</p>'; return; }
        const widgetMeta = palette.find((w) => w.name === block.widget) || {};
        const loc = locate(block.id);
        const holds = isContainer(block.widget);
        const cols = columnsOf(block.settings);

        const crumbs = ['<button type="button" data-crumb="">Page</button>']
            .concat(pathTo(block.id).map((b, i, path) => {
                const info = paletteByName[b.widget] || {};
                const current = i === path.length - 1;
                return `<button type="button" data-crumb="${esc(b.id)}"${current ? ' class="is-current"' : ''}>${esc(info.title || b.widget)}</button>`;
            }))
            .join('<span>/</span>');

        const hint = holds
            ? (cols > 1
                ? `<div class="pb-hint">Container — new sections drop into column <b>${insertColumn(block) + 1}</b> of ${cols}. Click another column to change it.</div>`
                : '<div class="pb-hint">Container — new sections drop in here.</div>')
            : (loc && loc.depth > 0
                ? `<div class="pb-hint">Inside <b>${esc((paletteByName[loc.parent.widget] || {}).title || 'section')}</b>, column ${((Number(block.column) || 0) + 1)} of ${columnsOf(loc.parent.settings)}.</div>`
                : '');

        // The switcher only earns its place on a widget that has something to
        // switch: it picks which tier of a responsive control the panel edits.
        const switcher = schema.controls.some((c) => c.responsive)
            ? `<div class="pb-device" role="group" aria-label="Device tier">${deviceTiers.map((t) => {
                const on = device === t;
                const label = t.charAt(0).toUpperCase() + t.slice(1);
                return `<button type="button" data-device="${esc(t)}" aria-pressed="${on}"${on ? ' class="is-on"' : ''}>${esc(label)}</button>`;
            }).join('')}</div>`
            : '';

        panel.innerHTML = `
            <nav class="pb-crumb">${crumbs}</nav>
            <p class="mb-4 font-display text-sm tracking-[0.1em] text-accent uppercase">${esc(widgetMeta.title || block.widget)}</p>
            ${hint}
            ${switcher}
            <div id="pb-controls">${schema.controls.map((c) => controlHtml(c, block.settings)).join('')}</div>`;
        panel.querySelectorAll('[data-device]').forEach((btn) => {
            btn.addEventListener('click', () => setDevice(btn.dataset.device));
        });
        // Sub-fields inside a repeater carry `data-sub` instead of `data-key`;
        // both drive the same mutation, so both need the listener. Image
        // controls are skipped — their hidden path input is written by the
        // delegated upload handler, not by typing.
        panel.querySelectorAll('[data-key], [data-sub]').forEach((el) => {
            if (el.classList.contains('pb-img-path') || el.hasAttribute('data-upload')) return;
            const ev = el.tagName === 'SELECT' || el.type === 'checkbox' ? 'change' : 'input';
            el.addEventListener(ev, () => onControlChange(el, block, schema.controls));
        });
        applyConditions(panel, block.settings);
    }

    function controlHtml(c, settings) {
        // Headings are panel furniture, not settings: no key, nothing binds.
        if (c.type === 'heading') {
            return `<p class="pb-field-heading">${esc(c.label)}</p>`;
        }

        const key = c.key;
        // A responsive control reads the tier the panel is editing. Desktop is
        // the base key itself; the other tiers are the siblings beside it, and
        // a tier with no sibling of its own inherits from desktop.
        const tier = c.responsive ? device : 'desktop';
        const storageKey = tier === 'desktop' ? key : key + (deviceSuffix[tier] || '');
        const hasOverride = tier === 'desktop' || Object.prototype.hasOwnProperty.call(settings, storageKey);
        const val = hasOverride ? settings[storageKey] : settings[key];
        const cond = c.condition ? ` data-cond='${esc(JSON.stringify(c.condition))}'` : '';
        const label = `<label class="field__label">${esc(c.label)}</label>`;
        const help = c.help ? `<p class="text-[11px] text-muted">${esc(c.help)}</p>` : '';

        if (c.type === 'repeater') {
            const rows = Array.isArray(val) ? val : [];
            return `<div class="field" data-control="repeater" data-key="${esc(key)}"${cond}>
                <div class="mb-2 flex items-center justify-between">${label}<button type="button" class="text-[10px] tracking-[0.16em] text-accent uppercase" data-add-row>Add +</button></div>
                <div data-rows>${rows.map((row, ri) => repeaterRowHtml(c, row, ri)).join('')}</div>
                ${help}
            </div>`;
        }

        let input;
        if (c.type === 'textarea') {
            input = `<textarea class="input" data-key="${esc(key)}" rows="3">${esc(val ?? '')}</textarea>`;
        } else if (c.type === 'select') {
            // A tier with no override must not *look* set: leaving every real
            // option unselected and offering Automatic in their place is what
            // keeps the displayed value from being mistaken for one the author
            // chose — and quietly saved the next time the control is touched.
            const canInherit = tier !== 'desktop';
            const shown = hasOverride ? val : '';
            const inherit = canInherit ? `<option value=""${hasOverride ? '' : ' selected'}>Automatic</option>` : '';
            input = `<select class="input" data-key="${esc(key)}">${inherit}${Object.entries(c.options || {}).map(([ov, ol]) => `<option value="${esc(ov)}"${String(shown) === String(ov) ? ' selected' : ''}>${esc(ol)}</option>`).join('')}</select>`;
        } else if (c.type === 'toggle') {
            input = `<label class="flex cursor-pointer items-center gap-3 border border-line p-3 has-[:checked]:border-accent"><input type="checkbox" data-key="${esc(key)}" class="h-4 w-4 accent-accent"${val ? ' checked' : ''}><span class="text-sm text-white">On</span></label>`;
        } else if (c.type === 'number') {
            input = `<input class="input" type="number" data-key="${esc(key)}" value="${esc(val ?? '')}">`;
        } else if (c.type === 'url') {
            // type=url gets the browser's link keyboard and its own validity
            // state; the placeholder covers both the absolute and site-local
            // forms the renderer accepts.
            input = `<input class="input" type="url" data-key="${esc(key)}" value="${esc(val ?? '')}" placeholder="https://… or /page" spellcheck="false" autocomplete="url">`;
        } else if (c.type === 'image') {
            input = `<div data-image="${esc(key)}">
                <input type="hidden" class="pb-img-path" data-key="${esc(key)}" value="${esc(val ?? '')}">
                <div class="mb-2 pb-img-preview">${val ? `<img src="${esc(publicUrl(val))}" class="h-20 w-auto border border-line" alt="">` : ''}</div>
                <input type="file" accept="image/*" class="text-xs text-muted" data-upload>
            </div>`;
        } else {
            input = `<input class="input" type="text" data-key="${esc(key)}" value="${esc(val ?? '')}">`;
        }

        return `<div class="field"${cond}>${label}${input}${help}</div>`;
    }

    function repeaterRowHtml(c, row, ri) {
        const fields = (c.fields || []).map((f) => {
            const v = row[f.key];
            let input;
            if (f.type === 'textarea') input = `<textarea class="input" data-sub="${esc(f.key)}" rows="2">${esc(v ?? '')}</textarea>`;
            else if (f.type === 'select') input = `<select class="input" data-sub="${esc(f.key)}">${Object.entries(f.options || {}).map(([ov, ol]) => `<option value="${esc(ov)}"${String(v) === String(ov) ? ' selected' : ''}>${esc(ol)}</option>`).join('')}</select>`;
            else if (f.type === 'number') input = `<input class="input" type="number" data-sub="${esc(f.key)}" value="${esc(v ?? '')}">`;
            else if (f.type === 'url') input = `<input class="input" type="url" data-sub="${esc(f.key)}" value="${esc(v ?? '')}" placeholder="https://… or /page" spellcheck="false" autocomplete="url">`;
            else if (f.type === 'image') input = `<div><input type="hidden" class="pb-img-path" data-sub="${esc(f.key)}" value="${esc(v ?? '')}"><div class="mb-2 pb-img-preview">${v ? `<img src="${esc(publicUrl(v))}" class="h-16 w-auto border border-line" alt="">` : ''}</div><input type="file" accept="image/*" class="text-xs text-muted" data-upload></div>`;
            else input = `<input class="input" type="text" data-sub="${esc(f.key)}" value="${esc(v ?? '')}">`;
            return `<div class="field"><label class="field__label">${esc(f.label)}</label>${input}</div>`;
        }).join('');
        return `<div class="pb-rep-row" data-row="${ri}">
            <div class="pb-rep-head"><span class="font-display text-[10px] tracking-[0.2em] text-muted uppercase">Item ${ri + 1}</span><button type="button" class="text-[10px] tracking-[0.16em] text-muted uppercase hover:text-accent" data-remove-row>Remove</button></div>
            ${fields}</div>`;
    }

    function publicUrl(path) {
        if (!path) return '';
        if (/^(https?:|\/)/.test(path)) return path;
        return '/storage/' + path;
    }

    function onControlChange(el, block, controls) {
        const key = el.dataset.key;
        const sub = el.dataset.sub;
        const wrapper = el.closest('[data-control="repeater"]');

        if (wrapper && el.closest('[data-rows]')) {
            // Repeater sub-field edit.
            const repKey = wrapper.dataset.key;
            const rowEl = el.closest('[data-row]');
            const ri = parseInt(rowEl.dataset.row, 10);
            const field = (controls.find((c) => c.key === repKey)?.fields || []).find((f) => f.key === sub);
            block.settings[repKey] = block.settings[repKey] || [];
            block.settings[repKey][ri] = block.settings[repKey][ri] || {};
            block.settings[repKey][ri][sub] = coerce(el, field);
        } else if (wrapper && el.dataset.addRow !== undefined) {
            return;
        } else if (key) {
            const field = controls.find((c) => c.key === key);
            const tier = field && field.responsive ? device : 'desktop';
            const storageKey = tier === 'desktop' ? key : key + (deviceSuffix[tier] || '');

            if (tier !== 'desktop' && el.value === '') {
                // "Automatic" is the absence of an override, so the key goes
                // away rather than being written as an empty string — a blank
                // value would be coerced to the control default and inherit
                // nothing.
                delete block.settings[storageKey];
            } else {
                block.settings[storageKey] = coerce(el, field);
            }

            applyConditions(panel, block.settings);
            // The insert-column hint quotes the desktop count, so only a
            // change to that tier needs the panel redrawn behind it.
            if (tier === 'desktop' && key === 'columns') renderPanel();
        }

        coalesce('Edited ' + ((paletteByName[block.widget] || {}).title || block.widget));
        queuePreview(block);
        queueSave();
    }

    function coerce(el, field) {
        if (!field) return el.value;
        if (field.type === 'toggle') return el.checked;
        if (field.type === 'number') return el.value === '' ? '' : Number(el.value);
        return el.value;
    }

    function applyConditions(panel, settings) {
        panel.querySelectorAll('[data-cond]').forEach((el) => {
            const cond = JSON.parse(el.dataset.cond);
            const show = Object.entries(cond).every(([k, expected]) => String(settings[k]) === String(expected));
            el.style.display = show ? '' : 'none';
        });
    }

    // Breadcrumbs + repeater rows (event delegation on panel).
    panel.addEventListener('click', (e) => {
        const crumb = e.target.closest('[data-crumb]');
        if (crumb) {
            const id = crumb.dataset.crumb;
            if (id) selectBlock(id);
            else { selectedId = null; activeColumn = 0; renderCanvas(); renderPanel(); }
            return;
        }

        const block = byId(selectedId);
        if (!block) return;
        const addBtn = e.target.closest('[data-add-row]');
        const removeBtn = e.target.closest('[data-remove-row]');
        if (addBtn) {
            const wrapper = addBtn.closest('[data-control="repeater"]');
            const repKey = wrapper.dataset.key;
            const schemaCtrl = schemaCache[block.widget].controls.find((c) => c.key === repKey);
            const rowDefaults = {};
            (schemaCtrl.fields || []).forEach((f) => { rowDefaults[f.key] = f.default ?? (f.type === 'number' ? 0 : ''); });
            commit('Added item', () => {
                block.settings[repKey] = block.settings[repKey] || [];
                block.settings[repKey].push(rowDefaults);
            });
            renderPanel();
            queuePreview(block);
        } else if (removeBtn) {
            const wrapper = removeBtn.closest('[data-control="repeater"]');
            const repKey = wrapper.dataset.key;
            const rowEl = removeBtn.closest('[data-row]');
            const ri = parseInt(rowEl.dataset.row, 10);
            commit('Removed item', () => {
                (block.settings[repKey] || []).splice(ri, 1);
            });
            renderPanel();
            queuePreview(block);
        }
    });

    panel.addEventListener('change', async (e) => {
        const input = e.target.closest('[data-upload]');
        if (!input || !input.files || !input.files[0]) return;
        const block = byId(selectedId);
        if (!block) return;
        const holder = input.closest('[data-image]') || input.closest('.field');
        const pathInput = holder.querySelector('.pb-img-path');
        const formData = new FormData();
        formData.append('image', input.files[0]);
        try {
            const res = await fetch(urls.upload, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': urls.csrf }, body: formData });
            if (!res.ok) return;
            const data = await res.json();
            pathInput.value = data.path || '';
            const prev = holder.querySelector('.pb-img-preview');
            if (prev) prev.innerHTML = data.url ? `<img src="${esc(data.url)}" class="h-20 w-auto border border-line" alt="">` : '';
            // Write the path back into settings.
            const sub = pathInput.dataset.sub;
            const key = pathInput.dataset.key;
            commit('Uploaded image', () => {
                if (sub) {
                    const wrapper = pathInput.closest('[data-control="repeater"]');
                    const repKey = wrapper?.dataset.key;
                    const ri = parseInt(pathInput.closest('[data-row]')?.dataset.row ?? '-1', 10);
                    if (repKey && ri >= 0) block.settings[repKey][ri][sub] = data.path || '';
                } else if (key) {
                    block.settings[key] = data.path || '';
                }
            });
            queuePreview(block);
        } catch (_) { /* ignore */ }
    });

    // --- Save --------------------------------------------------------------
    /** Emit the tree the sanitizer expects: children only hang off containers. */
    function serializeBlocks(list, nested) {
        return list.map((b) => {
            const node = { id: b.id, widget: b.widget, settings: b.settings };
            if (nested && b.column !== undefined) node.column = Number(b.column) || 0;
            if (isContainer(b.widget)) node.children = serializeBlocks(b.children || [], true);
            return node;
        });
    }

    function queueSave() {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(save, 800);
    }

    async function save() {
        saveStatus.textContent = 'Saving…';
        document.getElementById('pb-f-schema').value = JSON.stringify({ blocks: serializeBlocks(blocks, false) });
        try {
            const res = await fetch(urls.update, {
                method: 'POST',
                // Form-encoded on purpose: StorePageRequest decodes the schema
                // out of the string field, and labelling it application/json
                // would hide _method=PUT from the router.
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': urls.csrf },
                body: new URLSearchParams(new FormData(document.getElementById('pb-save-form'))),
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok) {
                saveStatus.textContent = 'Saved ✓';
                setTimeout(() => { saveStatus.textContent = ''; }, 2000);
            } else {
                saveStatus.textContent = 'Save failed';
                if (data && data.errors) console.error(data.errors);
            }
        } catch (_) { saveStatus.textContent = 'Save failed'; }
    }

    document.getElementById('pb-save').addEventListener('click', save);

    // --- Undo / redo -------------------------------------------------------
    document.getElementById('pb-undo').addEventListener('click', undo);
    document.getElementById('pb-redo').addEventListener('click', redo);

    // --- Device preview ----------------------------------------------------
    document.querySelectorAll('[data-preview-device]').forEach((btn) => {
        btn.addEventListener('click', () => setDevice(btn.dataset.previewDevice));
    });
    // A device remembered from last time is already holding the canvas by the
    // time renderCanvas() runs below; only the switcher still reads Desktop.
    syncPreviewButtons();

    // --- Keyboard shortcuts ------------------------------------------------
    const isMac = /Mac|iP(hone|ad|od)/.test(navigator.platform || navigator.userAgent);
    const modKey = isMac ? 'metaKey' : 'ctrlKey';

    /**
     * One table for every shortcut. The help sheet is rendered straight from
     * it, so the two can't drift apart. `mod` is Cmd on macOS, Ctrl elsewhere.
     */
    const shortcuts = [];

    function shortcut(combo, title, when, run) {
        shortcuts.push({ combo, title, when: when || (() => true), run });
    }

    function comboLabel(combo) {
        const names = isMac ? { mod: '⌘', shift: '⇧', alt: '⌥' } : { mod: 'Ctrl', shift: 'Shift', alt: 'Alt' };
        const parts = combo.split('+');
        const key = parts.pop();
        // Word keys read better title-cased; single letters stay shouted.
        const label = key.length > 1
            ? key.charAt(0).toUpperCase() + key.slice(1)
            : (key === '?' ? '?' : key.toUpperCase());
        return parts.map((p) => names[p] || p.toUpperCase()).concat(label).join(isMac ? '' : '+');
    }

    function matchShortcut(e) {
        const want = e.key.toLowerCase();
        return shortcuts.find((s) => {
            const parts = s.combo.toLowerCase().split('+');
            const key = parts.pop();
            if (key !== want) return false;
            if (parts.includes('mod') !== !!e[modKey]) return false;
            if (parts.includes('alt') !== !!e.altKey) return false;

            // `?` reaches us as Shift+/ on most layouts, so shift is only
            // pinned for letters and digits — there e.key's case already
            // carries it, and Ctrl+Shift+Z has to stay distinct from Ctrl+Z.
            const wantsShift = parts.includes('shift');
            if (/^[a-z0-9]$/.test(key) && !!e.shiftKey !== wantsShift) return false;
            if (wantsShift && !e.shiftKey) return false;
            return true;
        });
    }

    /** The selection is only real while the block is still in the tree. */
    const selectedBlock = () => (selectedId && locate(selectedId) ? selectedId : null);

    /**
     * True while a text field owns the keyboard. Read live from
     * `document.activeElement` rather than taken from the event, because the
     * right-click menu asks the same question long after the last keystroke.
     */
    const typingInField = () => {
        const el = document.activeElement;
        return !!el && (/^(INPUT|TEXTAREA|SELECT)$/.test(el.tagName) || el.isContentEditable);
    };

    /**
     * The predicate shared by every block-scoped action, compared by identity
     * rather than by behaviour. That lets the right-click menu select exactly
     * the entries which act on the selection — no second list to keep in step.
     *
     * It also stands down while a field is focused. Copy, cut and paste are
     * the only shortcuts here that a text field genuinely needs, and the
     * handler lets modifier combinations through — without this they would
     * move the selected block instead of the selected text.
     */
    const onSelection = () => !!selectedBlock() && !typingInField();

    function toggleHelp() {
        closeContextMenu();
        const el = document.getElementById('pb-help');
        if (!el.hidden) { el.hidden = true; return; }
        document.getElementById('pb-help-rows').innerHTML = shortcuts.map((s) => `
            <tr><td>${esc(comboLabel(s.combo))}</td><td>${esc(s.title)}</td></tr>`).join('');
        el.hidden = false;
    }

    document.addEventListener('keydown', (e) => {
        const s = matchShortcut(e);
        if (!s || !s.when()) return;
        // A focused field owns the plain keys, so Delete can't eat a
        // keystroke mid-word. Modifier combos still win — undo and redo have
        // no `when` of their own, and the block-scoped ones stand down
        // because `onSelection` checks the same thing.
        const typing = /^(INPUT|TEXTAREA|SELECT)$/.test(e.target.tagName) || e.target.isContentEditable;
        if (typing && !e[modKey] && !e.ctrlKey && !e.metaKey) return;
        e.preventDefault();
        // The menu is a transient affordance, and the tree can change under it
        // the moment an action runs — so any key that acts dismisses it too.
        closeContextMenu();
        s.run();
    });

    shortcut('mod+z', 'Undo', null, undo);
    shortcut('mod+shift+z', 'Redo', null, redo);
    shortcut('mod+y', 'Redo', null, redo);
    // `matchShortcut` lowercases every combo, so the casing here only decides
    // how the key is spelled in the help sheet and the right-click menu.
    shortcut('mod+c', 'Copy selected block', onSelection, copyBlock);
    shortcut('mod+x', 'Cut selected block', onSelection, cutBlock);
    shortcut('mod+v', 'Paste copied block', onSelection, pasteBlock);
    shortcut('mod+d', 'Duplicate selected block', onSelection, () => handleAction(selectedBlock(), 'dup'));
    shortcut('alt+ArrowUp', 'Move block up', onSelection, () => handleAction(selectedBlock(), 'up'));
    shortcut('alt+ArrowDown', 'Move block down', onSelection, () => handleAction(selectedBlock(), 'down'));
    shortcut('delete', 'Delete selected block', onSelection, () => handleAction(selectedBlock(), 'del'));
    shortcut('backspace', 'Delete selected block', onSelection, () => handleAction(selectedBlock(), 'del'));
    shortcut('mod+alt+o', 'Toggle page structure', null, toggleTree);
    shortcut('?', 'Show this sheet', null, toggleHelp);
    shortcut('escape', 'Close this sheet', () => !document.getElementById('pb-help').hidden, toggleHelp);

    document.getElementById('pb-help-open').addEventListener('click', toggleHelp);
    document.getElementById('pb-tree-open').addEventListener('click', toggleTree);
    document.getElementById('pb-help').addEventListener('click', (e) => {
        if (e.target.id === 'pb-help' || e.target.closest('[data-close]')) {
            document.getElementById('pb-help').hidden = true;
        }
    });

    // --- Right-click menu ---------------------------------------------------
    // Every row is drawn from the shortcut table by the shared selection
    // predicate, so the menu cannot offer an action the keyboard cannot also
    // do, and cannot drift away from the help sheet.
    const menu = document.getElementById('pb-menu');
    let menuRows = [];

    function closeContextMenu() {
        menu.hidden = true;
        menuRows = [];
    }

    function openContextMenu(x, y) {
        // Several keys can drive one action (Delete and Backspace both delete).
        // The help sheet lists every binding because it documents keys; this
        // menu documents actions, so each one appears once, under the key it
        // is listed with first.
        const seen = new Set();
        const rows = shortcuts.filter((s) => {
            if (s.when !== onSelection || !s.when()) return false;
            if (seen.has(s.title)) return false;
            seen.add(s.title);
            return true;
        });

        // Nothing is selected, so there is nothing worth offering.
        if (!rows.length) { closeContextMenu(); return; }

        menuRows = rows;
        menu.innerHTML = rows.map((s, i) => `
            <button type="button" data-menu-row="${i}">
                <span>${esc(s.title)}</span>
                <kbd>${esc(comboLabel(s.combo))}</kbd>
            </button>`).join('');

        menu.hidden = false;
        menu.style.left = x + 'px';
        menu.style.top = y + 'px';
        clampMenu();
        // The panel can grow once the display web font swaps in, after the
        // first measurement, so clamp again when layout has settled and
        // whenever the window changes size.
        requestAnimationFrame(clampMenu);
        if (document.fonts) document.fonts.ready.then(clampMenu);
        menu.querySelector('button').focus({ preventScroll: true });
    }

    /**
     * Pull the menu back inside the viewport. Written as a delta against its
     * own rect rather than as an absolute reposition, so calling it again
     * after a resize or a font swap can never move a menu that is already
     * where it should be.
     */
    function clampMenu() {
        if (menu.hidden) return;
        const box = menu.getBoundingClientRect();
        const dx = Math.min(0, window.innerWidth - box.right - 8) + Math.max(0, 8 - box.left);
        const dy = Math.min(0, window.innerHeight - box.bottom - 8) + Math.max(0, 8 - box.top);
        if (dx) menu.style.left = (box.left + dx) + 'px';
        if (dy) menu.style.top = (box.top + dy) + 'px';
    }

    canvas.addEventListener('contextmenu', (e) => {
        const wrap = e.target.closest('.pb-canvas-block');
        // Only the canvas swallows the native menu. The rest of the admin
        // keeps it — taking "Inspect element" away from a developer helps
        // nobody.
        if (!wrap) return;
        e.preventDefault();

        // Right-clicking a different block moves the selection with it.
        const id = wrap.dataset.id;
        if (id !== selectedId) selectBlock(id);

        // And it releases whatever field had focus. Without this, a panel
        // input still holding focus would leave `onSelection` declining every
        // row, so right-clicking the block you are already editing would open
        // nothing at all.
        if (document.activeElement && document.activeElement.blur) document.activeElement.blur();

        openContextMenu(e.clientX, e.clientY);
    });

    menu.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-menu-row]');
        if (!btn) return;
        const row = menuRows[Number(btn.dataset.menuRow)];
        closeContextMenu();
        if (row) row.run();
    });

    // Anything that isn't the menu itself dismisses it.
    document.addEventListener('click', (e) => {
        if (!menu.hidden && !e.target.closest('#pb-menu')) closeContextMenu();
    });
    document.addEventListener('scroll', () => { if (!menu.hidden) closeContextMenu(); }, true);
    window.addEventListener('resize', clampMenu);
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        // Capture phase, so Escape unwinds one layer at a time — the
        // right-click menu first, the structure drawer next — before the
        // shortcut layer claims it for the help sheet.
        if (!menu.hidden) { e.stopPropagation(); closeContextMenu(); return; }
        if (!treeEl.hidden) { e.stopPropagation(); treeEl.hidden = true; }
    }, true);

    // --- Boot --------------------------------------------------------------
    renderPalette();
    renderCanvas();
    // Seed the stack so the first undo has somewhere to go.
    undoStack.steps.push(Object.assign(snapshot(), { label: 'Opened' }));
    undoStack.index = 0;
    updateHistoryButtons();
})();
</script>
@endpush
