{{--
    One builder-managed question, rendered by its type.
    Expects: $field (App\Models\FormField)
--}}
@php
    $inputName = "answers.{$field->name}";
    $inputId = 'field-'.$field->name;
    $resolvedDefault = $field->default_value;
    if (is_string($resolvedDefault) && preg_match('/^\{url:([a-zA-Z0-9_\-]+)\}$/', trim($resolvedDefault), $m)) {
        $resolvedDefault = (string) request()->query($m[1], '');
    }
    $value = old($inputName, $resolvedDefault);
    $textName = "answers.{$field->name}_text";
    $textValue = old($textName);
    $fileNameInput = "answers.{$field->name}_file_name";
    $fileNameValue = old($fileNameInput);
    $widthClass = match ($field->width) {
        'half' => 'sm:col-span-3',
        'third' => 'sm:col-span-2',
        default => 'sm:col-span-6',
    };
    if ($field->css_class) {
        $widthClass .= ' '.e($field->css_class);
    }
    $condition = $field->condition ?? [];
    $conditionMatch = $field->condition_match ?? 'all';
    $hasTextInput = (bool) $field->has_text_input;
    $optionCount = count($field->options ?? []);
    $gridColsClass = $optionCount >= 12 ? 'sm:grid-cols-2 lg:grid-cols-3' : 'sm:grid-cols-2';
@endphp

@if ($field->type === \App\Enums\FormFieldType::Hidden)
    <input type="hidden" id="{{ $inputId }}" name="{{ $inputName }}" value="{{ $value }}">
@elseif ($field->type === \App\Enums\FormFieldType::SectionBreak)
    <div class="sm:col-span-6 border-b border-line pb-4 pt-2 {{ $field->css_class }}"
         data-field="{{ $field->name }}"
         data-field-type="{{ $field->type->value }}"
         @if ($condition !== []) data-condition="{{ json_encode($condition) }}" data-condition-match="{{ $conditionMatch }}" @endif>
        <h4 class="font-display text-sm tracking-[0.2em] text-white uppercase">{{ $field->label }}</h4>
        @if ($field->help_text)
            <p class="mt-1 text-sm text-muted">{{ $field->help_text }}</p>
        @endif
    </div>
@elseif ($field->type === \App\Enums\FormFieldType::CustomHtml)
    <div class="{{ $widthClass }}"
         data-field="{{ $field->name }}"
         data-field-type="{{ $field->type->value }}"
         @if ($condition !== []) data-condition="{{ json_encode($condition) }}" data-condition-match="{{ $conditionMatch }}" @endif>
        <div class="border border-line/70 bg-surface/60 p-4 text-sm text-body">
            {!! $field->help_text ?: e($field->label) !!}
        </div>
    </div>
@elseif ($field->type->isConsent())
    <div class="{{ $widthClass }}"
         data-field="{{ $field->name }}"
         data-field-label="{{ $field->label }}"
         data-field-type="{{ $field->type->value }}"
         data-field-required="{{ $field->is_required ? '1' : '0' }}"
         @if ($condition !== []) data-condition="{{ json_encode($condition) }}" data-condition-match="{{ $conditionMatch }}" @endif>
        <div class="field">
            <label class="flex cursor-pointer items-start gap-3 border border-line bg-surface p-4 transition-colors duration-200 hover:border-accent has-[:checked]:border-accent has-[:checked]:bg-accent/10">
                <input type="checkbox" id="{{ $inputId }}" name="{{ $inputName }}" value="1"
                       @checked((bool) $value) @required($field->is_required)
                       class="mt-0.5 h-4 w-4 shrink-0 accent-accent">
                <span class="text-sm leading-relaxed text-white">
                    {{ $field->label }}@if ($field->is_required) <span class="text-accent">*</span> @endif
                    @if ($field->help_text)
                        <span class="mt-1 block text-xs text-muted">{{ $field->help_text }}</span>
                    @endif
                </span>
            </label>
            @error($inputName) <span class="field__error">{{ $message }}</span> @enderror
        </div>
    </div>
