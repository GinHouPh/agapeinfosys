/**
 * Multi-step SRS & Form Wizard behaviour.
 *
 * Powers any `[data-form-wizard]` form: step navigation, localStorage draft
 * auto-save/resume, 2-level SRSFormData JSON export/import, conditional
 * "Additional Details" textareas, LargeTextEditor (Write/Preview + .md/.txt
 * drag-and-drop), dynamic architectural banners, and Pre-Flight Review Summary.
 */

const escapeHtml = (str) =>
    String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

const renderSimpleMarkdown = (md) => {
    if (!md || !md.trim()) return '';
    const lines = md.replace(/\r\n/g, '\n').split('\n');
    let html = '';
    let inList = false;
    let inCode = false;

    const closeList = () => {
        if (inList) {
            html += '</ul>';
            inList = false;
        }
    };

    const inlineFormat = (text) => {
        let out = escapeHtml(text);
        out = out.replace(/`([^`]+)`/g, '<code class="bg-box px-1.5 py-0.5 font-mono text-xs text-accent">$1</code>');
        out = out.replace(/\*\*([^*]+)\*\*/g, '<strong class="text-white font-semibold">$1</strong>');
        out = out.replace(/\*([^*]+)\*/g, '<em>$1</em>');
        return out;
    };

    for (const line of lines) {
        if (line.trim().startsWith('```')) {
            closeList();
            if (!inCode) {
                html += '<pre class="my-3 overflow-x-auto border border-line bg-ink p-3 font-mono text-xs text-body"><code>';
                inCode = true;
            } else {
                html += '</code></pre>';
                inCode = false;
            }
            continue;
        }

        if (inCode) {
            html += escapeHtml(line) + '\n';
            continue;
        }

        if (/^###\s+/.test(line)) {
            closeList();
            html += `<h4 class="mt-4 mb-2 font-display text-sm font-semibold uppercase tracking-wider text-white">${inlineFormat(line.replace(/^###\s+/, ''))}</h4>`;
        } else if (/^##\s+/.test(line)) {
            closeList();
            html += `<h3 class="mt-5 mb-2 font-display text-base font-semibold uppercase tracking-wider text-white border-b border-line pb-1">${inlineFormat(line.replace(/^##\s+/, ''))}</h3>`;
        } else if (/^#\s+/.test(line)) {
            closeList();
            html += `<h2 class="mt-5 mb-3 font-display text-lg font-semibold uppercase tracking-wider text-accent">${inlineFormat(line.replace(/^#\s+/, ''))}</h2>`;
        } else if (/^\s*[-*+]\s+/.test(line)) {
            if (!inList) {
                html += '<ul class="my-2 list-disc space-y-1 pl-5 text-xs text-body">';
                inList = true;
            }
            html += `<li>${inlineFormat(line.replace(/^\s*[-*+]\s+/, ''))}</li>`;
        } else if (line.trim() === '') {
            closeList();
        } else {
            closeList();
            html += `<p class="my-2 text-xs leading-relaxed text-body">${inlineFormat(line)}</p>`;
        }
    }

    closeList();
    if (inCode) html += '</code></pre>';
    return html;
};

