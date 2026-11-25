/**
 * Visual 3-pane Form Editor (Palette -> Live Canvas -> Field Inspector).
 *
 * Reads initial state from #fe-config JSON and persists changes atomically
 * via PUT /admin/forms/{form}/builder.
 */
(() => {
    'use strict';

    const configEl = document.getElementById('fe-config');
    if (!configEl) return;

    const config = JSON.parse(configEl.textContent);
    const fieldTypes = config.fieldTypes || [];
    const templates = config.templates || [];
    const urls = config.urls || {};

    const typeMap = {};
    fieldTypes.forEach((t) => { typeMap[t.value] = t; });

    const CATEGORIES = [
        { key: 'basic', label: 'Basic Fields' },
        { key: 'choice', label: 'Choice Fields' },
        { key: 'advanced', label: 'Advanced' },
        { key: 'layout', label: 'Layout & Content' },
        { key: 'compliance', label: 'Compliance' },
    ];

    const OPERATORS = [
        { value: 'equals', label: 'Equals' },
        { value: 'not_equals', label: 'Not equals' },
        { value: 'contains', label: 'Contains' },
        { value: 'not_contains', label: 'Does not contain' },
        { value: 'greater_than', label: 'Greater than' },
        { value: 'less_than', label: 'Less than' },
        { value: 'is_filled', label: 'Is filled' },
        { value: 'is_empty', label: 'Is empty' },
    ];

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
    }[c]));

    const uid = () => 'tmp_' + Math.random().toString(36).slice(2, 10);

    const slugifyName = (label) => String(label || '')
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '') || ('field_' + Math.random().toString(36).slice(2, 6));

    function normalizeField(raw) {
        return {
            _uid: raw._uid || (raw.id ? 'id_' + raw.id : uid()),
            id: raw.id || null,
            label: raw.label || 'Untitled Field',
            name: raw.name || '',
            type: raw.type || 'text',
            group: raw.group || '',
            section_id: raw.section_id || '',
            options: Array.isArray(raw.options) ? [...raw.options] : (typeMap[raw.type]?.hasOptions ? ['Option 1', 'Option 2'] : []),
            has_text_input: Boolean(raw.has_text_input),
            text_placeholder: raw.text_placeholder || '',
            placeholder: raw.placeholder || '',
            help_text: raw.help_text || '',
            default_value: raw.default_value || '',
            width: ['full', 'half', 'third'].includes(raw.width) ? raw.width : 'full',
            css_class: raw.css_class || '',
            validation: Array.isArray(raw.validation) ? [...raw.validation] : [],
            condition: Array.isArray(raw.condition) ? raw.condition.map((c) => ({ ...c })) : [],
            condition_match: raw.condition_match === 'any' ? 'any' : 'all',
            file_settings: raw.file_settings ? { ...raw.file_settings } : { mimes: ['pdf', 'doc', 'docx'], max_kilobytes: 5120 },
            is_required: Boolean(raw.is_required),
        };
    }

    // --- State & History ---------------------------------------------------
    let fields = (config.initialFields || []).map(normalizeField);
    let selectedUid = fields[0]?._uid || null;
    let inspectorTab = 'general';
    let device = 'desktop';
    let dirty = false;
    let saving = false;
    let dragState = null;

    const history = { past: [], future: [] };
    const snapshot = () => JSON.stringify(fields);

    function commitHistory(beforeSnap) {
        const afterSnap = snapshot();
        if (beforeSnap === afterSnap) return;
        history.past.push(beforeSnap);
        if (history.past.length > 50) history.past.shift();
        history.future = [];
        markDirty(true);
        updateHistoryButtons();
    }

    function undo() {
        if (history.past.length === 0) return;
        history.future.push(snapshot());
        fields = JSON.parse(history.past.pop());
        if (!fields.some((f) => f._uid === selectedUid)) {
            selectedUid = fields[0]?._uid || null;
        }
        markDirty(true);
        updateHistoryButtons();
        renderAll();
    }

    function redo() {
        if (history.future.length === 0) return;
        history.past.push(snapshot());
        fields = JSON.parse(history.future.pop());
        if (!fields.some((f) => f._uid === selectedUid)) {
            selectedUid = fields[0]?._uid || null;
        }
        markDirty(true);
        updateHistoryButtons();
        renderAll();
    }

    // --- DOM Refs ----------------------------------------------------------
    const paletteEl = document.getElementById('fe-palette');
    const paletteSearchEl = document.getElementById('fe-palette-search');
    const canvasEl = document.getElementById('fe-canvas');
    const canvasWrapEl = document.getElementById('fe-canvas-wrap');
    const inspectorEl = document.getElementById('fe-inspector');
    const countEl = document.getElementById('fe-count');
    const statusEl = document.getElementById('fe-save-status');
    const saveBtn = document.getElementById('fe-save');
    const undoBtn = document.getElementById('fe-undo');
    const redoBtn = document.getElementById('fe-redo');
    const toastEl = document.getElementById('fe-toast');
    const toastMsgEl = document.getElementById('fe-toast-msg');

    let toastTimer = null;
    function toast(msg, isError = false) {
        if (!toastEl || !toastMsgEl) return;
        toastMsgEl.textContent = msg;
        toastEl.classList.toggle('border-accent', isError);
        toastEl.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { toastEl.hidden = true; }, 3500);
    }

    function markDirty(isDirty) {
        dirty = isDirty;
        if (statusEl) {
            statusEl.textContent = dirty ? 'Unsaved changes' : 'All changes saved';
            statusEl.className = dirty ? 'text-xs text-accent' : 'text-xs text-muted';
        }
    }

    function updateHistoryButtons() {
        if (undoBtn) undoBtn.disabled = history.past.length === 0;
        if (redoBtn) redoBtn.disabled = history.future.length === 0;
    }

    // --- Mode Tabs (Builder / Settings / Templates / Embed) ----------------
    document.querySelectorAll('[data-fe-mode]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const mode = btn.dataset.feMode;
            document.querySelectorAll('[data-fe-mode]').forEach((b) => {
                const active = b.dataset.feMode === mode;
                b.classList.toggle('!border-accent', active);
                b.classList.toggle('!text-accent', active);
            });
            document.querySelectorAll('[data-fe-pane]').forEach((pane) => {
                pane.classList.toggle('hidden', pane.dataset.fePane !== mode);
            });
        });
    });

    // --- Device Switcher ---------------------------------------------------
    document.querySelectorAll('[data-fe-device]').forEach((btn) => {
        btn.addEventListener('click', () => {
            device = btn.dataset.feDevice;
            document.querySelectorAll('[data-fe-device]').forEach((b) => {
                const active = b.dataset.feDevice === device;
                b.setAttribute('aria-pressed', String(active));
                b.classList.toggle('bg-accent', active);
                b.classList.toggle('text-on-accent', active);
                b.classList.toggle('text-muted', !active);
            });
            if (canvasWrapEl) {
                canvasWrapEl.classList.remove('max-w-full', 'max-w-[768px]', 'max-w-[390px]');
                canvasWrapEl.classList.add(
                    device === 'tablet' ? 'max-w-[768px]' : (device === 'mobile' ? 'max-w-[390px]' : 'max-w-full')
                );
            }
        });
    });

    // --- Palette -----------------------------------------------------------
    function renderPalette() {
        if (!paletteEl) return;
        const query = (paletteSearchEl?.value || '').trim().toLowerCase();
        let html = '';

        CATEGORIES.forEach((cat) => {
            const items = fieldTypes.filter((t) =>
                t.category === cat.key && (!query || t.label.toLowerCase().includes(query) || t.value.includes(query))
            );
            if (items.length === 0) return;

            html += `<div class="space-y-2">
                <p class="font-display text-[10px] tracking-[0.22em] text-muted uppercase">${esc(cat.label)}</p>
                <div class="grid grid-cols-1 gap-1.5">`;

            items.forEach((t) => {
                html += `<button type="button" draggable="true" data-palette-type="${esc(t.value)}"
                    class="group flex w-full cursor-grab items-center justify-between border border-line bg-surface/60 px-3 py-2 text-left text-xs text-white transition-colors hover:border-accent hover:bg-accent/10 active:cursor-grabbing">
                    <span>${esc(t.label)}</span>
                    <span class="font-mono text-[10px] text-muted group-hover:text-accent">+</span>
                </button>`;
            });

            html += `</div></div>`;
        });

        paletteEl.innerHTML = html || '<p class="text-xs text-muted">No matching field types.</p>';

        paletteEl.querySelectorAll('[data-palette-type]').forEach((el) => {
            el.addEventListener('click', () => addFieldByType(el.dataset.paletteType));
            el.addEventListener('dragstart', (e) => {
                dragState = { source: 'palette', type: el.dataset.paletteType };
                e.dataTransfer.effectAllowed = 'copy';
                e.dataTransfer.setData('text/plain', el.dataset.paletteType);
            });
            el.addEventListener('dragend', () => { dragState = null; });
        });
    }

    if (paletteSearchEl) {
        paletteSearchEl.addEventListener('input', renderPalette);
    }

    function createFieldTemplate(type) {
        const meta = typeMap[type] || { label: 'Field', hasOptions: false };
        const label = meta.label;
        const existingNames = new Set(fields.map((f) => f.name));
        const base = slugifyName(label);
        let name = base;
        let n = 2;
        while (existingNames.has(name)) {
            name = `${base}_${n++}`;
        }

        return normalizeField({
            _uid: uid(),
            label: type === 'gdpr'
                ? 'I agree to the Privacy Policy and consent to my data being processed.'
                : (type === 'terms' ? 'I accept the Terms & Conditions.' : label),
            name,
            type,
            options: meta.hasOptions ? ['Option 1', 'Option 2', 'Option 3'] : [],
            is_required: type === 'email' || type === 'gdpr' || type === 'terms',
            width: 'full',
        });
    }

    function addFieldByType(type, insertIndex = null) {
        const before = snapshot();
        const field = createFieldTemplate(type);
        if (insertIndex !== null && insertIndex >= 0 && insertIndex <= fields.length) {
            fields.splice(insertIndex, 0, field);
        } else {
            fields.push(field);
        }
        selectedUid = field._uid;
        commitHistory(before);
        renderAll();
    }

    // --- Canvas ------------------------------------------------------------
    function renderFieldPreview(field) {
        const t = field.type;
        if (t === 'hidden') {
            return `<div class="border border-dashed border-line bg-ink/50 px-3 py-2 font-mono text-[11px] text-muted">
                Hidden input: <span class="text-white">${esc(field.name)}</span> = <span class="text-accent">${esc(field.default_value || '(empty)')}</span>
            </div>`;
        }
        if (t === 'section_break') {
            return `<div class="border-b border-line pb-2">
                <p class="font-display text-xs tracking-[0.2em] text-white uppercase">${esc(field.label)}</p>
                ${field.help_text ? `<p class="mt-1 text-xs text-muted">${esc(field.help_text)}</p>` : ''}
            </div>`;
        }
        if (t === 'custom_html') {
            return `<div class="border border-line/70 bg-ink/50 p-3 text-xs text-body">
                ${field.help_text ? esc(field.help_text) : '<span class="text-muted">Custom HTML block</span>'}
            </div>`;
        }
        if (t === 'gdpr' || t === 'terms') {
            return `<div class="flex items-start gap-2.5 border border-line bg-ink/40 p-3 text-xs text-white">
                <span class="mt-0.5 inline-block h-3.5 w-3.5 shrink-0 border border-muted"></span>
                <span>${esc(field.label)}${field.is_required ? ' <span class="text-accent">*</span>' : ''}</span>
            </div>`;
        }
        if (t === 'textarea' || t === 'large_text_editor') {
            return `<div class="h-16 border border-line bg-ink/60 px-3 py-2 text-xs text-muted">${esc(field.placeholder || (t === 'large_text_editor' ? 'Markdown / Specification editor...' : 'Long text answer...'))}</div>`;
        }
        if (t === 'select') {
            return `<div class="flex items-center justify-between border border-line bg-ink/60 px-3 py-2 text-xs text-muted">
                <span>${esc(field.placeholder || 'Select...')}</span>
                <span>&#9662;</span>
            </div>`;
        }
        if (t === 'radio' || t === 'checkbox') {
            const opts = (field.options || []).slice(0, 4);
            return `<div class="grid gap-1.5 sm:grid-cols-2">
                ${opts.map((o) => `<div class="flex items-center gap-2 border border-line bg-ink/40 px-2.5 py-1.5 text-[11px] text-body">
                    <span class="inline-block h-3 w-3 shrink-0 border border-muted ${t === 'radio' ? 'rounded-full' : ''}"></span>
                    <span class="truncate">${esc(o)}</span>
                </div>`).join('')}
            </div>`;
        }
        if (t === 'file') {
            return `<div class="border border-dashed border-line bg-ink/40 px-3 py-2.5 text-xs text-muted">
                Choose file (${esc((field.file_settings?.mimes || ['pdf']).join(', '))})
            </div>`;
        }
        return `<div class="border border-line bg-ink/60 px-3 py-2 text-xs text-muted">${esc(field.placeholder || `Enter ${field.label.toLowerCase()}...`)}</div>`;
    }

    function renderCanvas() {
        if (!canvasEl) return;
        if (countEl) countEl.textContent = `${fields.length} ${fields.length === 1 ? 'field' : 'fields'}`;

        if (fields.length === 0) {
            canvasEl.innerHTML = `<div class="flex flex-col items-center justify-center border border-dashed border-line p-12 text-center">
                <p class="font-display text-sm tracking-[0.2em] text-white uppercase">Empty Form Canvas</p>
                <p class="mt-2 max-w-md text-xs text-muted">Drag fields from the left palette, click any field type to add it, or start from a pre-built template.</p>
                <button type="button" data-open-templates class="tag mt-5 border-accent text-accent hover:bg-accent hover:text-on-accent">
                    Browse Templates
                </button>
            </div>`;
            canvasEl.querySelector('[data-open-templates]')?.addEventListener('click', () => {
                document.querySelector('[data-fe-mode="templates"]')?.click();
            });
            return;
        }

        let html = '<div class="grid gap-4 sm:grid-cols-6">';
        let lastGroup = null;

        fields.forEach((field, idx) => {
            const group = (field.group || '').trim();
            if (group !== '' && group !== lastGroup) {
                html += `<div class="sm:col-span-6 mt-2 border-b border-line pb-2">
                    <span class="font-display text-[11px] tracking-[0.22em] text-accent uppercase">${esc(group)}</span>
                </div>`;
            }
            lastGroup = group;

            const isSelected = field._uid === selectedUid;
            const colSpan = field.width === 'half' ? 'sm:col-span-3' : (field.width === 'third' ? 'sm:col-span-2' : 'sm:col-span-6');
            const isLayout = typeMap[field.type]?.isLayoutOnly || field.type === 'gdpr' || field.type === 'terms';
            const hasLogic = (field.condition || []).length > 0;

            html += `<div draggable="true" data-field-uid="${esc(field._uid)}" data-field-index="${idx}"
                class="${colSpan} group relative cursor-pointer border p-4 transition-colors ${isSelected ? 'border-accent bg-accent/5' : 'border-line bg-surface/50 hover:border-white/30'}">
                <div class="mb-2.5 flex items-center justify-between gap-2">
                    <div class="flex min-w-0 items-center gap-2">
                        <span class="cursor-grab text-xs text-muted hover:text-white" title="Drag to reorder">&#8942;&#8942;</span>
                        <span class="truncate text-xs font-medium text-white">
                            ${!isLayout ? `${esc(field.label)}${field.is_required ? ' <span class="text-accent">*</span>' : ''}` : esc(typeMap[field.type]?.label || field.type)}
                        </span>
                        ${hasLogic ? '<span class="border border-accent/50 px-1.5 py-0.5 font-mono text-[9px] text-accent uppercase">Logic</span>' : ''}
                    </div>
                    <div class="flex shrink-0 items-center gap-1 opacity-80 group-hover:opacity-100">
                        <button type="button" data-action="width" title="Cycle width (Full / 1/2 / 1/3)"
                            class="border border-line px-1.5 py-0.5 font-mono text-[10px] text-muted hover:border-accent hover:text-white">
                            ${field.width === 'half' ? '1/2' : (field.width === 'third' ? '1/3' : '1/1')}
                        </button>
                        <button type="button" data-action="up" title="Move up" ${idx === 0 ? 'disabled' : ''}
                            class="border border-line px-1.5 py-0.5 text-[10px] text-muted hover:border-accent hover:text-white disabled:opacity-30">&uarr;</button>
                        <button type="button" data-action="down" title="Move down" ${idx === fields.length - 1 ? 'disabled' : ''}
                            class="border border-line px-1.5 py-0.5 text-[10px] text-muted hover:border-accent hover:text-white disabled:opacity-30">&darr;</button>
                        <button type="button" data-action="duplicate" title="Duplicate field"
                            class="border border-line px-1.5 py-0.5 text-[10px] text-muted hover:border-accent hover:text-white">&#10064;</button>
                        <button type="button" data-action="delete" title="Delete field"
                            class="border border-line px-1.5 py-0.5 text-[10px] text-muted hover:border-accent hover:text-accent">&times;</button>
                    </div>
                </div>
                ${renderFieldPreview(field)}
                ${!isLayout && field.help_text ? `<p class="mt-1.5 text-[11px] text-muted">${esc(field.help_text)}</p>` : ''}
            </div>`;
        });

        html += '</div>';
        canvasEl.innerHTML = html;

        canvasEl.querySelectorAll('[data-field-uid]').forEach((card) => {
            const cardUid = card.dataset.fieldUid;
            const idx = Number(card.dataset.fieldIndex);

            card.addEventListener('click', (e) => {
                if (e.target.closest('[data-action]')) return;
                selectedUid = cardUid;
                renderCanvas();
                renderInspector();
            });

            card.querySelectorAll('[data-action]').forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    handleFieldAction(cardUid, btn.dataset.action);
                });
            });

            card.addEventListener('dragstart', (e) => {
                dragState = { source: 'canvas', index: idx };
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', cardUid);
                card.classList.add('opacity-50');
            });

            card.addEventListener('dragend', () => {
                dragState = null;
                card.classList.remove('opacity-50');
            });

            card.addEventListener('dragover', (e) => {
                e.preventDefault();
                card.classList.add('ring-1', 'ring-accent');
            });

            card.addEventListener('dragleave', () => {
                card.classList.remove('ring-1', 'ring-accent');
            });

            card.addEventListener('drop', (e) => {
                e.preventDefault();
                e.stopPropagation();
                card.classList.remove('ring-1', 'ring-accent');
                if (!dragState) return;
                if (dragState.source === 'palette') {
                    addFieldByType(dragState.type, idx);
                } else if (dragState.source === 'canvas' && dragState.index !== idx) {
                    const before = snapshot();
                    const [moved] = fields.splice(dragState.index, 1);
                    fields.splice(idx, 0, moved);
                    selectedUid = moved._uid;
                    commitHistory(before);
                    renderAll();
                }
                dragState = null;
            });
        });
    }

    if (canvasEl) {
        canvasEl.addEventListener('dragover', (e) => { e.preventDefault(); });
        canvasEl.addEventListener('drop', (e) => {
            e.preventDefault();
            if (dragState?.source === 'palette') {
                addFieldByType(dragState.type);
                dragState = null;
            }
        });
    }

    function handleFieldAction(fieldUid, action) {
        const idx = fields.findIndex((f) => f._uid === fieldUid);
        if (idx === -1) return;
        const before = snapshot();
        const field = fields[idx];

        if (action === 'width') {
            const order = ['full', 'half', 'third'];
            field.width = order[(order.indexOf(field.width) + 1) % order.length];
        } else if (action === 'up' && idx > 0) {
            [fields[idx - 1], fields[idx]] = [fields[idx], fields[idx - 1]];
        } else if (action === 'down' && idx < fields.length - 1) {
            [fields[idx + 1], fields[idx]] = [fields[idx], fields[idx + 1]];
        } else if (action === 'duplicate') {
            const copy = normalizeField({ ...field, id: null, _uid: uid(), name: slugifyName(field.name + '_copy') });
            fields.splice(idx + 1, 0, copy);
            selectedUid = copy._uid;
        } else if (action === 'delete') {
            fields.splice(idx, 1);
            if (selectedUid === fieldUid) {
                selectedUid = fields[Math.min(idx, fields.length - 1)]?._uid || null;
            }
        } else {
            return;
        }

        commitHistory(before);
        renderAll();
    }

    // --- Inspector ---------------------------------------------------------
    function renderInspector() {
        if (!inspectorEl) return;
        const field = fields.find((f) => f._uid === selectedUid);
        if (!field) {
            inspectorEl.innerHTML = '<p class="text-sm text-muted">Select a field on the canvas to configure its label, validation, width, or conditional logic.</p>';
            return;
        }

        const meta = typeMap[field.type] || {};
        const otherFields = fields.filter((f) => f._uid !== field._uid && !typeMap[f.type]?.isLayoutOnly);

        let html = `<div class="mb-4 flex border-b border-line">
            ${['general', 'logic', 'advanced'].map((tab) => {
                const label = tab === 'general' ? 'General' : (tab === 'logic' ? 'Logic & Rules' : 'Advanced');
                const active = inspectorTab === tab;
                return `<button type="button" data-inspector-tab="${tab}"
                    class="flex-1 border-b-2 pb-2.5 font-display text-[11px] tracking-[0.16em] uppercase transition-colors ${active ? 'border-accent text-white' : 'border-transparent text-muted hover:text-white'}">${label}</button>`;
            }).join('')}
        </div>`;

        if (inspectorTab === 'general') {
            html += `<div class="space-y-4">
                <div class="field">
                    <label class="field__label">Label *</label>
                    <input class="input text-sm" type="text" data-bind="label" value="${esc(field.label)}">
                </div>
                <div class="field">
                    <label class="field__label">Type</label>
                    <select class="input text-sm" data-bind="type">
                        ${fieldTypes.map((t) => `<option value="${esc(t.value)}" ${field.type === t.value ? 'selected' : ''}>${esc(t.label)}</option>`).join('')}
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="field">
                        <label class="field__label">Section</label>
                        <input class="input text-sm" type="text" data-bind="group" value="${esc(field.group)}" placeholder="e.g. Contact">
                    </div>
                    <div class="field">
                        <label class="field__label">Width</label>
                        <select class="input text-sm" data-bind="width">
                            <option value="full" ${field.width === 'full' ? 'selected' : ''}>Full (1/1)</option>
                            <option value="half" ${field.width === 'half' ? 'selected' : ''}>Half (1/2)</option>
                            <option value="third" ${field.width === 'third' ? 'selected' : ''}>Third (1/3)</option>
                        </select>
                    </div>
                </div>
                ${!meta.isLayoutOnly && !meta.isConsent && field.type !== 'hidden' ? `
                    <div class="field">
                        <label class="field__label">Placeholder</label>
                        <input class="input text-sm" type="text" data-bind="placeholder" value="${esc(field.placeholder)}">
                    </div>
                ` : ''}
                <div class="field">
                    <label class="field__label">${field.type === 'custom_html' ? 'HTML Content' : 'Help Text'}</label>
                    <textarea class="input text-sm" rows="${field.type === 'custom_html' ? 4 : 2}" data-bind="help_text">${esc(field.help_text)}</textarea>
                </div>
                ${meta.hasOptions ? `
                    <div class="field">
                        <label class="field__label">Options (one per line)</label>
                        <textarea class="input font-mono text-xs" rows="4" data-bind-options>${esc((field.options || []).join('\n'))}</textarea>
                    </div>
                    <label class="flex cursor-pointer items-center gap-2.5 border border-line p-2.5 text-xs text-white hover:border-accent">
                        <input type="checkbox" class="h-4 w-4 accent-accent" data-bind-bool="has_text_input" ${field.has_text_input ? 'checked' : ''}>
                        <span>Allow additional text note</span>
                    </label>
                ` : ''}
                ${!meta.isLayoutOnly && field.type !== 'hidden' ? `
                    <label class="flex cursor-pointer items-center gap-2.5 border border-line p-2.5 text-xs text-white hover:border-accent">
                        <input type="checkbox" class="h-4 w-4 accent-accent" data-bind-bool="is_required" ${field.is_required ? 'checked' : ''}>
                        <span>Required field</span>
                    </label>
                ` : ''}
            </div>`;
        } else if (inspectorTab === 'logic') {
            const rules = field.condition || [];
            html += `<div class="space-y-5">
                ${!meta.isLayoutOnly ? `
                    <div class="field">
                        <label class="field__label">Extra Validation Rules</label>
                        <input class="input font-mono text-xs" type="text" data-bind-validation value="${esc((field.validation || []).join(', '))}" placeholder="min:3, max:120">
                        <p class="text-[11px] text-muted">Laravel rules, comma separated.</p>
                    </div>
                ` : ''}
                ${meta.isFile ? `
                    <div class="grid grid-cols-2 gap-3">
                        <div class="field">
                            <label class="field__label">Accepted Mimes</label>
                            <input class="input font-mono text-xs" type="text" data-bind-mimes value="${esc((field.file_settings?.mimes || []).join(', '))}" placeholder="pdf, doc">
                        </div>
                        <div class="field">
                            <label class="field__label">Max Size (KB)</label>
                            <input class="input font-mono text-xs" type="number" min="1" max="204800" data-bind-maxkb value="${esc(field.file_settings?.max_kilobytes ?? 5120)}">
                        </div>
                    </div>
                ` : ''}
                <div class="space-y-3 border-t border-line pt-4">
                    <div class="flex items-center justify-between">
                        <span class="field__label !mb-0">Conditional Logic</span>
                        <select class="border border-line bg-surface px-2 py-1 text-[11px] text-white" data-bind="condition_match">
                            <option value="all" ${field.condition_match === 'all' ? 'selected' : ''}>Match ALL</option>
                            <option value="any" ${field.condition_match === 'any' ? 'selected' : ''}>Match ANY</option>
                        </select>
                    </div>
                    <div class="space-y-2.5" id="fe-condition-rows">
                        ${rules.map((r, rIdx) => `
                            <div class="space-y-2 border border-line bg-surface/50 p-2.5" data-rule-idx="${rIdx}">
                                <div class="flex items-center justify-between gap-2">
                                    <select class="input text-xs" data-rule-prop="field">
                                        <option value="">Select field...</option>
                                        ${otherFields.map((of) => `<option value="${esc(of.name)}" ${r.field === of.name ? 'selected' : ''}>${esc(of.label)}</option>`).join('')}
                                    </select>
                                    <button type="button" data-remove-rule="${rIdx}" class="px-1.5 text-xs text-muted hover:text-accent">&times;</button>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <select class="input text-xs" data-rule-prop="operator">
                                        ${OPERATORS.map((op) => `<option value="${op.value}" ${r.operator === op.value ? 'selected' : ''}>${op.label}</option>`).join('')}
                                    </select>
                                    <input class="input text-xs" type="text" data-rule-prop="value" value="${esc(r.value || '')}" placeholder="Value">
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    <button type="button" id="fe-add-rule" class="tag w-full justify-center hover:border-accent hover:text-accent">
                        + Add Condition Rule
                    </button>
                </div>
            </div>`;
        } else {
            html += `<div class="space-y-4">
                <div class="field">
                    <label class="field__label">Machine Key</label>
                    <input class="input font-mono text-xs" type="text" data-bind="name" value="${esc(field.name)}">
                    <p class="text-[11px] text-muted">Lowercase letters, digits, underscores.</p>
                </div>
                <div class="field">
                    <label class="field__label">Default Value</label>
                    <input class="input text-sm" type="text" data-bind="default_value" value="${esc(field.default_value)}" placeholder="Static value or {url:param}">
                    <p class="text-[11px] text-muted">Use <code class="text-accent">{url:utm_source}</code> to pre-fill from query params.</p>
                </div>
                <div class="field">
                    <label class="field__label">Custom CSS Class</label>
                    <input class="input font-mono text-xs" type="text" data-bind="css_class" value="${esc(field.css_class)}">
                </div>
                <div class="field">
                    <label class="field__label">Wizard Section ID</label>
                    <input class="input font-mono text-xs" type="text" data-bind="section_id" value="${esc(field.section_id)}">
                </div>
            </div>`;
        }

        inspectorEl.innerHTML = html;

        inspectorEl.querySelectorAll('[data-inspector-tab]').forEach((btn) => {
            btn.addEventListener('click', () => {
                inspectorTab = btn.dataset.inspectorTab;
                renderInspector();
            });
        });

        inspectorEl.querySelectorAll('[data-bind]').forEach((input) => {
            input.addEventListener('input', () => {
                const before = snapshot();
                const prop = input.dataset.bind;
                field[prop] = input.value;
                if (prop === 'type') {
                    if (typeMap[field.type]?.hasOptions && (!field.options || field.options.length === 0)) {
                        field.options = ['Option 1', 'Option 2'];
                    }
                    renderInspector();
                }
                commitHistory(before);
                renderCanvas();
            });
        });

        inspectorEl.querySelectorAll('[data-bind-bool]').forEach((input) => {
            input.addEventListener('change', () => {
                const before = snapshot();
                field[input.dataset.bindBool] = input.checked;
                commitHistory(before);
                renderCanvas();
            });
        });

        const optsEl = inspectorEl.querySelector('[data-bind-options]');
        if (optsEl) {
            optsEl.addEventListener('input', () => {
                const before = snapshot();
                field.options = optsEl.value.split(/\r?\n/).map((s) => s.trim()).filter(Boolean);
                commitHistory(before);
                renderCanvas();
            });
        }

        const valEl = inspectorEl.querySelector('[data-bind-validation]');
        if (valEl) {
            valEl.addEventListener('input', () => {
                const before = snapshot();
                field.validation = valEl.value.split(',').map((s) => s.trim()).filter(Boolean);
                commitHistory(before);
            });
        }

        const mimesEl = inspectorEl.querySelector('[data-bind-mimes]');
        if (mimesEl) {
            mimesEl.addEventListener('input', () => {
                const before = snapshot();
                field.file_settings = field.file_settings || {};
                field.file_settings.mimes = mimesEl.value.split(',').map((s) => s.trim()).filter(Boolean);
                commitHistory(before);
                renderCanvas();
            });
        }

        const maxKbEl = inspectorEl.querySelector('[data-bind-maxkb]');
        if (maxKbEl) {
            maxKbEl.addEventListener('input', () => {
                const before = snapshot();
                field.file_settings = field.file_settings || {};
                field.file_settings.max_kilobytes = Number(maxKbEl.value) || 5120;
                commitHistory(before);
            });
        }

        inspectorEl.querySelector('#fe-add-rule')?.addEventListener('click', () => {
            const before = snapshot();
            field.condition = field.condition || [];
            field.condition.push({ field: otherFields[0]?.name || '', operator: 'equals', value: '' });
            commitHistory(before);
            renderAll();
        });

        inspectorEl.querySelectorAll('[data-remove-rule]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const before = snapshot();
                field.condition.splice(Number(btn.dataset.removeRule), 1);
                commitHistory(before);
                renderAll();
            });
        });

        inspectorEl.querySelectorAll('[data-rule-idx]').forEach((row) => {
            const rIdx = Number(row.dataset.ruleIdx);
            row.querySelectorAll('[data-rule-prop]').forEach((el) => {
                el.addEventListener('input', () => {
                    const before = snapshot();
                    field.condition[rIdx][el.dataset.ruleProp] = el.value;
                    commitHistory(before);
                });
            });
        });
    }

    // --- Templates ---------------------------------------------------------
    document.querySelectorAll('[data-apply-template]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const tpl = templates.find((t) => t.id === btn.dataset.applyTemplate);
            if (!tpl) return;
            if (fields.length > 0 && !confirm(`Replace current ${fields.length} fields with the "${tpl.name}" template? (You can Undo this.)`)) {
                return;
            }
            const before = snapshot();
            fields = tpl.fields.map((f) => normalizeField({ ...f, _uid: uid() }));
            selectedUid = fields[0]?._uid || null;
            commitHistory(before);
            renderAll();
            document.querySelector('[data-fe-mode="builder"]')?.click();
            toast(`Applied "${tpl.name}" template. Click Save to persist.`);
        });
    });

    // --- Copy Snippets -----------------------------------------------------
    document.querySelectorAll('[data-copy-target]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const target = document.getElementById(btn.dataset.copyTarget);
            if (!target) return;
            try {
                await navigator.clipboard.writeText(target.value || target.textContent);
                toast('Copied to clipboard.');
            } catch (_) {
                toast('Copy failed — select and copy manually.', true);
            }
        });
    });

    // --- Save --------------------------------------------------------------
    async function saveBuilder() {
        if (saving || !urls.sync) return;
        saving = true;
        if (saveBtn) saveBtn.disabled = true;
        if (statusEl) statusEl.textContent = 'Saving...';

        try {
            const res = await fetch(urls.sync, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': urls.csrf,
                },
                body: JSON.stringify({
                    fields: fields.map((f) => ({
                        id: f.id,
                        label: f.label,
                        name: f.name,
                        type: f.type,
                        group: f.group || null,
                        section_id: f.section_id || null,
                        options: f.options,
                        has_text_input: f.has_text_input,
                        text_placeholder: f.text_placeholder || null,
                        placeholder: f.placeholder || null,
                        help_text: f.help_text || null,
                        default_value: f.default_value || null,
                        width: f.width,
                        css_class: f.css_class || null,
                        validation: f.validation,
                        condition: f.condition,
                        condition_match: f.condition_match,
                        file_settings: f.file_settings,
                        is_required: f.is_required,
                    })),
                }),
            });

            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                throw new Error(err.message || 'Could not save form fields.');
            }

            const data = await res.json();
            const prevIndex = fields.findIndex((f) => f._uid === selectedUid);
            fields = (data.fields || []).map(normalizeField);
            selectedUid = fields[Math.max(0, prevIndex)]?._uid || fields[0]?._uid || null;
            markDirty(false);
            renderAll();
            toast('Form fields saved.');
        } catch (err) {
            toast(err.message || 'Save failed.', true);
            if (statusEl) statusEl.textContent = 'Save failed';
        } finally {
            saving = false;
            if (saveBtn) saveBtn.disabled = false;
        }
    }

    if (saveBtn) saveBtn.addEventListener('click', saveBuilder);
    if (undoBtn) undoBtn.addEventListener('click', undo);
    if (redoBtn) redoBtn.addEventListener('click', redo);

    // Keyboard shortcuts: Ctrl+S, Ctrl+Z, Ctrl+Shift+Z / Ctrl+Y
    document.addEventListener('keydown', (e) => {
        const mod = e.ctrlKey || e.metaKey;
        if (!mod) return;
        if (e.key.toLowerCase() === 's') {
            e.preventDefault();
            saveBuilder();
        } else if (e.key.toLowerCase() === 'z' && !e.shiftKey && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) {
            e.preventDefault();
            undo();
        } else if ((e.key.toLowerCase() === 'y' || (e.key.toLowerCase() === 'z' && e.shiftKey)) && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) {
            e.preventDefault();
            redo();
        }
    });

    function renderAll() {
        renderPalette();
        renderCanvas();
        renderInspector();
    }

    updateHistoryButtons();
    markDirty(false);
    renderAll();
})();