@elseif ($field->type === \App\Enums\FormFieldType::Checkbox)
    <div class="{{ $widthClass }}"
         data-field="{{ $field->name }}"
         data-field-label="{{ $field->label }}"
         data-field-type="{{ $field->type->value }}"
         data-field-required="{{ $field->is_required ? '1' : '0' }}"
         @if ($hasTextInput) data-has-text-input="1" @endif
         @if ($condition !== []) data-condition="{{ json_encode($condition) }}" @endif>
        <fieldset class="space-y-4">
            <legend class="field__label">
                {{ $field->label }}@if ($field->is_required) <span class="text-accent">*</span> @endif
            </legend>

            <div class="grid gap-3 {{ $gridColsClass }}">
                @foreach ($field->options ?? [] as $option)
                    <label class="flex cursor-pointer items-start gap-3 border border-line bg-surface p-3.5 transition-colors duration-200 hover:border-accent has-[:checked]:border-accent has-[:checked]:bg-accent/10">
                        <input
                            type="checkbox"
                            name="{{ $inputName }}[]"
                            value="{{ $option }}"
                            @checked(in_array($option, (array) $value, true))
                            class="mt-0.5 h-4 w-4 shrink-0 accent-accent"
                        >
                        <span class="text-xs leading-snug text-white">{{ $option }}</span>
                    </label>
                @endforeach
            </div>

            @if ($hasTextInput)
                <div data-additional-details class="{{ empty($value) && empty($textValue) ? 'hidden' : '' }} border-t border-line/60 pt-3">
                    <label class="mb-1.5 block font-display text-[11px] font-semibold tracking-[0.16em] text-accent uppercase" for="{{ $inputId }}-text">
                        Additional Details
                    </label>
                    <textarea
                        class="input text-xs"
                        id="{{ $inputId }}-text"
                        name="{{ $textName }}"
                        rows="2"
                        placeholder="{{ $field->text_placeholder ?: 'Provide additional details...' }}"
                    >{{ $textValue }}</textarea>
                    @error($textName) <span class="field__error">{{ $message }}</span> @enderror
                </div>
            @endif

            @if ($field->help_text)
                <p class="text-sm text-muted">{{ $field->help_text }}</p>
            @endif
            @error($inputName) <span class="field__error">{{ $message }}</span> @enderror
        </fieldset>
    </div>
@elseif ($field->type === \App\Enums\FormFieldType::Radio)
    <div class="{{ $widthClass }}"
         data-field="{{ $field->name }}"
         data-field-label="{{ $field->label }}"
         data-field-type="{{ $field->type->value }}"
         data-field-required="{{ $field->is_required ? '1' : '0' }}"
         @if ($hasTextInput) data-has-text-input="1" @endif
         @if ($condition !== []) data-condition="{{ json_encode($condition) }}" @endif>
        <fieldset class="space-y-4">
            <legend class="field__label">
                {{ $field->label }}@if ($field->is_required) <span class="text-accent">*</span> @endif
            </legend>

            <div class="grid gap-3 {{ $gridColsClass }}">
                @foreach ($field->options ?? [] as $option)
                    <label class="flex cursor-pointer items-start gap-3 border border-line bg-surface p-3.5 transition-colors duration-200 hover:border-accent has-[:checked]:border-accent has-[:checked]:bg-accent/10">
                        <input
                            type="radio"
                            name="{{ $inputName }}"
                            value="{{ $option }}"
                            @checked($value === $option)
                            class="mt-0.5 h-4 w-4 shrink-0 accent-accent"
                        >
                        <span class="text-xs leading-snug text-white">{{ $option }}</span>
                    </label>
                @endforeach
            </div>

            @if ($hasTextInput)
                <div data-additional-details class="{{ empty($value) && empty($textValue) ? 'hidden' : '' }} border-t border-line/60 pt-3">
                    <label class="mb-1.5 block font-display text-[11px] font-semibold tracking-[0.16em] text-accent uppercase" for="{{ $inputId }}-text">
                        Additional Details
                    </label>
                    <textarea
                        class="input text-xs"
                        id="{{ $inputId }}-text"
                        name="{{ $textName }}"
                        rows="2"
                        placeholder="{{ $field->text_placeholder ?: 'Provide additional details...' }}"
                    >{{ $textValue }}</textarea>
                    @error($textName) <span class="field__error">{{ $message }}</span> @enderror
                </div>
            @endif

            @if ($field->help_text)
                <p class="text-sm text-muted">{{ $field->help_text }}</p>
            @endif
            @error($inputName) <span class="field__error">{{ $message }}</span> @enderror
        </fieldset>
    </div>