const initLargeTextEditors = (root, onContentChanged) => {
    root.querySelectorAll('[data-large-editor]').forEach((editor) => {
        const textarea = editor.querySelector('[data-editor-textarea]');
        const preview = editor.querySelector('[data-editor-preview]');
        const writeBtn = editor.querySelector('[data-editor-tab="write"]');
        const previewBtn = editor.querySelector('[data-editor-tab="preview"]');
        const writePane = editor.querySelector('[data-editor-pane="write"]');
        const previewPane = editor.querySelector('[data-editor-pane="preview"]');
        const attachBtn = editor.querySelector('[data-editor-attach]');
        const fileInput = editor.querySelector('[data-editor-file-input]');
        const fileNameInput = editor.querySelector('[data-editor-file-name-input]');
        const fileChip = editor.querySelector('[data-editor-file-chip]');
        const fileChipLabel = editor.querySelector('[data-editor-file-chip-label]');
        const clearFileBtn = editor.querySelector('[data-editor-clear-file]');
        const clearTextBtn = editor.querySelector('[data-editor-clear-text]');
        const statsEl = editor.querySelector('[data-editor-stats]');
        const errorBanner = editor.querySelector('[data-editor-error]');
        const errorText = editor.querySelector('[data-editor-error-text]');
        const errorClose = editor.querySelector('[data-editor-error-close]');
        const dropOverlay = editor.querySelector('[data-editor-drop-overlay]');

        if (!textarea) return;

        const updateStats = () => {
            const val = textarea.value || '';
            const words = val.trim() ? val.trim().split(/\s+/).filter(Boolean).length : 0;
            const chars = val.length;
            if (statsEl) {
                statsEl.textContent = `${words} ${words === 1 ? 'word' : 'words'} · ${chars} chars`;
            }
            if (clearTextBtn) {
                clearTextBtn.classList.toggle('hidden', !val);
            }
        };

        const setFileName = (name) => {
            if (fileNameInput) fileNameInput.value = name || '';
            if (fileChip && fileChipLabel) {
                fileChipLabel.textContent = name || '';
                fileChip.classList.toggle('hidden', !name);
                fileChip.classList.toggle('inline-flex', Boolean(name));
            }
        };

        const showError = (msg) => {
            if (errorBanner && errorText) {
                errorText.textContent = msg;
                errorBanner.classList.remove('hidden');
            }
        };

        const switchTab = (tab) => {
            const isPreview = tab === 'preview';
            if (writePane) writePane.classList.toggle('hidden', isPreview);
            if (previewPane) previewPane.classList.toggle('hidden', !isPreview);

            if (writeBtn) {
                writeBtn.classList.toggle('bg-accent', !isPreview);
                writeBtn.classList.toggle('text-on-accent', !isPreview);
                writeBtn.classList.toggle('text-muted', isPreview);
            }
            if (previewBtn) {
                previewBtn.classList.toggle('bg-accent', isPreview);
                previewBtn.classList.toggle('text-on-accent', isPreview);
                previewBtn.classList.toggle('text-muted', !isPreview);
            }

            if (isPreview && preview) {
                const val = textarea.value || '';
                preview.innerHTML = val.trim()
                    ? renderSimpleMarkdown(val)
                    : '<div class="py-10 text-center text-xs text-muted">No content yet. Switch to Write or attach a .md / .txt file.</div>';
            }
        };

        const processFile = (file) => {
            if (errorBanner) errorBanner.classList.add('hidden');
            const name = file.name || '';
            const lower = name.toLowerCase();
            if (!['.md', '.markdown', '.txt'].some((ext) => lower.endsWith(ext))) {
                showError('Unsupported file type. Only Markdown (.md) and plain text (.txt) files are allowed.');
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                showError(`File "${name}" exceeds the 5 MB limit.`);
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                textarea.value = String(e.target?.result || '');
                setFileName(name);
                updateStats();
                onContentChanged();
            };
            reader.readAsText(file);
        };

        writeBtn?.addEventListener('click', () => switchTab('write'));
        previewBtn?.addEventListener('click', () => switchTab('preview'));
        attachBtn?.addEventListener('click', () => fileInput?.click());
        fileInput?.addEventListener('change', (e) => {
            const file = e.target.files?.[0];
            if (file) processFile(file);
            e.target.value = '';
        });
        clearFileBtn?.addEventListener('click', () => {
            setFileName('');
            onContentChanged();
        });
        clearTextBtn?.addEventListener('click', () => {
            if (confirm('Clear the specification text?')) {
                textarea.value = '';
                setFileName('');
                updateStats();
                onContentChanged();
            }
        });
        errorClose?.addEventListener('click', () => errorBanner?.classList.add('hidden'));

        editor.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropOverlay?.classList.remove('hidden');
        });
        editor.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropOverlay?.classList.add('hidden');
        });
        editor.addEventListener('drop', (e) => {
            e.preventDefault();
            dropOverlay?.classList.add('hidden');
            if (e.dataTransfer?.files?.length) {
                processFile(e.dataTransfer.files[0]);
            }
        });

        textarea.addEventListener('input', () => {
            updateStats();
            onContentChanged();
        });

        editor._syncUi = () => {
            updateStats();
            setFileName(fileNameInput?.value || '');
        };

        updateStats();
    });
};

