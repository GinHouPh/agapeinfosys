(()=>{let e=document.getElementById(`fe-config`);if(!e)return;let t=JSON.parse(e.textContent),n=t.fieldTypes||[],r=t.templates||[],i=t.urls||{},a={};n.forEach(e=>{a[e.value]=e});let o=[{key:`basic`,label:`Basic Fields`},{key:`choice`,label:`Choice Fields`},{key:`advanced`,label:`Advanced`},{key:`layout`,label:`Layout & Content`},{key:`compliance`,label:`Compliance`}],s=[{value:`equals`,label:`Equals`},{value:`not_equals`,label:`Not equals`},{value:`contains`,label:`Contains`},{value:`not_contains`,label:`Does not contain`},{value:`greater_than`,label:`Greater than`},{value:`less_than`,label:`Less than`},{value:`is_filled`,label:`Is filled`},{value:`is_empty`,label:`Is empty`}],c=e=>String(e??``).replace(/[&<>"']/g,e=>({"&":`&amp;`,"<":`&lt;`,">":`&gt;`,'"':`&quot;`,"'":`&#39;`})[e]),l=()=>`tmp_`+Math.random().toString(36).slice(2,10),u=e=>String(e||``).toLowerCase().trim().replace(/[^a-z0-9]+/g,`_`).replace(/^_+|_+$/g,``)||`field_`+Math.random().toString(36).slice(2,6);function d(e){return{_uid:e._uid||(e.id?`id_`+e.id:l()),id:e.id||null,label:e.label||`Untitled Field`,name:e.name||``,type:e.type||`text`,group:e.group||``,section_id:e.section_id||``,options:Array.isArray(e.options)?[...e.options]:a[e.type]?.hasOptions?[`Option 1`,`Option 2`]:[],has_text_input:!!e.has_text_input,text_placeholder:e.text_placeholder||``,placeholder:e.placeholder||``,help_text:e.help_text||``,default_value:e.default_value||``,width:[`full`,`half`,`third`].includes(e.width)?e.width:`full`,css_class:e.css_class||``,validation:Array.isArray(e.validation)?[...e.validation]:[],condition:Array.isArray(e.condition)?e.condition.map(e=>({...e})):[],condition_match:e.condition_match===`any`?`any`:`all`,file_settings:e.file_settings?{...e.file_settings}:{mimes:[`pdf`,`doc`,`docx`],max_kilobytes:5120},is_required:!!e.is_required}}let f=(t.initialFields||[]).map(d),p=f[0]?._uid||null,m=`general`,h=`desktop`,g=!1,_=!1,v=null,y={past:[],future:[]},b=()=>JSON.stringify(f);function x(e){e!==b()&&(y.past.push(e),y.past.length>50&&y.past.shift(),y.future=[],R(!0),z())}function S(){y.past.length!==0&&(y.future.push(b()),f=JSON.parse(y.past.pop()),f.some(e=>e._uid===p)||(p=f[0]?._uid||null),R(!0),z(),J())}function C(){y.future.length!==0&&(y.past.push(b()),f=JSON.parse(y.future.pop()),f.some(e=>e._uid===p)||(p=f[0]?._uid||null),R(!0),z(),J())}let w=document.getElementById(`fe-palette`),T=document.getElementById(`fe-palette-search`),E=document.getElementById(`fe-canvas`),D=document.getElementById(`fe-canvas-wrap`),O=document.getElementById(`fe-inspector`),k=document.getElementById(`fe-count`),A=document.getElementById(`fe-save-status`),j=document.getElementById(`fe-save`),M=document.getElementById(`fe-undo`),N=document.getElementById(`fe-redo`),P=document.getElementById(`fe-toast`),F=document.getElementById(`fe-toast-msg`),I=null;function L(e,t=!1){P&&F&&(F.textContent=e,P.classList.toggle(`border-accent`,t),P.hidden=!1,clearTimeout(I),I=setTimeout(()=>{P.hidden=!0},3500))}function R(e){g=e,A&&(A.textContent=g?`Unsaved changes`:`All changes saved`,A.className=g?`text-xs text-accent`:`text-xs text-muted`)}function z(){M&&(M.disabled=y.past.length===0),N&&(N.disabled=y.future.length===0)}document.querySelectorAll(`[data-fe-mode]`).forEach(e=>{e.addEventListener(`click`,()=>{let t=e.dataset.feMode;document.querySelectorAll(`[data-fe-mode]`).forEach(e=>{let n=e.dataset.feMode===t;e.classList.toggle(`!border-accent`,n),e.classList.toggle(`!text-accent`,n)}),document.querySelectorAll(`[data-fe-pane]`).forEach(e=>{e.classList.toggle(`hidden`,e.dataset.fePane!==t)})})}),document.querySelectorAll(`[data-fe-device]`).forEach(e=>{e.addEventListener(`click`,()=>{h=e.dataset.feDevice,document.querySelectorAll(`[data-fe-device]`).forEach(e=>{let t=e.dataset.feDevice===h;e.setAttribute(`aria-pressed`,String(t)),e.classList.toggle(`bg-accent`,t),e.classList.toggle(`text-on-accent`,t),e.classList.toggle(`text-muted`,!t)}),D&&(D.classList.remove(`max-w-full`,`max-w-[768px]`,`max-w-[390px]`),D.classList.add(h===`tablet`?`max-w-[768px]`:h===`mobile`?`max-w-[390px]`:`max-w-full`))})});function B(){if(!w)return;let e=(T?.value||``).trim().toLowerCase(),t=``;o.forEach(r=>{let i=n.filter(t=>t.category===r.key&&(!e||t.label.toLowerCase().includes(e)||t.value.includes(e)));i.length!==0&&(t+=`<div class="space-y-2">
                <p class="font-display text-[10px] tracking-[0.22em] text-muted uppercase">${c(r.label)}</p>
                <div class="grid grid-cols-1 gap-1.5">`,i.forEach(e=>{t+=`<button type="button" draggable="true" data-palette-type="${c(e.value)}"
                    class="group flex w-full cursor-grab items-center justify-between border border-line bg-surface/60 px-3 py-2 text-left text-xs text-white transition-colors hover:border-accent hover:bg-accent/10 active:cursor-grabbing">
                    <span>${c(e.label)}</span>
                    <span class="font-mono text-[10px] text-muted group-hover:text-accent">+</span>
                </button>`}),t+=`</div></div>`)}),w.innerHTML=t||`<p class="text-xs text-muted">No matching field types.</p>`,w.querySelectorAll(`[data-palette-type]`).forEach(e=>{e.addEventListener(`click`,()=>H(e.dataset.paletteType)),e.addEventListener(`dragstart`,t=>{v={source:`palette`,type:e.dataset.paletteType},t.dataTransfer.effectAllowed=`copy`,t.dataTransfer.setData(`text/plain`,e.dataset.paletteType)}),e.addEventListener(`dragend`,()=>{v=null})})}T&&T.addEventListener(`input`,B);function V(e){let t=a[e]||{label:`Field`,hasOptions:!1},n=t.label,r=new Set(f.map(e=>e.name)),i=u(n),o=i,s=2;for(;r.has(o);)o=`${i}_${s++}`;return d({_uid:l(),label:e===`gdpr`?`I agree to the Privacy Policy and consent to my data being processed.`:e===`terms`?`I accept the Terms & Conditions.`:n,name:o,type:e,options:t.hasOptions?[`Option 1`,`Option 2`,`Option 3`]:[],is_required:e===`email`||e===`gdpr`||e===`terms`,width:`full`})}function H(e,t=null){let n=b(),r=V(e);t!==null&&t>=0&&t<=f.length?f.splice(t,0,r):f.push(r),p=r._uid,x(n),J()}function U(e){let t=e.type;return t===`hidden`?`<div class="border border-dashed border-line bg-ink/50 px-3 py-2 font-mono text-[11px] text-muted">
                Hidden input: <span class="text-white">${c(e.name)}</span> = <span class="text-accent">${c(e.default_value||`(empty)`)}</span>
            </div>`:t===`section_break`?`<div class="border-b border-line pb-2">
                <p class="font-display text-xs tracking-[0.2em] text-white uppercase">${c(e.label)}</p>
                ${e.help_text?`<p class="mt-1 text-xs text-muted">${c(e.help_text)}</p>`:``}
            </div>`:t===`custom_html`?`<div class="border border-line/70 bg-ink/50 p-3 text-xs text-body">
                ${e.help_text?c(e.help_text):`<span class="text-muted">Custom HTML block</span>`}
            </div>`:t===`gdpr`||t===`terms`?`<div class="flex items-start gap-2.5 border border-line bg-ink/40 p-3 text-xs text-white">
                <span class="mt-0.5 inline-block h-3.5 w-3.5 shrink-0 border border-muted"></span>
                <span>${c(e.label)}${e.is_required?` <span class="text-accent">*</span>`:``}</span>
            </div>`:t===`textarea`||t===`large_text_editor`?`<div class="h-16 border border-line bg-ink/60 px-3 py-2 text-xs text-muted">${c(e.placeholder||(t===`large_text_editor`?`Markdown / Specification editor...`:`Long text answer...`))}</div>`:t===`select`?`<div class="flex items-center justify-between border border-line bg-ink/60 px-3 py-2 text-xs text-muted">
                <span>${c(e.placeholder||`Select...`)}</span>
                <span>&#9662;</span>
            </div>`:t===`radio`||t===`checkbox`?`<div class="grid gap-1.5 sm:grid-cols-2">
                ${(e.options||[]).slice(0,4).map(e=>`<div class="flex items-center gap-2 border border-line bg-ink/40 px-2.5 py-1.5 text-[11px] text-body">
                    <span class="inline-block h-3 w-3 shrink-0 border border-muted ${t===`radio`?`rounded-full`:``}"></span>
                    <span class="truncate">${c(e)}</span>
                </div>`).join(``)}
            </div>`:t===`file`?`<div class="border border-dashed border-line bg-ink/40 px-3 py-2.5 text-xs text-muted">
                Choose file (${c((e.file_settings?.mimes||[`pdf`]).join(`, `))})
            </div>`:`<div class="border border-line bg-ink/60 px-3 py-2 text-xs text-muted">${c(e.placeholder||`Enter ${e.label.toLowerCase()}...`)}</div>`}function W(){if(!E)return;if(k&&(k.textContent=`${f.length} ${f.length===1?`field`:`fields`}`),f.length===0){E.innerHTML=`<div class="flex flex-col items-center justify-center border border-dashed border-line p-12 text-center">
                <p class="font-display text-sm tracking-[0.2em] text-white uppercase">Empty Form Canvas</p>
                <p class="mt-2 max-w-md text-xs text-muted">Drag fields from the left palette, click any field type to add it, or start from a pre-built template.</p>
                <button type="button" data-open-templates class="tag mt-5 border-accent text-accent hover:bg-accent hover:text-on-accent">
                    Browse Templates
                </button>
            </div>`,E.querySelector(`[data-open-templates]`)?.addEventListener(`click`,()=>{document.querySelector(`[data-fe-mode="templates"]`)?.click()});return}let e=`<div class="grid gap-4 sm:grid-cols-6">`,t=null;f.forEach((n,r)=>{let i=(n.group||``).trim();i!==``&&i!==t&&(e+=`<div class="sm:col-span-6 mt-2 border-b border-line pb-2">
                    <span class="font-display text-[11px] tracking-[0.22em] text-accent uppercase">${c(i)}</span>
                </div>`),t=i;let o=n._uid===p,s=n.width===`half`?`sm:col-span-3`:n.width===`third`?`sm:col-span-2`:`sm:col-span-6`,l=a[n.type]?.isLayoutOnly||n.type===`gdpr`||n.type===`terms`,u=(n.condition||[]).length>0;e+=`<div draggable="true" data-field-uid="${c(n._uid)}" data-field-index="${r}"
                class="${s} group relative cursor-pointer border p-4 transition-colors ${o?`border-accent bg-accent/5`:`border-line bg-surface/50 hover:border-white/30`}">
                <div class="mb-2.5 flex items-center justify-between gap-2">
                    <div class="flex min-w-0 items-center gap-2">
                        <span class="cursor-grab text-xs text-muted hover:text-white" title="Drag to reorder">&#8942;&#8942;</span>
                        <span class="truncate text-xs font-medium text-white">
                            ${l?c(a[n.type]?.label||n.type):`${c(n.label)}${n.is_required?` <span class="text-accent">*</span>`:``}`}
                        </span>
                        ${u?`<span class="border border-accent/50 px-1.5 py-0.5 font-mono text-[9px] text-accent uppercase">Logic</span>`:``}
                    </div>
                    <div class="flex shrink-0 items-center gap-1 opacity-80 group-hover:opacity-100">
                        <button type="button" data-action="width" title="Cycle width (Full / 1/2 / 1/3)"
                            class="border border-line px-1.5 py-0.5 font-mono text-[10px] text-muted hover:border-accent hover:text-white">
                            ${n.width===`half`?`1/2`:n.width===`third`?`1/3`:`1/1`}
                        </button>
                        <button type="button" data-action="up" title="Move up" ${r===0?`disabled`:``}
                            class="border border-line px-1.5 py-0.5 text-[10px] text-muted hover:border-accent hover:text-white disabled:opacity-30">&uarr;</button>
                        <button type="button" data-action="down" title="Move down" ${r===f.length-1?`disabled`:``}
                            class="border border-line px-1.5 py-0.5 text-[10px] text-muted hover:border-accent hover:text-white disabled:opacity-30">&darr;</button>
                        <button type="button" data-action="duplicate" title="Duplicate field"
                            class="border border-line px-1.5 py-0.5 text-[10px] text-muted hover:border-accent hover:text-white">&#10064;</button>
                        <button type="button" data-action="delete" title="Delete field"
                            class="border border-line px-1.5 py-0.5 text-[10px] text-muted hover:border-accent hover:text-accent">&times;</button>
                    </div>
                </div>
                ${U(n)}
                ${!l&&n.help_text?`<p class="mt-1.5 text-[11px] text-muted">${c(n.help_text)}</p>`:``}
            </div>`}),e+=`</div>`,E.innerHTML=e,E.querySelectorAll(`[data-field-uid]`).forEach(e=>{let t=e.dataset.fieldUid,n=Number(e.dataset.fieldIndex);e.addEventListener(`click`,e=>{e.target.closest(`[data-action]`)||(p=t,W(),K())}),e.querySelectorAll(`[data-action]`).forEach(e=>{e.addEventListener(`click`,n=>{n.stopPropagation(),G(t,e.dataset.action)})}),e.addEventListener(`dragstart`,r=>{v={source:`canvas`,index:n},r.dataTransfer.effectAllowed=`move`,r.dataTransfer.setData(`text/plain`,t),e.classList.add(`opacity-50`)}),e.addEventListener(`dragend`,()=>{v=null,e.classList.remove(`opacity-50`)}),e.addEventListener(`dragover`,t=>{t.preventDefault(),e.classList.add(`ring-1`,`ring-accent`)}),e.addEventListener(`dragleave`,()=>{e.classList.remove(`ring-1`,`ring-accent`)}),e.addEventListener(`drop`,t=>{if(t.preventDefault(),t.stopPropagation(),e.classList.remove(`ring-1`,`ring-accent`),v){if(v.source===`palette`)H(v.type,n);else if(v.source===`canvas`&&v.index!==n){let e=b(),[t]=f.splice(v.index,1);f.splice(n,0,t),p=t._uid,x(e),J()}v=null}})})}E&&(E.addEventListener(`dragover`,e=>{e.preventDefault()}),E.addEventListener(`drop`,e=>{e.preventDefault(),v?.source===`palette`&&(H(v.type),v=null)}));function G(e,t){let n=f.findIndex(t=>t._uid===e);if(n===-1)return;let r=b(),i=f[n];if(t===`width`){let e=[`full`,`half`,`third`];i.width=e[(e.indexOf(i.width)+1)%e.length]}else if(t===`up`&&n>0)[f[n-1],f[n]]=[f[n],f[n-1]];else if(t===`down`&&n<f.length-1)[f[n+1],f[n]]=[f[n],f[n+1]];else if(t===`duplicate`){let e=d({...i,id:null,_uid:l(),name:u(i.name+`_copy`)});f.splice(n+1,0,e),p=e._uid}else if(t===`delete`)f.splice(n,1),p===e&&(p=f[Math.min(n,f.length-1)]?._uid||null);else return;x(r),J()}function K(){if(!O)return;let e=f.find(e=>e._uid===p);if(!e){O.innerHTML=`<p class="text-sm text-muted">Select a field on the canvas to configure its label, validation, width, or conditional logic.</p>`;return}let t=a[e.type]||{},r=f.filter(t=>t._uid!==e._uid&&!a[t.type]?.isLayoutOnly),i=`<div class="mb-4 flex border-b border-line">
            ${[`general`,`logic`,`advanced`].map(e=>`<button type="button" data-inspector-tab="${e}"
                    class="flex-1 border-b-2 pb-2.5 font-display text-[11px] tracking-[0.16em] uppercase transition-colors ${m===e?`border-accent text-white`:`border-transparent text-muted hover:text-white`}">${e===`general`?`General`:e===`logic`?`Logic & Rules`:`Advanced`}</button>`).join(``)}
        </div>`;if(m===`general`)i+=`<div class="space-y-4">
                <div class="field">
                    <label class="field__label">Label *</label>
                    <input class="input text-sm" type="text" data-bind="label" value="${c(e.label)}">
                </div>
                <div class="field">
                    <label class="field__label">Type</label>
                    <select class="input text-sm" data-bind="type">
                        ${n.map(t=>`<option value="${c(t.value)}" ${e.type===t.value?`selected`:``}>${c(t.label)}</option>`).join(``)}
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="field">
                        <label class="field__label">Section</label>
                        <input class="input text-sm" type="text" data-bind="group" value="${c(e.group)}" placeholder="e.g. Contact">
                    </div>
                    <div class="field">
                        <label class="field__label">Width</label>
                        <select class="input text-sm" data-bind="width">
                            <option value="full" ${e.width===`full`?`selected`:``}>Full (1/1)</option>
                            <option value="half" ${e.width===`half`?`selected`:``}>Half (1/2)</option>
                            <option value="third" ${e.width===`third`?`selected`:``}>Third (1/3)</option>
                        </select>
                    </div>
                </div>
                ${!t.isLayoutOnly&&!t.isConsent&&e.type!==`hidden`?`
                    <div class="field">
                        <label class="field__label">Placeholder</label>
                        <input class="input text-sm" type="text" data-bind="placeholder" value="${c(e.placeholder)}">
                    </div>
                `:``}
                <div class="field">
                    <label class="field__label">${e.type===`custom_html`?`HTML Content`:`Help Text`}</label>
                    <textarea class="input text-sm" rows="${e.type===`custom_html`?4:2}" data-bind="help_text">${c(e.help_text)}</textarea>
                </div>
                ${t.hasOptions?`
                    <div class="field">
                        <label class="field__label">Options (one per line)</label>
                        <textarea class="input font-mono text-xs" rows="4" data-bind-options>${c((e.options||[]).join(`
`))}</textarea>
                    </div>
                    <label class="flex cursor-pointer items-center gap-2.5 border border-line p-2.5 text-xs text-white hover:border-accent">
                        <input type="checkbox" class="h-4 w-4 accent-accent" data-bind-bool="has_text_input" ${e.has_text_input?`checked`:``}>
                        <span>Allow additional text note</span>
                    </label>
                `:``}
                ${!t.isLayoutOnly&&e.type!==`hidden`?`
                    <label class="flex cursor-pointer items-center gap-2.5 border border-line p-2.5 text-xs text-white hover:border-accent">
                        <input type="checkbox" class="h-4 w-4 accent-accent" data-bind-bool="is_required" ${e.is_required?`checked`:``}>
                        <span>Required field</span>
                    </label>
                `:``}
            </div>`;else if(m===`logic`){let n=e.condition||[];i+=`<div class="space-y-5">
                ${t.isLayoutOnly?``:`
                    <div class="field">
                        <label class="field__label">Extra Validation Rules</label>
                        <input class="input font-mono text-xs" type="text" data-bind-validation value="${c((e.validation||[]).join(`, `))}" placeholder="min:3, max:120">
                        <p class="text-[11px] text-muted">Laravel rules, comma separated.</p>
                    </div>
                `}
                ${t.isFile?`
                    <div class="grid grid-cols-2 gap-3">
                        <div class="field">
                            <label class="field__label">Accepted Mimes</label>
                            <input class="input font-mono text-xs" type="text" data-bind-mimes value="${c((e.file_settings?.mimes||[]).join(`, `))}" placeholder="pdf, doc">
                        </div>
                        <div class="field">
                            <label class="field__label">Max Size (KB)</label>
                            <input class="input font-mono text-xs" type="number" min="1" max="204800" data-bind-maxkb value="${c(e.file_settings?.max_kilobytes??5120)}">
                        </div>
                    </div>
                `:``}
                <div class="space-y-3 border-t border-line pt-4">
                    <div class="flex items-center justify-between">
                        <span class="field__label !mb-0">Conditional Logic</span>
                        <select class="border border-line bg-surface px-2 py-1 text-[11px] text-white" data-bind="condition_match">
                            <option value="all" ${e.condition_match===`all`?`selected`:``}>Match ALL</option>
                            <option value="any" ${e.condition_match===`any`?`selected`:``}>Match ANY</option>
                        </select>
                    </div>
                    <div class="space-y-2.5" id="fe-condition-rows">
                        ${n.map((e,t)=>`
                            <div class="space-y-2 border border-line bg-surface/50 p-2.5" data-rule-idx="${t}">
                                <div class="flex items-center justify-between gap-2">
                                    <select class="input text-xs" data-rule-prop="field">
                                        <option value="">Select field...</option>
                                        ${r.map(t=>`<option value="${c(t.name)}" ${e.field===t.name?`selected`:``}>${c(t.label)}</option>`).join(``)}
                                    </select>
                                    <button type="button" data-remove-rule="${t}" class="px-1.5 text-xs text-muted hover:text-accent">&times;</button>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <select class="input text-xs" data-rule-prop="operator">
                                        ${s.map(t=>`<option value="${t.value}" ${e.operator===t.value?`selected`:``}>${t.label}</option>`).join(``)}
                                    </select>
                                    <input class="input text-xs" type="text" data-rule-prop="value" value="${c(e.value||``)}" placeholder="Value">
                                </div>
                            </div>
                        `).join(``)}
                    </div>
                    <button type="button" id="fe-add-rule" class="tag w-full justify-center hover:border-accent hover:text-accent">
                        + Add Condition Rule
                    </button>
                </div>
            </div>`}else i+=`<div class="space-y-4">
                <div class="field">
                    <label class="field__label">Machine Key</label>
                    <input class="input font-mono text-xs" type="text" data-bind="name" value="${c(e.name)}">
                    <p class="text-[11px] text-muted">Lowercase letters, digits, underscores.</p>
                </div>
                <div class="field">
                    <label class="field__label">Default Value</label>
                    <input class="input text-sm" type="text" data-bind="default_value" value="${c(e.default_value)}" placeholder="Static value or {url:param}">
                    <p class="text-[11px] text-muted">Use <code class="text-accent">{url:utm_source}</code> to pre-fill from query params.</p>
                </div>
                <div class="field">
                    <label class="field__label">Custom CSS Class</label>
                    <input class="input font-mono text-xs" type="text" data-bind="css_class" value="${c(e.css_class)}">
                </div>
                <div class="field">
                    <label class="field__label">Wizard Section ID</label>
                    <input class="input font-mono text-xs" type="text" data-bind="section_id" value="${c(e.section_id)}">
                </div>
            </div>`;O.innerHTML=i,O.querySelectorAll(`[data-inspector-tab]`).forEach(e=>{e.addEventListener(`click`,()=>{m=e.dataset.inspectorTab,K()})}),O.querySelectorAll(`[data-bind]`).forEach(t=>{t.addEventListener(`input`,()=>{let n=b(),r=t.dataset.bind;e[r]=t.value,r===`type`&&(a[e.type]?.hasOptions&&(!e.options||e.options.length===0)&&(e.options=[`Option 1`,`Option 2`]),K()),x(n),W()})}),O.querySelectorAll(`[data-bind-bool]`).forEach(t=>{t.addEventListener(`change`,()=>{let n=b();e[t.dataset.bindBool]=t.checked,x(n),W()})});let o=O.querySelector(`[data-bind-options]`);o&&o.addEventListener(`input`,()=>{let t=b();e.options=o.value.split(/\r?\n/).map(e=>e.trim()).filter(Boolean),x(t),W()});let l=O.querySelector(`[data-bind-validation]`);l&&l.addEventListener(`input`,()=>{let t=b();e.validation=l.value.split(`,`).map(e=>e.trim()).filter(Boolean),x(t)});let u=O.querySelector(`[data-bind-mimes]`);u&&u.addEventListener(`input`,()=>{let t=b();e.file_settings=e.file_settings||{},e.file_settings.mimes=u.value.split(`,`).map(e=>e.trim()).filter(Boolean),x(t),W()});let d=O.querySelector(`[data-bind-maxkb]`);d&&d.addEventListener(`input`,()=>{let t=b();e.file_settings=e.file_settings||{},e.file_settings.max_kilobytes=Number(d.value)||5120,x(t)}),O.querySelector(`#fe-add-rule`)?.addEventListener(`click`,()=>{let t=b();e.condition=e.condition||[],e.condition.push({field:r[0]?.name||``,operator:`equals`,value:``}),x(t),J()}),O.querySelectorAll(`[data-remove-rule]`).forEach(t=>{t.addEventListener(`click`,()=>{let n=b();e.condition.splice(Number(t.dataset.removeRule),1),x(n),J()})}),O.querySelectorAll(`[data-rule-idx]`).forEach(t=>{let n=Number(t.dataset.ruleIdx);t.querySelectorAll(`[data-rule-prop]`).forEach(t=>{t.addEventListener(`input`,()=>{let r=b();e.condition[n][t.dataset.ruleProp]=t.value,x(r)})})})}document.querySelectorAll(`[data-apply-template]`).forEach(e=>{e.addEventListener(`click`,()=>{let t=r.find(t=>t.id===e.dataset.applyTemplate);if(!t||f.length>0&&!confirm(`Replace current ${f.length} fields with the "${t.name}" template? (You can Undo this.)`))return;let n=b();f=t.fields.map(e=>d({...e,_uid:l()})),p=f[0]?._uid||null,x(n),J(),document.querySelector(`[data-fe-mode="builder"]`)?.click(),L(`Applied "${t.name}" template. Click Save to persist.`)})}),document.querySelectorAll(`[data-copy-target]`).forEach(e=>{e.addEventListener(`click`,async()=>{let t=document.getElementById(e.dataset.copyTarget);if(t)try{await navigator.clipboard.writeText(t.value||t.textContent),L(`Copied to clipboard.`)}catch{L(`Copy failed — select and copy manually.`,!0)}})});async function q(){if(!_&&i.sync){_=!0,j&&(j.disabled=!0),A&&(A.textContent=`Saving...`);try{let e=await fetch(i.sync,{method:`PUT`,headers:{"Content-Type":`application/json`,Accept:`application/json`,"X-CSRF-TOKEN":i.csrf},body:JSON.stringify({fields:f.map(e=>({id:e.id,label:e.label,name:e.name,type:e.type,group:e.group||null,section_id:e.section_id||null,options:e.options,has_text_input:e.has_text_input,text_placeholder:e.text_placeholder||null,placeholder:e.placeholder||null,help_text:e.help_text||null,default_value:e.default_value||null,width:e.width,css_class:e.css_class||null,validation:e.validation,condition:e.condition,condition_match:e.condition_match,file_settings:e.file_settings,is_required:e.is_required}))})});if(!e.ok){let t=await e.json().catch(()=>({}));throw Error(t.message||`Could not save form fields.`)}let t=await e.json(),n=f.findIndex(e=>e._uid===p);f=(t.fields||[]).map(d),p=f[Math.max(0,n)]?._uid||f[0]?._uid||null,R(!1),J(),L(`Form fields saved.`)}catch(e){L(e.message||`Save failed.`,!0),A&&(A.textContent=`Save failed`)}finally{_=!1,j&&(j.disabled=!1)}}}j&&j.addEventListener(`click`,q),M&&M.addEventListener(`click`,S),N&&N.addEventListener(`click`,C),document.addEventListener(`keydown`,e=>{(e.ctrlKey||e.metaKey)&&(e.key.toLowerCase()===`s`?(e.preventDefault(),q()):e.key.toLowerCase()===`z`&&!e.shiftKey&&![`INPUT`,`TEXTAREA`,`SELECT`].includes(document.activeElement?.tagName)?(e.preventDefault(),S()):(e.key.toLowerCase()===`y`||e.key.toLowerCase()===`z`&&e.shiftKey)&&![`INPUT`,`TEXTAREA`,`SELECT`].includes(document.activeElement?.tagName)&&(e.preventDefault(),C()))});function J(){B(),W(),K()}z(),R(!1),J()})();