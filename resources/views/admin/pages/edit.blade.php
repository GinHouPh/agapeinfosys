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
        .pb-canvas .section { pointer-events: none; }
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
    let blocks = initialBlocks.map((b) => ({ id: b.id, widget: b.widget, settings: b.settings || {}, html: b.html || '' }));
    let selectedId = null;
    const schemaCache = {};

    const canvas = document.getElementById('pb-canvas');
    const panel = document.getElementById('pb-panel');
    const paletteEl = document.getElementById('pb-palette');
    const countEl = document.getElementById('pb-count');
    const saveStatus = document.getElementById('pb-save-status');
    let previewTimer = null;
    let saveTimer = null;

    const uid = () => Math.random().toString(36).slice(2, 12);
    const byId = (id) => blocks.find((b) => b.id === id);
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

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
        countEl.textContent = blocks.length === 1 ? '1 section' : `${blocks.length} sections`;
        if (blocks.length === 0) {
            canvas.innerHTML = '<div class="pb-empty">No sections yet — add one from the palette on the left.</div>';
            return;
        }
        canvas.innerHTML = blocks.map((b) => `
            <div class="pb-canvas-block${b.id === selectedId ? ' is-selected' : ''}" data-id="${esc(b.id)}" draggable="true">
                <div class="pb-block-toolbar">
                    <button type="button" data-act="up" title="Move up">↑</button>
                    <button type="button" data-act="down" title="Move down">↓</button>
                    <button type="button" data-act="dup" title="Duplicate">⧉</button>
                    <button type="button" data-act="del" title="Delete">✕</button>
                </div>
                <div class="pb-block-body">${b.html || ''}</div>
            </div>`).join('');
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
        selectBlock(id);
    });

    function handleAction(id, act) {
        const i = blocks.findIndex((b) => b.id === id);
        if (i < 0) return;
        if (act === 'up' && i > 0) { [blocks[i - 1], blocks[i]] = [blocks[i], blocks[i - 1]]; }
        else if (act === 'down' && i < blocks.length - 1) { [blocks[i + 1], blocks[i]] = [blocks[i], blocks[i + 1]]; }
        else if (act === 'dup') {
            const copy = { ...blocks[i], id: uid(), settings: JSON.parse(JSON.stringify(blocks[i].settings)) };
            blocks.splice(i + 1, 0, copy);
            selectedId = copy.id;
        } else if (act === 'del') {
            blocks.splice(i, 1);
            if (selectedId === id) { selectedId = null; renderPanel(); }
        }
        renderCanvas();
        queueSave();
    }

    // --- Drag to reorder ---------------------------------------------------
    let dragId = null;
    canvas.addEventListener('dragstart', (e) => {
        const wrap = e.target.closest('.pb-canvas-block');
        if (!wrap) return;
        dragId = wrap.dataset.id;
        wrap.classList.add('is-dragging');
        e.dataTransfer.effectAllowed = 'move';
        try { e.dataTransfer.setData('text/plain', dragId); } catch (_) {}
    });
    canvas.addEventListener('dragover', (e) => {
        e.preventDefault();
        const wrap = e.target.closest('.pb-canvas-block');
        document.querySelectorAll('.pb-canvas-block').forEach((el) => el.classList.remove('is-drop-before', 'is-drop-after'));
        if (!wrap || wrap.dataset.id === dragId) return;
        const rect = wrap.getBoundingClientRect();
        const before = (e.clientY - rect.top) < rect.height / 2;
        wrap.classList.add(before ? 'is-drop-before' : 'is-drop-after');
    });
    canvas.addEventListener('dragleave', (e) => {
        const wrap = e.target.closest('.pb-canvas-block');
        if (wrap) wrap.classList.remove('is-drop-before', 'is-drop-after');
    });
    canvas.addEventListener('drop', (e) => {
        e.preventDefault();
        const wrap = e.target.closest('.pb-canvas-block');
        document.querySelectorAll('.pb-canvas-block').forEach((el) => el.classList.remove('is-dragging', 'is-drop-before', 'is-drop-after'));
        if (!wrap || !dragId || wrap.dataset.id === dragId) { dragId = null; return; }
        const targetId = wrap.dataset.id;
        const from = blocks.findIndex((b) => b.id === dragId);
        let to = blocks.findIndex((b) => b.id === targetId);
        if (from < 0 || to < 0) { dragId = null; return; }
        const rect = wrap.getBoundingClientRect();
        const before = (e.clientY - rect.top) < rect.height / 2;
        const [moved] = blocks.splice(from, 1);
        to = blocks.findIndex((b) => b.id === targetId);
        blocks.splice(before ? to : to + 1, 0, moved);
        dragId = null;
        renderCanvas();
        queueSave();
    });
    canvas.addEventListener('dragend', () => {
        document.querySelectorAll('.pb-canvas-block').forEach((el) => el.classList.remove('is-dragging', 'is-drop-before', 'is-drop-after'));
        dragId = null;
    });

    // --- Add / select ------------------------------------------------------
    async function addBlock(widget) {
        try {
            const schema = await loadSchema(widget);
            const block = { id: uid(), widget, settings: JSON.parse(JSON.stringify(schema.defaults || {})), html: '' };
            blocks.push(block);
            selectedId = block.id;
            renderCanvas();
            renderPanel();
            await refreshPreview(block);
        } catch (_) { /* ignore */ }
    }

    async function selectBlock(id) {
        selectedId = id;
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
            const wrap = canvas.querySelector(`[data-id="${CSS.escape(block.id)}"] .pb-block-body`);
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
        panel.innerHTML = `
            <p class="mb-4 font-display text-sm tracking-[0.1em] text-accent uppercase">${esc(widgetMeta.title || block.widget)}</p>
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
        }

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

    // Add / remove repeater rows + image uploads (event delegation on panel).
    panel.addEventListener('click', (e) => {
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
            block.settings[repKey] = block.settings[repKey] || [];
            block.settings[repKey].push(rowDefaults);
            renderPanel();
            queuePreview(block);
            queueSave();
        } else if (removeBtn) {
            const wrapper = removeBtn.closest('[data-control="repeater"]');
            const repKey = wrapper.dataset.key;
            const rowEl = removeBtn.closest('[data-row]');
            const ri = parseInt(rowEl.dataset.row, 10);
            (block.settings[repKey] || []).splice(ri, 1);
            renderPanel();
            queuePreview(block);
            queueSave();
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
            if (sub) {
                const wrapper = pathInput.closest('[data-control="repeater"]');
                const repKey = wrapper?.dataset.key;
                const ri = parseInt(pathInput.closest('[data-row]')?.dataset.row ?? '-1', 10);
                if (repKey && ri >= 0) block.settings[repKey][ri][sub] = data.path || '';
            } else if (key) {
                block.settings[key] = data.path || '';
            }
            queuePreview(block);
            queueSave();
        } catch (_) { /* ignore */ }
    });

    // --- Save --------------------------------------------------------------
    function queueSave() {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(save, 800);
    }

    async function save() {
        saveStatus.textContent = 'Saving…';
        document.getElementById('pb-f-schema').value = JSON.stringify({ blocks: blocks.map((b) => ({ id: b.id, widget: b.widget, settings: b.settings })) });
        try {
            const res = await fetch(urls.update, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': urls.csrf },
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

    // --- Boot --------------------------------------------------------------
    renderPalette();
    renderCanvas();
})();
</script>
@endpush