export const initFormWizards = () => {
    document.querySelectorAll('[data-form-wizard]').forEach((wizard) => {
        const formSlug = wizard.dataset.formSlug || 'form';
        const storageKey = `agape_wizard_draft_${formSlug}`;
        const form = wizard.querySelector('form[data-wizard-form]');
        const steps = Array.from(wizard.querySelectorAll('[data-wizard-step]'));
        const ribbonBtns = Array.from(wizard.querySelectorAll('[data-wizard-ribbon-btn]'));
        const jumpSelect = wizard.querySelector('[data-wizard-jump]');
        const prevBtn = wizard.querySelector('[data-wizard-prev]');
        const nextBtn = wizard.querySelector('[data-wizard-next]');
        const reviewOpenBtn = wizard.querySelector('[data-wizard-review-open]');
        const reviewCloseBtns = Array.from(wizard.querySelectorAll('[data-wizard-review-close]'));
        const formView = wizard.querySelector('[data-wizard-form-view]');
        const reviewView = wizard.querySelector('[data-wizard-review-view]');
        const reviewBody = wizard.querySelector('[data-wizard-review-body]');
        const reviewStatsBar = wizard.querySelector('[data-wizard-review-bar]');
        const reviewStatsPct = wizard.querySelector('[data-wizard-review-pct]');
        const reviewStatsCount = wizard.querySelector('[data-wizard-review-count]');
        const stepLabelEl = wizard.querySelector('[data-wizard-step-label]');
        const stepPctEl = wizard.querySelector('[data-wizard-step-pct]');
        const progressBar = wizard.querySelector('[data-wizard-progress-bar]');
        const toastEl = wizard.querySelector('[data-wizard-toast]');
        const toastMsgEl = wizard.querySelector('[data-wizard-toast-msg]');
        const draftBanner = wizard.querySelector('[data-wizard-draft-banner]');
        const draftMetaEl = wizard.querySelector('[data-wizard-draft-meta]');
        const resumeBtn = wizard.querySelector('[data-wizard-draft-resume]');
        const dismissBtn = wizard.querySelector('[data-wizard-draft-dismiss]');
        const saveDraftBtns = Array.from(wizard.querySelectorAll('[data-wizard-save-draft]'));
        const exportBtns = Array.from(wizard.querySelectorAll('[data-wizard-export-json]'));
        const importBtn = wizard.querySelector('[data-wizard-import-json]');
        const importInput = wizard.querySelector('[data-wizard-import-input]');
        const clearBtn = wizard.querySelector('[data-wizard-clear-draft]');
        const lastSavedEl = wizard.querySelector('[data-wizard-last-saved]');

        if (!form || steps.length === 0) return;

        let activeStep = 0;
        let viewMode = 'form';
        let toastTimer = null;
        let autoSaveTimer = null;
        let pendingDraft = null;

        const showToast = (msg) => {
            if (!toastEl || !toastMsgEl) return;
            toastMsgEl.textContent = msg;
            toastEl.classList.remove('hidden');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => toastEl.classList.add('hidden'), 3500);
        };

        const getFieldValue = (fieldEl) => {
            const name = fieldEl.dataset.field;
            if (!name) return '';
            const checkboxes = fieldEl.querySelectorAll(`input[type="checkbox"][name="answers[${name}][]"]`);
            if (checkboxes.length > 0) {
                return Array.from(checkboxes).filter((c) => c.checked).map((c) => c.value);
            }
            const radios = fieldEl.querySelectorAll(`input[type="radio"][name="answers[${name}]"]`);
            if (radios.length > 0) {
                const checked = Array.from(radios).find((r) => r.checked);
                return checked ? checked.value : '';
            }
            const input = fieldEl.querySelector(`[name="answers[${name}]"]`);
            return input ? input.value : '';
        };

        const isFieldFilled = (val) => (Array.isArray(val) ? val.length > 0 : String(val || '').trim().length > 0);

        const collectSrsFormData = () => {
            const data = {};
            steps.forEach((stepEl) => {
                const sectionId = stepEl.dataset.sectionId || `section_${stepEl.dataset.wizardStep}`;
                const sectionData = {};
                stepEl.querySelectorAll('[data-field]').forEach((fieldEl) => {
                    const name = fieldEl.dataset.field;
                    if (!name) return;
                    const val = getFieldValue(fieldEl);
                    if (isFieldFilled(val)) {
                        sectionData[name] = val;
                    }
                    const textInput = fieldEl.querySelector(`[name="answers[${name}_text]"]`);
                    if (textInput && textInput.value.trim() !== '') {
                        sectionData[`${name}_text`] = textInput.value;
                    }
                    const fileNameInput = fieldEl.querySelector(`[name="answers[${name}_file_name]"]`);
                    if (fileNameInput && fileNameInput.value.trim() !== '') {
                        sectionData[`${name}_file_name`] = fileNameInput.value;
                    }
                });
                if (Object.keys(sectionData).length > 0) {
                    data[sectionId] = sectionData;
                }
            });
            return data;
        };

        const applySrsFormData = (rawData) => {
            if (!rawData || typeof rawData !== 'object') return;
            const flat = {};
            if (rawData.project_overview || Object.values(rawData).some((v) => v && typeof v === 'object' && !Array.isArray(v))) {
                Object.values(rawData).forEach((secObj) => {
                    if (secObj && typeof secObj === 'object' && !Array.isArray(secObj)) {
                        Object.assign(flat, secObj);
                    }
                });
            } else {
                Object.assign(flat, rawData);
            }

            wizard.querySelectorAll('[data-field]').forEach((fieldEl) => {
                const name = fieldEl.dataset.field;
                if (!name) return;
                const val = flat[name];

                const checkboxes = fieldEl.querySelectorAll(`input[type="checkbox"][name="answers[${name}][]"]`);
                if (checkboxes.length > 0) {
                    const arr = Array.isArray(val) ? val : [];
                    checkboxes.forEach((c) => {
                        c.checked = arr.includes(c.value);
                    });
                } else {
                    const radios = fieldEl.querySelectorAll(`input[type="radio"][name="answers[${name}]"]`);
                    if (radios.length > 0) {
                        radios.forEach((r) => {
                            r.checked = r.value === val;
                        });
                    } else {
                        const input = fieldEl.querySelector(`[name="answers[${name}]"]`);
                        if (input && input.type !== 'file') {
                            input.value = typeof val === 'string' ? val : '';
                        }
                    }
                }

                const textInput = fieldEl.querySelector(`[name="answers[${name}_text]"]`);
                if (textInput) {
                    textInput.value = typeof flat[`${name}_text`] === 'string' ? flat[`${name}_text`] : '';
                }
                const fileNameInput = fieldEl.querySelector(`[name="answers[${name}_file_name]"]`);
                if (fileNameInput) {
                    fileNameInput.value = typeof flat[`${name}_file_name`] === 'string' ? flat[`${name}_file_name`] : '';
                }
            });

            wizard.querySelectorAll('[data-large-editor]').forEach((ed) => ed._syncUi?.());
            syncChoiceTextboxes();
            syncArchitectureBanners();
            updateCounts();
        };

        const syncChoiceTextboxes = () => {
            wizard.querySelectorAll('[data-has-text-input="1"]').forEach((fieldEl) => {
                const wrapper = fieldEl.querySelector('[data-additional-details]');
                if (!wrapper) return;
                const val = getFieldValue(fieldEl);
                const show = isFieldFilled(val);
                wrapper.classList.toggle('hidden', !show);
            });
        };

        const syncArchitectureBanners = () => {
            const adminArchField = wizard.querySelector('[data-field="admin_panel_architecture"]');
            const archetypeField = wizard.querySelector('[data-field="project_archetype"]');
            const adminArch = adminArchField ? String(getFieldValue(adminArchField) || '') : '';
            const archetype = archetypeField ? String(getFieldValue(archetypeField) || '') : '';

            wizard.querySelectorAll('[data-arch-banner="no_admin"]').forEach((el) => {
                el.classList.toggle('hidden', adminArch !== 'No Admin Panel (Not needed / Zero admin surfaces)');
            });
            wizard.querySelectorAll('[data-arch-banner="multi_tenant"]').forEach((el) => {
                el.classList.toggle('hidden', !archetype.includes('Multi-Tenant'));
            });
            wizard.querySelectorAll('[data-arch-banner="marketplace"]').forEach((el) => {
                el.classList.toggle('hidden', !archetype.includes('Marketplace'));
            });
        };

        const getStats = () => {
            let total = 0;
            let completed = 0;
            const perSection = steps.map((stepEl) => {
                const fields = Array.from(stepEl.querySelectorAll('[data-field]'));
                let secCompleted = 0;
                fields.forEach((f) => {
                    total++;
                    if (isFieldFilled(getFieldValue(f))) {
                        completed++;
                        secCompleted++;
                    }
                });
                return { completed: secCompleted, total: fields.length };
            });
            return {
                total,
                completed,
                percentage: total > 0 ? Math.round((completed / total) * 100) : 0,
                perSection,
            };
        };

        const updateCounts = () => {
            const stats = getStats();

            ribbonBtns.forEach((btn, idx) => {
                const secStats = stats.perSection[idx] || { completed: 0, total: 0 };
                const dot = btn.querySelector('[data-ribbon-dot]');
                const isFilled = secStats.completed > 0;
                const isActive = idx === activeStep;
                if (dot) {
                    dot.classList.toggle('hidden', !isFilled);
                    dot.classList.toggle('bg-on-accent', isActive);
                    dot.classList.toggle('bg-accent', !isActive);
                }
            });

            if (jumpSelect) {
                Array.from(jumpSelect.options).forEach((opt, idx) => {
                    const secStats = stats.perSection[idx];
                    const base = opt.dataset.baseTitle || opt.textContent;
                    if (secStats) {
                        opt.textContent = `${base} (${secStats.completed}/${secStats.total})`;
                    }
                });
            }

            if (clearBtn) {
                clearBtn.classList.toggle('hidden', stats.completed === 0);
            }
        };

        const renderStep = () => {
            steps.forEach((stepEl, idx) => {
                stepEl.classList.toggle('hidden', idx !== activeStep);
            });

            ribbonBtns.forEach((btn, idx) => {
                const isActive = idx === activeStep;
                btn.classList.toggle('bg-accent', isActive);
                btn.classList.toggle('text-on-accent', isActive);
                btn.classList.toggle('border-accent', isActive);
                btn.classList.toggle('font-semibold', isActive);
                btn.classList.toggle('bg-surface', !isActive);
                btn.classList.toggle('text-muted', !isActive);
                btn.classList.toggle('border-line', !isActive);
            });

            if (jumpSelect) jumpSelect.value = String(activeStep);

            const pct = Math.round(((activeStep + 1) / steps.length) * 100);
            if (stepLabelEl) stepLabelEl.textContent = `Section ${activeStep + 1} of ${steps.length}`;
            if (stepPctEl) stepPctEl.textContent = `${pct}%`;
            if (progressBar) progressBar.style.width = `${pct}%`;

            if (prevBtn) {
                prevBtn.disabled = activeStep === 0;
                prevBtn.classList.toggle('opacity-40', activeStep === 0);
            }
            if (nextBtn) {
                const label = nextBtn.querySelector('[data-next-label]');
                if (label) {
                    label.textContent = activeStep === steps.length - 1 ? 'Review Summary' : 'Next Section';
                }
            }

            updateCounts();
        };

        const renderReview = () => {
            const stats = getStats();
            if (reviewStatsBar) reviewStatsBar.style.width = `${stats.percentage}%`;
            if (reviewStatsPct) reviewStatsPct.textContent = `${stats.percentage}%`;
            if (reviewStatsCount) reviewStatsCount.textContent = `${stats.completed} of ${stats.total} fields completed`;

            if (!reviewBody) return;
            reviewBody.innerHTML = '';

            let anySectionRendered = false;

            steps.forEach((stepEl, idx) => {
                const title = stepEl.dataset.sectionTitle || `Section ${idx + 1}`;
                const icon = stepEl.dataset.sectionIcon || '📋';
                const fields = Array.from(stepEl.querySelectorAll('[data-field]'));
                const filledFields = [];

                fields.forEach((fieldEl) => {
                    const name = fieldEl.dataset.field;
                    const label = fieldEl.dataset.fieldLabel || name;
                    const type = fieldEl.dataset.fieldType || 'text';
                    const val = getFieldValue(fieldEl);
                    const textInput = fieldEl.querySelector(`[name="answers[${name}_text]"]`);
                    const textVal = textInput ? textInput.value.trim() : '';
                    const fileNameInput = fieldEl.querySelector(`[name="answers[${name}_file_name]"]`);
                    const fileName = fileNameInput ? fileNameInput.value.trim() : '';

                    if (isFieldFilled(val)) {
                        filledFields.push({ label, type, val, textVal, fileName });
                    }
                });

                if (filledFields.length === 0) return;
                anySectionRendered = true;

                const card = document.createElement('div');
                card.className = 'border border-line bg-surface overflow-hidden';

                let rowsHtml = '';
                filledFields.forEach((item) => {
                    let valueHtml = '';
                    if (Array.isArray(item.val)) {
                        valueHtml = `<div class="flex flex-wrap gap-1.5 mt-1">${item.val
                            .map((v) => `<span class="inline-flex items-center gap-1 px-2.5 py-1 bg-ink border border-line text-xs text-white">${escapeHtml(v)}</span>`)
                            .join('')}</div>`;
                    } else if (item.type === 'large_text_editor') {
                        valueHtml = `<div class="mt-2 max-h-72 overflow-y-auto border border-line bg-ink p-4">${renderSimpleMarkdown(item.val)}</div>`;
                    } else {
                        valueHtml = `<p class="text-xs text-white whitespace-pre-wrap">${escapeHtml(item.val)}</p>`;
                    }

                    const fileBadge = item.fileName
                        ? `<span class="px-2 py-0.5 bg-ink border border-accent text-accent text-[11px] font-mono">Attached: ${escapeHtml(item.fileName)}</span>`
                        : '';

                    const detailsHtml = item.textVal
                        ? `<div class="mt-2 p-3 bg-ink border border-line"><p class="font-display text-[11px] uppercase tracking-wider text-accent mb-1">Additional Details:</p><p class="text-xs text-muted whitespace-pre-wrap">${escapeHtml(item.textVal)}</p></div>`
                        : '';

                    rowsHtml += `
                        <div class="border-l-2 border-accent pl-4 py-1">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                                <dt class="font-display text-xs uppercase tracking-[0.16em] text-muted">${escapeHtml(item.label)}</dt>
                                ${fileBadge}
                            </div>
                            <dd>${valueHtml}${detailsHtml}</dd>
                        </div>
                    `;
                });

                card.innerHTML = `
                    <div class="bg-box px-5 py-3 border-b border-line flex justify-between items-center">
                        <h3 class="font-display text-sm font-semibold text-white uppercase tracking-[0.08em] flex items-center gap-2.5">
                            <span>${escapeHtml(icon)}</span>
                            <span>${escapeHtml(title)}</span>
                        </h3>
                        <button type="button" data-jump-edit="${idx}" class="font-display text-xs uppercase tracking-[0.14em] text-accent hover:text-white cursor-pointer">
                            Edit
                        </button>
                    </div>
                    <div class="p-5 space-y-4">${rowsHtml}</div>
                `;

                card.querySelector('[data-jump-edit]')?.addEventListener('click', () => {
                    activeStep = idx;
                    setViewMode('form');
                });

                reviewBody.appendChild(card);
            });

            if (!anySectionRendered) {
                reviewBody.innerHTML = '<div class="border border-dashed border-line p-8 text-center text-sm text-muted">No answers filled yet. Return to the questionnaire to get started.</div>';
            }
        };

        const setViewMode = (mode) => {
            viewMode = mode;
            const isReview = mode === 'review';
            if (formView) formView.classList.toggle('hidden', isReview);
            if (reviewView) reviewView.classList.toggle('hidden', !isReview);
            if (isReview) renderReview();
            else renderStep();
            wizard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        };

        const saveDraft = (silent = false) => {
            try {
                const formData = collectSrsFormData();
                localStorage.setItem(
                    storageKey,
                    JSON.stringify({
                        formData,
                        activeStep,
                        savedAt: new Date().toISOString(),
                    }),
                );
                const nowStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                if (lastSavedEl) lastSavedEl.textContent = `(${nowStr})`;
                if (!silent) showToast(`Draft saved locally at ${nowStr}.`);
            } catch {
                if (!silent) showToast('Unable to save draft locally.');
            }
        };

        const scheduleAutoSave = () => {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => saveDraft(true), 1200);
        };

        // Check existing draft in localStorage (only if form has no server-side old() errors)
        if (!wizard.dataset.hasErrors) {
            try {
                const raw = localStorage.getItem(storageKey);
                if (raw) {
                    const parsed = JSON.parse(raw);
                    if (parsed?.formData && Object.keys(parsed.formData).length > 0) {
                        pendingDraft = parsed;
                        const pName = parsed.formData?.project_overview?.project_name || 'Saved Draft';
                        const time = parsed.savedAt
                            ? new Date(parsed.savedAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                            : 'Earlier session';
                        const stepNum = typeof parsed.activeStep === 'number' ? parsed.activeStep + 1 : 1;
                        if (draftMetaEl) {
                            draftMetaEl.textContent = `Found a saved draft for "${pName}" from ${time} (Section ${stepNum} of ${steps.length}).`;
                        }
                        draftBanner?.classList.remove('hidden');
                    }
                }
            } catch {}
        }

        // If server validation returned errors, jump to the first section containing an error
        const firstError = wizard.querySelector('.field__error');
        if (firstError) {
            const errStep = firstError.closest('[data-wizard-step]');
            if (errStep) {
                activeStep = Number(errStep.dataset.wizardStep || 0);
            }
        }

        resumeBtn?.addEventListener('click', () => {
            if (!pendingDraft) return;
            applySrsFormData(pendingDraft.formData);
            if (typeof pendingDraft.activeStep === 'number') {
                activeStep = Math.min(steps.length - 1, Math.max(0, pendingDraft.activeStep));
            }
            draftBanner?.classList.add('hidden');
            renderStep();
            showToast('Resumed saved draft.');
        });

        dismissBtn?.addEventListener('click', () => {
            draftBanner?.classList.add('hidden');
        });

        saveDraftBtns.forEach((btn) => btn.addEventListener('click', () => saveDraft(false)));

        exportBtns.forEach((btn) =>
            btn.addEventListener('click', () => {
                const formData = collectSrsFormData();
                const projectName = formData?.project_overview?.project_name || formSlug;
                const cleanName = String(projectName).toLowerCase().replace(/[^a-z0-9]+/g, '-');
                const payload = {
                    app: 'Agape Infosys SRS Wizard',
                    version: '2.5',
                    exportedAt: new Date().toISOString(),
                    formSlug,
                    activeStep,
                    completionStats: getStats(),
                    formData,
                };
                const blob = new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `${cleanName}-srs-draft-${new Date().toISOString().split('T')[0]}.json`;
                a.click();
                URL.revokeObjectURL(url);
                showToast(`Exported "${projectName}" JSON draft.`);
            }),
        );

        importBtn?.addEventListener('click', () => importInput?.click());
        importInput?.addEventListener('change', (e) => {
            const file = e.target.files?.[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (ev) => {
                try {
                    const parsed = JSON.parse(String(ev.target?.result || ''));
                    const imported = parsed.formData || parsed;
                    if (!imported || typeof imported !== 'object') {
                        showToast('Invalid JSON structure.');
                        return;
                    }
                    applySrsFormData(imported);
                    if (typeof parsed.activeStep === 'number') {
                        activeStep = Math.min(steps.length - 1, Math.max(0, parsed.activeStep));
                    }
                    renderStep();
                    saveDraft(true);
                    showToast('Successfully imported JSON answers.');
                } catch {
                    showToast('Failed to parse JSON file.');
                }
            };
            reader.readAsText(file);
            e.target.value = '';
        });

        clearBtn?.addEventListener('click', () => {
            if (!confirm('Clear all answers and start with a blank questionnaire?')) return;
            try {
                localStorage.removeItem(storageKey);
            } catch {}
            applySrsFormData({});
            activeStep = 0;
            draftBanner?.classList.add('hidden');
            if (lastSavedEl) lastSavedEl.textContent = '';
            renderStep();
            showToast('Draft cleared.');
        });

        ribbonBtns.forEach((btn, idx) => {
            btn.addEventListener('click', () => {
                activeStep = idx;
                renderStep();
            });
        });

        jumpSelect?.addEventListener('change', (e) => {
            activeStep = Number(e.target.value || 0);
            renderStep();
        });

        prevBtn?.addEventListener('click', () => {
            if (activeStep > 0) {
                activeStep--;
                renderStep();
            }
        });

        nextBtn?.addEventListener('click', () => {
            if (activeStep < steps.length - 1) {
                activeStep++;
                renderStep();
            } else {
                setViewMode('review');
            }
        });

        reviewOpenBtn?.addEventListener('click', () => setViewMode('review'));
        reviewCloseBtns.forEach((btn) => btn.addEventListener('click', () => setViewMode('form')));

        form.addEventListener('input', () => {
            syncChoiceTextboxes();
            syncArchitectureBanners();
            updateCounts();
            scheduleAutoSave();
        });

        form.addEventListener('change', () => {
            syncChoiceTextboxes();
            syncArchitectureBanners();
            updateCounts();
            scheduleAutoSave();
        });

        form.addEventListener('submit', (e) => {
            // Check required fields across all steps so hidden steps don't fail silently
            for (let idx = 0; idx < steps.length; idx++) {
                const fields = Array.from(steps[idx].querySelectorAll('[data-field][data-field-required="1"]'));
                for (const fieldEl of fields) {
                    if (fieldEl.classList.contains('hidden')) continue;
                    if (!isFieldFilled(getFieldValue(fieldEl))) {
                        e.preventDefault();
                        activeStep = idx;
                        setViewMode('form');
                        const label = fieldEl.dataset.fieldLabel || fieldEl.dataset.field;
                        showToast(`Please complete required field "${label}" in Section ${idx + 1}.`);
                        fieldEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return;
                    }
                }
            }
            // Clear local draft on successful submit
            try {
                localStorage.removeItem(storageKey);
            } catch {}
        });

        initLargeTextEditors(wizard, () => {
            updateCounts();
            scheduleAutoSave();
        });

        syncChoiceTextboxes();
        syncArchitectureBanners();
        renderStep();
    });
};
