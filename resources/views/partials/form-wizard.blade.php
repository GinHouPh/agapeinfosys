{{--
    Multi-step Form Wizard adopted from srs_genie's WizardForm architecture.
    Expects: $form (App\Models\Form)
--}}
@php
    $sectionMeta = $form->wizard_settings['section_meta'] ?? [];
    $sections = \App\Models\FormField::wizardSections($form->fields, is_array($sectionMeta) ? $sectionMeta : []);
    $action = match (true) {
        request()->routeIs('srs.*') => route('srs.store'),
        request()->routeIs('requirements.*') => route('requirements.store'),
        default => route('forms.store', $form),
    };
@endphp

<div class="shell max-w-6xl" data-form-wizard data-form-slug="{{ $form->slug }}" @if ($errors->any()) data-has-errors="1" @endif>
    @if (session('status'))
        <p class="form-status mb-6" role="status">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <div class="form-status mb-6" role="alert">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($form->fields->isEmpty())
        <p class="text-lg text-muted">
            This form has no questions yet — check back shortly.
        </p>
    @else
        {{-- Floating Toast --}}
        <div data-wizard-toast class="fixed right-6 bottom-6 z-50 hidden flex items-center gap-3 border border-accent bg-box px-5 py-3 text-white shadow-2xl">
            <span class="h-2 w-2 rounded-full bg-accent"></span>
            <span data-wizard-toast-msg class="font-mono text-xs"></span>
        </div>

        {{-- Resume Unsaved Draft Banner --}}
        <div data-wizard-draft-banner class="mb-5 hidden flex-col items-start justify-between gap-3 border-l-4 border-accent bg-box p-4 shadow-lg sm:flex-row sm:items-center">
            <div>
                <div class="font-display text-xs font-semibold tracking-wider text-white uppercase">
                    Unsaved Draft Found
                </div>
                <p data-wizard-draft-meta class="mt-0.5 text-xs text-muted"></p>
            </div>
            <div class="flex w-full justify-end gap-2 sm:w-auto">
                <button type="button" data-wizard-draft-resume class="btn btn--accent !px-3 !py-1.5 !text-xs">
                    Resume Draft
                </button>
                <button type="button" data-wizard-draft-dismiss class="btn btn--ghost !px-3 !py-1.5 !text-xs">
                    Dismiss
                </button>
            </div>
        </div>

        <input type="file" accept=".json,application/json" class="hidden" data-wizard-import-input>

        <form action="{{ $action }}" method="POST" novalidate enctype="multipart/form-data" data-wizard-form>
            @csrf

            {{-- A bot fills this in; a person never sees it. --}}
            <div class="hidden" aria-hidden="true">
                <label for="{{ $honeypot ?? 'hp' }}">Leave this field empty</label>
                <input type="text" id="{{ $honeypot ?? 'hp' }}" name="{{ $honeypot ?? 'hp' }}" tabindex="-1" autocomplete="off">
            </div>

            {{-- Wizard Form View --}}
            <div data-wizard-form-view class="border border-line bg-surface">
                {{-- Header & Toolbar --}}
                <div class="border-b border-line bg-surface p-6">
                    <div class="mb-5 flex flex-col justify-between gap-4 md:flex-row md:items-center">
                        <div>
                            <div class="eyebrow mb-1">Specification Engine</div>
                            <h2 class="!text-2xl font-display font-semibold tracking-[0.06em] text-white uppercase">
                                {{ $form->title }}
                            </h2>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" data-wizard-save-draft class="btn btn--ghost !px-3 !py-1.5 !text-xs" title="Save draft locally">
                                Save Draft <span data-wizard-last-saved class="ml-1 text-[10px] text-muted"></span>
                            </button>
                            <button type="button" data-wizard-export-json class="btn btn--ghost !px-3 !py-1.5 !text-xs" title="Export questionnaire data to JSON">
                                Export JSON
                            </button>
                            <button type="button" data-wizard-import-json class="btn btn--ghost !px-3 !py-1.5 !text-xs" title="Import questionnaire JSON">
                                Import JSON
                            </button>
                            <button type="button" data-wizard-clear-draft class="btn btn--ghost hidden !px-3 !py-1.5 !text-xs hover:!border-accent" title="Clear questionnaire answers">
                                Clear
                            </button>
                            <button type="button" data-wizard-review-open class="btn btn--ghost !px-3 !py-1.5 !text-xs">
                                Review
                            </button>
                        </div>
                    </div>

                    {{-- Stepper Progress --}}
                    <div class="space-y-2">
                        <div class="flex justify-between font-display text-xs tracking-[0.16em] text-muted uppercase">
                            <span data-wizard-step-label>Section 1 of {{ count($sections) }}</span>
                            <span data-wizard-step-pct class="text-accent">{{ count($sections) > 0 ? round(100 / count($sections)) : 100 }}%</span>
                        </div>
                        <div class="h-1.5 overflow-hidden border border-line bg-ink">
                            <div data-wizard-progress-bar class="h-full bg-accent transition-all duration-300 ease-out" style="width: {{ count($sections) > 0 ? round(100 / count($sections)) : 100 }}%"></div>
                        </div>
                    </div>
                </div>

                {{-- Section Navigation Ribbon --}}
                <div class="overflow-x-auto border-b border-line bg-box px-4 py-2.5">
                    <div class="flex min-w-max items-center gap-1.5">
                        @foreach ($sections as $idx => $sec)
                            <button
                                type="button"
                                data-wizard-ribbon-btn="{{ $idx }}"
                                class="flex cursor-pointer items-center gap-1.5 border px-3 py-1.5 font-display text-xs tracking-wider uppercase transition-all select-none {{ $idx === 0 ? 'border-accent bg-accent font-semibold text-on-accent' : 'border-line bg-surface text-muted hover:text-white' }}"
                                title="{{ $sec['title'] }}"
                            >
                                <span class="text-xs">{{ $sec['icon'] }}</span>
                                <span>{{ $idx + 1 }}. {{ $sec['short_title'] }}</span>
                                <span data-ribbon-dot class="hidden h-1.5 w-1.5 rounded-full bg-accent"></span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Step Panels --}}
                <div class="bg-ink p-6 md:p-8">
                    @foreach ($sections as $idx => $sec)
                        <div
                            data-wizard-step="{{ $idx }}"
                            data-section-id="{{ $sec['id'] }}"
                            data-section-title="{{ $sec['title'] }}"
                            data-section-icon="{{ $sec['icon'] }}"
                            class="{{ $idx === 0 ? '' : 'hidden' }}"
                        >
                            <div class="mb-8 flex items-center gap-4 border-b border-line pb-6">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center border border-line bg-box text-2xl">
                                    {{ $sec['icon'] }}
                                </div>
                                <div>
                                    <h3 class="!text-xl sm:!text-2xl font-display font-semibold tracking-[0.06em] text-white uppercase">
                                        {{ $sec['title'] }}
                                    </h3>
                                    <p class="mt-1 font-body text-xs text-muted">
                                        {{ $sec['description'] ?: 'Specify detailed parameters for this dimension.' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Dynamic Architectural Context Banners --}}
                            @if ($sec['id'] === 'system_type')
                                <div class="mb-6 flex items-start gap-3 border border-accent/30 bg-accent/10 p-4 text-xs">
                                    <div>
                                        <p class="font-display font-semibold tracking-wider text-white uppercase">
                                            System Topology &amp; Subsystem Portals
                                        </p>
                                        <p class="mt-0.5 leading-relaxed text-muted">
                                            Define the structural topology of your solution — whether frontend-only, monolithic client + /admin, decoupled separate web apps, or a multi-tenant SaaS / Marketplace ecosystem.
                                        </p>
                                    </div>
                                </div>
                            @endif

                            @if ($sec['id'] === 'user_roles')
                                <div data-arch-banner="no_admin" class="mb-6 hidden border border-emerald-500/30 bg-emerald-950/40 p-4 text-xs">
                                    <p class="font-display font-semibold tracking-wider text-emerald-300 uppercase">
                                        Architecture Note: No Admin Panel Topology
                                    </p>
                                    <p class="mt-0.5 leading-relaxed text-emerald-200/80">
                                        You specified that this project requires no administrative panel. Focus on primary end-user/client roles below.
                                    </p>
                                </div>

                                <div data-arch-banner="multi_tenant" class="mb-6 hidden border border-sky-500/30 bg-sky-950/40 p-4 text-xs">
                                    <p class="font-display font-semibold tracking-wider text-sky-300 uppercase">
                                        Architecture Note: Multi-Tenant B2B SaaS Detected
                                    </p>
                                    <p class="mt-0.5 leading-relaxed text-sky-200/80">
                                        Model tenant isolation roles (Tenant Org Owner, Member, Billing Lead) alongside Central Platform SuperAdmin operations.
                                    </p>
                                </div>

                                <div data-arch-banner="marketplace" class="mb-6 hidden border border-purple-500/30 bg-purple-950/40 p-4 text-xs">
                                    <p class="font-display font-semibold tracking-wider text-purple-300 uppercase">
                                        Architecture Note: Multi-Sided Marketplace Detected
                                    </p>
                                    <p class="mt-0.5 leading-relaxed text-purple-200/80">
                                        Model separate Buyer, Vendor/Merchant, and SuperAdmin realms with escrow and commission controls.
                                    </p>
                                </div>
                            @endif

                            <div class="grid gap-6 sm:grid-cols-2">
                                @foreach ($sec['fields'] as $field)
                                    <div class="{{ $field->width === 'half' ? 'sm:col-span-1' : 'sm:col-span-2' }} border border-line/80 bg-box/40 p-4 sm:p-5">
                                        @include('forms.field', ['field' => $field])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Footer Navigation --}}
                <div class="flex flex-col items-center justify-between gap-3 border-t border-line bg-surface p-4 sm:flex-row sm:p-6">
                    <div class="flex w-full items-center justify-between gap-3 sm:w-auto sm:justify-start">
                        <button type="button" data-wizard-prev class="btn btn--ghost !px-4 !py-2 !text-xs">
                            &larr; Previous
                        </button>

                        <div class="flex items-center gap-2 font-display text-xs text-muted">
                            <span class="hidden text-[11px] tracking-wider uppercase md:inline">Jump to:</span>
                            <select data-wizard-jump class="cursor-pointer border border-line bg-box px-2.5 py-1.5 font-display text-xs tracking-wider text-white uppercase outline-none hover:border-accent">
                                @foreach ($sections as $idx => $sec)
                                    <option value="{{ $idx }}" data-base-title="{{ $sec['title'] }}" class="bg-surface text-white">
                                        {{ $sec['title'] }} (0/{{ $sec['fields']->count() }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex w-full justify-end sm:w-auto">
                        <button type="button" data-wizard-next class="btn btn--accent !px-5 !py-2 !text-xs">
                            <span data-next-label>{{ count($sections) <= 1 ? 'Review Summary' : 'Next Section' }}</span> &rarr;
                        </button>
                    </div>
                </div>
            </div>

            {{-- Pre-Flight Review Summary View --}}
            <div data-wizard-review-view class="hidden border border-line bg-surface">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-line bg-box p-6">
                    <div>
                        <div class="eyebrow mb-1">Pre-Flight Review</div>
                        <h2 class="!text-xl font-display font-semibold tracking-[0.06em] text-white uppercase">
                            Requirements Summary
                        </h2>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" data-wizard-save-draft class="btn btn--ghost !px-3 !py-1.5 !text-xs">
                            Save Draft
                        </button>
                        <button type="button" data-wizard-export-json class="btn btn--ghost !px-3 !py-1.5 !text-xs">
                            Export JSON
                        </button>
                        <button type="button" data-wizard-review-close class="btn btn--ghost !px-3 !py-1.5 !text-xs">
                            Back to Questionnaire
                        </button>
                    </div>
                </div>

                <div class="space-y-6 p-6 md:p-8">
                    <div class="border border-accent/40 bg-ink p-6">
                        <div class="mb-2 font-display text-xs font-semibold tracking-[0.2em] text-accent uppercase">
                            Completion Status
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="h-2 flex-1 border border-line bg-surface">
                                <div data-wizard-review-bar class="h-2 bg-accent transition-all duration-500" style="width: 0%"></div>
                            </div>
                            <span data-wizard-review-pct class="font-display text-2xl font-semibold text-white">0%</span>
                        </div>
                        <p data-wizard-review-count class="mt-2 font-body text-xs text-muted">0 of 0 fields completed</p>
                    </div>

                    <div data-wizard-review-body class="space-y-6"></div>
                </div>

                <div class="flex flex-col items-start gap-4 border-t border-line bg-surface p-6 sm:flex-row sm:items-center sm:justify-between">
                    <button type="button" data-wizard-review-close class="btn btn--ghost !px-4 !py-2.5 !text-xs">
                        &larr; Back to Edit
                    </button>

                    <button type="submit" class="btn btn--accent">
                        {{ $form->submit_label }}
                        <span class="btn__strip" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>