@elseif ($field->type === \App\Enums\FormFieldType::LargeTextEditor)
    <div class="{{ $widthClass }}"
         data-field="{{ $field->name }}"
         data-field-label="{{ $field->label }}"
         data-field-type="{{ $field->type->value }}"
         data-field-required="{{ $field->is_required ? '1' : '0' }}"
         @if ($condition !== []) data-condition="{{ json_encode($condition) }}" @endif>
        <div class="field">
            <label class="field__label" for="{{ $inputId }}">
                {{ $field->label }}@if ($field->is_required) <span class="text-accent">*</span> @endif
            </label>

            <div class="relative border border-line bg-ink/40" data-large-editor>
                <input type="file" accept=".md,.markdown,.txt,text/markdown,text/plain" class="hidden" data-editor-file-input>
                <input type="hidden" name="{{ $fileNameInput }}" value="{{ $fileNameValue }}" data-editor-file-name-input>

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line bg-surface p-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="flex items-center border border-line bg-box p-0.5">
                            <button type="button" data-editor-tab="write"
                                    class="cursor-pointer bg-accent px-3 py-1 font-display text-xs font-semibold tracking-wider text-on-accent uppercase transition-colors">
                                Write
                            </button>
                            <button type="button" data-editor-tab="preview"
                                    class="cursor-pointer px-3 py-1 font-display text-xs tracking-wider text-muted uppercase transition-colors hover:text-white">
                                Preview
                            </button>
                        </div>

                        <button type="button" data-editor-attach
                                class="flex cursor-pointer items-center gap-1.5 border border-line bg-box px-3 py-1.5 font-display text-xs tracking-wider text-white uppercase transition-colors hover:border-accent">
                            <span class="text-accent">+</span>
                            <span>Attach File</span>
                            <span class="font-mono text-[10px] text-muted normal-case">(.md, .txt)</span>
                        </button>

                        <span data-editor-file-chip class="{{ $fileNameValue ? 'inline-flex' : 'hidden' }} items-center gap-1.5 border border-accent bg-surface px-2.5 py-1 text-xs text-accent">
                            <span data-editor-file-chip-label class="max-w-[200px] truncate font-mono text-[11px]">{{ $fileNameValue }}</span>
                            <button type="button" data-editor-clear-file class="cursor-pointer text-muted hover:text-white" title="Remove file reference">&times;</button>
                        </span>
                    </div>

                    <div class="flex items-center gap-3 font-mono text-xs text-muted">
                        <span data-editor-stats>0 words · 0 chars</span>
                        <button type="button" data-editor-clear-text class="hidden cursor-pointer text-xs text-muted hover:text-accent">
                            Clear
                        </button>
                    </div>
                </div>

                <div data-editor-error class="hidden flex items-center justify-between gap-3 border-b border-accent/40 bg-accent/10 px-4 py-2.5 text-xs text-white">
                    <span data-editor-error-text></span>
                    <button type="button" data-editor-error-close class="cursor-pointer text-muted hover:text-white">&times;</button>
                </div>

                <div data-editor-pane="write">
                    <textarea
                        class="min-h-[220px] w-full resize-y border-0 bg-ink/70 p-4 font-body text-xs leading-relaxed text-white outline-none placeholder:text-muted/60 focus:ring-0 sm:text-sm"
                        id="{{ $inputId }}"
                        name="{{ $inputName }}"
                        rows="10"
                        placeholder="{{ $field->placeholder }}"
                        data-editor-textarea
                        @required($field->is_required)>{{ $value }}</textarea>
                </div>

                <div data-editor-pane="preview" class="hidden max-h-[520px] min-h-[220px] overflow-y-auto bg-ink/90 p-5" data-editor-preview></div>

                <div data-editor-drop-overlay class="pointer-events-none absolute inset-0 z-10 hidden flex-col items-center justify-center border-2 border-dashed border-accent bg-ink/90 p-6 text-center">
                    <p class="font-display text-sm font-semibold tracking-wider text-white uppercase">Drop Markdown or Text File Here</p>
                    <p class="mt-1 font-mono text-xs text-muted">Only .md and .txt files are accepted (max 5 MB)</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line/60 bg-surface/60 px-3 py-2 text-[11px] text-muted">
                    <span>Type Markdown or attach a <code class="bg-box px-1 py-0.5 font-mono text-white">.md</code> or <code class="bg-box px-1 py-0.5 font-mono text-white">.txt</code> specification file.</span>
                    <span class="font-display text-[10px] tracking-wider uppercase">Strict Formats: .md, .txt</span>
                </div>
            </div>

            @if ($field->help_text)
                <p class="text-sm text-muted">{{ $field->help_text }}</p>
            @endif
            @error($inputName) <span class="field__error">{{ $message }}</span> @enderror
        </div>
    </div>
