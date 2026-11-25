@extends('layouts.admin')

@section('title', 'Edit Form')
@section('heading', $form->title)

@section('content')
    {{-- Top Editor Toolbar --}}
    <div class="mb-5 flex flex-wrap items-center justify-between gap-4 border-b border-line pb-4">
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" data-fe-mode="builder"
                    class="tag !border-accent !text-accent">Builder</button>
            <button type="button" data-fe-mode="settings"
                    class="tag hover:border-accent hover:text-accent">Settings</button>
            <button type="button" data-fe-mode="templates"
                    class="tag hover:border-accent hover:text-accent">Templates</button>
            <button type="button" data-fe-mode="embed"
                    class="tag hover:border-accent hover:text-accent">Embed &amp; Share</button>

            <span class="badge ml-2 {{ $form->is_active ? 'badge--on' : 'badge--off' }}">
                {{ $form->is_active ? 'Active' : 'Draft' }}
            </span>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-1">
                <button type="button" id="fe-undo" class="tag disabled:opacity-40" title="Undo (Ctrl+Z)" disabled>&#8630;</button>
                <button type="button" id="fe-redo" class="tag disabled:opacity-40" title="Redo (Ctrl+Shift+Z)" disabled>&#8631;</button>
            </div>

            <span id="fe-save-status" class="text-xs text-muted">All changes saved</span>

            <a href="{{ route('admin.submissions.index', ['form' => $form->id]) }}"
               class="tag hover:border-accent hover:text-accent">
                Inbox ({{ $form->submissions_count ?? 0 }})
            </a>

            <a href="{{ route('forms.show', $form) }}" target="_blank" rel="noopener"
               class="tag hover:border-accent hover:text-accent">
                View Live
            </a>

            <form action="{{ route('admin.forms.duplicate', $form) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="tag hover:border-accent hover:text-accent">Duplicate</button>
            </form>

            <button type="button" id="fe-save" class="btn btn--accent">
                Save Fields
                <span class="btn__strip" aria-hidden="true"></span>
            </button>
        </div>
    </div>

    {{-- Mode 1: 3-Pane Visual Builder --}}
    <div data-fe-pane="builder" class="grid gap-5 xl:grid-cols-[250px_minmax(0,1fr)_330px]">
        {{-- Left: Field Palette --}}
        <aside class="admin-card h-fit">
            <h2 class="mb-3 border-b border-line pb-3 font-display text-xs tracking-[0.24em] text-white uppercase">
                Field Palette
            </h2>
            <input type="search" id="fe-palette-search" class="input mb-4 text-xs" placeholder="Filter fields...">
            <div id="fe-palette" class="max-h-[68vh] space-y-5 overflow-y-auto pr-1"></div>
        </aside>

        {{-- Center: Live Canvas --}}
        <div class="min-w-0">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-display text-xs tracking-[0.24em] text-muted uppercase">Form Canvas</h2>

                <div class="flex items-center gap-3">
                    <div class="flex border border-line bg-surface p-0.5 text-[11px]" role="group" aria-label="Preview width">
                        <button type="button" data-fe-device="desktop" aria-pressed="true"
                                class="cursor-pointer bg-accent px-2.5 py-1 font-display tracking-wider text-on-accent uppercase">Desktop</button>
                        <button type="button" data-fe-device="tablet" aria-pressed="false"
                                class="cursor-pointer px-2.5 py-1 font-display tracking-wider text-muted uppercase hover:text-white">Tablet</button>
                        <button type="button" data-fe-device="mobile" aria-pressed="false"
                                class="cursor-pointer px-2.5 py-1 font-display tracking-wider text-muted uppercase hover:text-white">Mobile</button>
                    </div>
                    <span id="fe-count" class="text-xs text-muted"></span>
                </div>
            </div>

            <div id="fe-canvas-wrap" class="mx-auto max-w-full transition-all duration-200">
                <div id="fe-canvas" class="admin-card min-h-[420px]"></div>
            </div>
        </div>

        {{-- Right: Field Inspector --}}
        <aside class="admin-card h-fit">
            <h2 class="mb-4 border-b border-line pb-3 font-display text-xs tracking-[0.24em] text-white uppercase">
                Field Settings
            </h2>
            <div id="fe-inspector"></div>
        </aside>
    </div>

    {{-- Mode 2: Form Settings --}}
    <div data-fe-pane="settings" class="hidden">
        <div class="admin-card max-w-4xl">
            <h2 class="mb-6 border-b border-line pb-4 font-display text-sm tracking-[0.24em] text-white uppercase">
                Form Settings &amp; Notifications
            </h2>
            @include('admin.forms.form')
        </div>
    </div>

    {{-- Mode 3: Reusable Templates --}}
    <div data-fe-pane="templates" class="hidden">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($templates as $template)
                <div class="admin-card flex flex-col justify-between gap-4">
                    <div>
                        <span class="font-display text-[10px] tracking-[0.22em] text-accent uppercase">
                            {{ $template['category'] }}
                        </span>
                        <h3 class="mt-1 font-display text-base tracking-wide text-white">
                            {{ $template['name'] }}
                        </h3>
                        <p class="mt-2 text-xs leading-relaxed text-muted">
                            {{ $template['description'] }}
                        </p>
                    </div>
                    <div class="flex items-center justify-between border-t border-line pt-3">
                        <span class="font-mono text-[11px] text-muted">{{ count($template['fields']) }} fields</span>
                        <button type="button" data-apply-template="{{ $template['id'] }}"
                                class="tag hover:border-accent hover:text-accent">
                            Use Template
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Mode 4: Embed & Share --}}
    <div data-fe-pane="embed" class="hidden">
        @php
            $publicUrl = route('forms.show', $form);
            $iframeCode = '<iframe src="'.$publicUrl.'" style="width:100%;min-height:640px;border:0;" loading="lazy" title="'.e($form->title).'"></iframe>';
        @endphp
        <div class="admin-card max-w-3xl space-y-6">
            <h2 class="border-b border-line pb-4 font-display text-sm tracking-[0.24em] text-white uppercase">
                Publish &amp; Embed
            </h2>

            <div class="field">
                <label class="field__label" for="fe-share-url">Direct Hosted URL</label>
                <div class="flex gap-2">
                    <input id="fe-share-url" type="text" readonly class="input font-mono text-xs" value="{{ $publicUrl }}">
                    <button type="button" data-copy-target="fe-share-url" class="tag shrink-0 hover:border-accent hover:text-accent">Copy</button>
                </div>
            </div>

            <div class="field">
                <label class="field__label" for="fe-embed-iframe">Iframe Embed Snippet</label>
                <div class="flex gap-2">
                    <textarea id="fe-embed-iframe" readonly rows="3" class="input font-mono text-xs">{{ $iframeCode }}</textarea>
                    <button type="button" data-copy-target="fe-embed-iframe" class="tag h-fit shrink-0 hover:border-accent hover:text-accent">Copy</button>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('admin.forms.index') }}" class="font-display text-xs tracking-[0.24em] text-accent uppercase">
            &larr; Back to forms
        </a>
    </div>

    {{-- Toast Notification --}}
    <div id="fe-toast" class="fixed right-6 bottom-6 z-50 border border-line bg-surface px-4 py-3 text-xs text-white shadow-xl" hidden>
        <span id="fe-toast-msg"></span>
    </div>

    @php
        $editorConfig = [
            'initialFields' => $form->fields->values(),
            'fieldTypes' => $fieldTypes,
            'templates' => $templates,
            'urls' => [
                'sync' => route('admin.forms.builder.sync', $form),
                'csrf' => csrf_token(),
            ],
        ];
    @endphp

    <script type="application/json" id="fe-config">{!! json_encode($editorConfig, JSON_HEX_TAG) !!}</script>
    @vite('resources/js/builder/form-editor.js')
@endsection
