<?php

namespace App\Http\Requests;

use App\Builder\DesignTokens;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateDesignThemeRequest extends FormRequest
{
    /**
     * Only admins who manage the design system may edit a theme.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('design.manage') === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:300'],
            'is_active' => ['sometimes', 'boolean'],

            'tokens' => ['sometimes', 'array'],
            'tokens.dark' => ['sometimes', 'array'],
            'tokens.light' => ['sometimes', 'array'],
            'tokens.font' => ['sometimes', 'array'],
            'tokens.radius' => ['sometimes', 'array'],
            'tokens.shadow' => ['sometimes', 'array'],

            'tokens.font.display' => ['nullable', 'string', Rule::in(array_keys(config('design.fonts.display', [])))],
            'tokens.font.body' => ['nullable', 'string', Rule::in(array_keys(config('design.fonts.body', [])))],

            'tokens.radius.*' => ['nullable', 'string', Rule::in(array_keys(config('design.scales.radius', [])))],
            'tokens.shadow.*' => ['nullable', 'string', Rule::in(array_keys(config('design.scales.shadow', [])))],
        ];
    }

    /**
     * Validate the colour values against the token list.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $tokens = $this->input('tokens', []);

            if (! is_array($tokens)) {
                return;
            }

            foreach (['dark', 'light'] as $scheme) {
                foreach ((array) ($tokens[$scheme] ?? []) as $name => $value) {
                    if (! array_key_exists($name, DesignTokens::COLORS)) {
                        $validator->errors()->add(
                            "tokens.{$scheme}.{$name}",
                            'That is not a design token.',
                        );

                        continue;
                    }

                    if (! $this->isColour((string) $value)) {
                        $validator->errors()->add(
                            "tokens.{$scheme}.{$name}",
                            'Use a hex colour such as #d90429, or an rgba() value.',
                        );
                    }
                }
            }
        });
    }

    /**
     * Coerce the toggle before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean(
                'is_active',
                $this->route('theme')?->is_active ?? false,
            ),
        ]);
    }

    /**
     * Whether a value is a hex or rgb(a) colour.
     */
    private function isColour(string $value): bool
    {
        $value = trim($value);

        if ($value === '') {
            return false;
        }

        return preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $value) === 1
            || preg_match('/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*[\d.]+\s*)?\)$/i', $value) === 1;
    }
}