@elseif ($field->type === \App\Enums\FormFieldType::Textarea)
    <div class="{{ $widthClass }}"
         data-field="{{ $field->name }}"
         data-field-label="{{ $field->label }}"
         data-field-type="{{ $field->type->value }}"
         data-field-required="{{ $field->is_required ? '1' : '0' }}"
         @if ($condition !== []) data-condition="{{ json_encode($condition) }}" @endif>
        <div class="field">
            <label class="field__label" for="{{ $inputId }}">
                {{ $field->label }}@if ($field->is_required) <span class="text-accent">*</span> @endif
            </label>
            <textarea class="input" id="{{ $inputId }}" name="{{ $inputName }}" rows="4"
                      placeholder="{{ $field->placeholder }}"
                      @required($field->is_required)>{{ $value }}</textarea>
            @if ($field->help_text)
                <p class="text-sm text-muted">{{ $field->help_text }}</p>
            @endif
            @error($inputName) <span class="field__error">{{ $message }}</span> @enderror
        </div>
    </div>
@elseif ($field->type === \App\Enums\FormFieldType::Select)
    <div class="{{ $widthClass }}"
         data-field="{{ $field->name }}"
         data-field-label="{{ $field->label }}"
         data-field-type="{{ $field->type->value }}"
         data-field-required="{{ $field->is_required ? '1' : '0' }}"
         @if ($condition !== []) data-condition="{{ json_encode($condition) }}" @endif>
        <div class="field">
            <label class="field__label" for="{{ $inputId }}">
                {{ $field->label }}@if ($field->is_required) <span class="text-accent">*</span> @endif
            </label>
            <select class="input" id="{{ $inputId }}" name="{{ $inputName }}" @required($field->is_required)>
                <option value="">{{ $field->placeholder ?: 'Select…' }}</option>
                @foreach ($field->options ?? [] as $option)
                    <option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>
                @endforeach
            </select>
            @if ($field->help_text)
                <p class="text-sm text-muted">{{ $field->help_text }}</p>
            @endif
            @error($inputName) <span class="field__error">{{ $message }}</span> @enderror
        </div>
    </div>
@elseif ($field->type === \App\Enums\FormFieldType::File)
    <div class="{{ $widthClass }}"
         data-field="{{ $field->name }}"
         data-field-label="{{ $field->label }}"
         data-field-type="{{ $field->type->value }}"
         data-field-required="{{ $field->is_required ? '1' : '0' }}"
         @if ($condition !== []) data-condition="{{ json_encode($condition) }}" @endif>
        <div class="field">
            <label class="field__label" for="{{ $inputId }}">
                {{ $field->label }}@if ($field->is_required) <span class="text-accent">*</span> @endif
            </label>
            <input class="input" type="file" id="{{ $inputId }}" name="{{ $inputName }}"
                   @if ($mimes = $field->acceptedMimes()) accept="{{ implode(',', $mimes) }}" @endif
                   @required($field->is_required)>
            @if ($field->help_text)
                <p class="text-sm text-muted">{{ $field->help_text }}</p>
            @endif
            <p class="text-xs text-muted">Up to {{ round($field->maxUploadKilobytes() / 1024, 1) }} MB.</p>
            @error($inputName) <span class="field__error">{{ $message }}</span> @enderror
        </div>
    </div>
@else
    <div class="{{ $widthClass }}"
         data-field="{{ $field->name }}"
         data-field-label="{{ $field->label }}"
         data-field-type="{{ $field->type->value }}"
         data-field-required="{{ $field->is_required ? '1' : '0' }}"
         @if ($condition !== []) data-condition="{{ json_encode($condition) }}" @endif>
        <div class="field">
            <label class="field__label" for="{{ $inputId }}">
                {{ $field->label }}@if ($field->is_required) <span class="text-accent">*</span> @endif
            </label>
            <input class="input" type="{{ $field->type->inputType() }}" id="{{ $inputId }}" name="{{ $inputName }}"
                   value="{{ $value }}" placeholder="{{ $field->placeholder }}" @required($field->is_required)>
            @if ($field->help_text)
                <p class="text-sm text-muted">{{ $field->help_text }}</p>
            @endif
            @error($inputName) <span class="field__error">{{ $message }}</span> @enderror
        </div>
    </div>
@endif
