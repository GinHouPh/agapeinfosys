{{--
    One builder-managed question, rendered by its type.
    Expects: $field (App\Models\FormField)
--}}
@php
    $inputName = "answers.{$field->name}";
    $inputId = 'field-'.$field->name;
    $value = old($inputName);
@endphp

@if ($field->type === \App\Enums\FormFieldType::Checkbox)
    <div class="sm:col-span-2 lg:col-span-3">
        <fieldset class="space-y-4">
            <legend class="field__label">
                {{ $field->label }}@if ($field->is_required) <span class="text-accent">*</span> @endif
            </legend>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($field->options ?? [] as $option)
                    <label class="flex cursor-pointer items-center gap-3 border border-line p-4 transition-colors duration-300 hover:border-accent has-[:checked]:border-accent">
                        <input
                            type="checkbox"
                            name="{{ $inputName }}[]"
                            value="{{ $option }}"
                            @checked(in_array($option, (array) $value, true))
                            class="h-4 w-4 accent-accent"
                        >
                        <span class="text-sm text-white">{{ $option }}</span>
                    </label>
                @endforeach
            </div>

            @if ($field->help_text)
                <p class="text-sm text-muted">{{ $field->help_text }}</p>
            @endif
            @error($inputName) <span class="field__error">{{ $message }}</span> @enderror
        </fieldset>
    </div>
@elseif ($field->type === \App\Enums\FormFieldType::Radio)
    <div class="sm:col-span-2 lg:col-span-3">
        <fieldset class="space-y-4">
            <legend class="field__label">
                {{ $field->label }}@if ($field->is_required) <span class="text-accent">*</span> @endif
            </legend>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($field->options ?? [] as $option)
                    <label class="flex cursor-pointer items-center gap-3 border border-line p-4 transition-colors duration-300 hover:border-accent has-[:checked]:border-accent">
                        <input
                            type="radio"
                            name="{{ $inputName }}"
                            value="{{ $option }}"
                            @checked($value === $option)
                            class="h-4 w-4 accent-accent"
                        >
                        <span class="text-sm text-white">{{ $option }}</span>
                    </label>
                @endforeach
            </div>

            @if ($field->help_text)
                <p class="text-sm text-muted">{{ $field->help_text }}</p>
            @endif
            @error($inputName) <span class="field__error">{{ $message }}</span> @enderror
        </fieldset>
    </div>
@elseif ($field->type === \App\Enums\FormFieldType::Textarea)
    <div class="sm:col-span-2">
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
@else
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
@endif
