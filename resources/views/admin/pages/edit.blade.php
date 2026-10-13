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
        .pb-chip { display: inline-block; margin-top: 4px; padding: 1px 5px; border: 1px solid var(--color-line); font-size: 9px; letter-spacing: .16em; text-transform: uppercase; color: var(--color-accent); }
        .pb-history button { padding: 4px 9px; line-height: 1.4; transition: color .15s, border-color .15s; }
        .pb-history button[disabled] { opacity: .3; cursor: not-allowed; }
        .pb-history button:not([disabled]):hover { border-color: var(--color-accent); color: var(--color-accent); }
        /* Both overlays below are flex containers, so the `hidden` attribute
           needs a rule of its own to actually hide them. */
        .pb-toast[hidden], .pb-help[hidden] { display: none; }
        .pb-toast {
            position: fixed; right: 24px; bottom: 24px; z-index: 50;
            display: flex; align-items: center; gap: 16px;
            padding: 12px 16px; border: 1px solid var(--color-line); background: var(--color-surface);
            font-size: 12px; color: var(--color-body); box-shadow: 0 10px 30px rgba(0, 0, 0, .5);
        }
        .pb-toast button { font-size: 10px; letter-spacing: .18em; text-transform: uppercase; color: var(--color-accent); }
        .pb-toast button:hover { text-decoration: underline; }
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
        <div class="mb-3 flex items-center justify-between">
            <h2 class="font-display text-xs tracking-[0.24em] text-muted uppercase">Canvas</h2>
            <span id="pb-count" class="text-xs text-muted"></span>
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

    // --- Canvas ------------------------------------------------------------
    function renderCanvas() {
        const total = countBlocks();
        countEl.textContent = total === 1 ? '1 section' : `${total} sections`;
        if (blocks.length === 0) {
            canvas.innerHTML = '<div class="pb-empty">No sections yet — add one from the palette on the left.</div>';
            return;
        }
        canvas.innerHTML = '';
        blocks.forEach((b) => canvas.appendChild(buildBlockEl(b)));
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
        const title = (paletteByName[loc.block.widget] || {}).title || loc.block.widget;

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

        panel.innerHTML = `
            <nav class="pb-crumb">${crumbs}</nav>
            <p class="mb-4 font-display text-sm tracking-[0.1em] text-accent uppercase">${esc(widgetMeta.title || block.widget)}</p>
            ${hint}
            <div id="pb-controls">${schema.controls.map((c) => controlHtml(c, block.settings)).join('')}</div>`;
        panel.querySelectorAll('[data-key]').forEach((el) => {
            const ev = el.tagName === 'SELECT' || el.type === 'checkbox' ? 'change' : 'input';
            el.addEventListener(ev, () => onControlChange(el, block, schema.controls));
        });
        applyConditions(panel, block.settings);
    }

    function controlHtml(c, settings) {
        const key = c.key;
        const val = settings[key];
        const cond = c.condition ? ` data-cond='${esc(JSON.stringify(c.condition))}'` : '';
        const label = `<label class="field__label">${esc(c.label)}${c.type === 'repeater' ? '' : ''}</label>`;
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
            input = `<select class="input" data-key="${esc(key)}">${Object.entries(c.options || {}).map(([ov, ol]) => `<option value="${esc(ov)}"${String(val) === String(ov) ? ' selected' : ''}>${esc(ol)}</option>`).join('')}</select>`;
        } else if (c.type === 'toggle') {
            input = `<label class="flex cursor-pointer items-center gap-3 border border-line p-3 has-[:checked]:border-accent"><input type="checkbox" data-key="${esc(key)}" class="h-4 w-4 accent-accent"${val ? ' checked' : ''}><span class="text-sm text-white">On</span></label>`;
        } else if (c.type === 'number') {
            input = `<input class="input" type="number" data-key="${esc(key)}" value="${esc(val ?? '')}">`;
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
            block.settings[key] = coerce(el, field);
            applyConditions(panel, block.settings);
            // The insert-column hint quotes the column count; keep it honest.
            if (key === 'columns') renderPanel();
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

    function toggleHelp() {
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
        // keystroke mid-word. Modifier combos still win, because document
        // history is the more useful undo once a block edit has been made.
        const typing = /^(INPUT|TEXTAREA|SELECT)$/.test(e.target.tagName) || e.target.isContentEditable;
        if (typing && !e[modKey] && !e.ctrlKey && !e.metaKey) return;
        e.preventDefault();
        s.run();
    });

    shortcut('mod+z', 'Undo', null, undo);
    shortcut('mod+shift+z', 'Redo', null, redo);
    shortcut('mod+y', 'Redo', null, redo);
    shortcut('delete', 'Delete selected block', () => !!selectedBlock(), () => handleAction(selectedBlock(), 'del'));
    shortcut('backspace', 'Delete selected block', () => !!selectedBlock(), () => handleAction(selectedBlock(), 'del'));
    shortcut('mod+d', 'Duplicate selected block', () => !!selectedBlock(), () => handleAction(selectedBlock(), 'dup'));
    shortcut('?', 'Show this sheet', null, toggleHelp);
    shortcut('escape', 'Close this sheet', () => !document.getElementById('pb-help').hidden, toggleHelp);

    document.getElementById('pb-help-open').addEventListener('click', toggleHelp);
    document.getElementById('pb-help').addEventListener('click', (e) => {
        if (e.target.id === 'pb-help' || e.target.closest('[data-close]')) {
            document.getElementById('pb-help').hidden = true;
        }
    });

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
