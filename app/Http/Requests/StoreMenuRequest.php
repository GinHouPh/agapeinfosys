<?php

namespace App\Http\Requests;

use App\Enums\MenuLocation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreMenuRequest extends FormRequest
{
    /**
     * Only signed-in admins manage menus.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'slug' => [
                'nullable', 'string', 'max:180', 'alpha_dash',
                Rule::unique('menus', 'slug'),
            ],
            'location' => ['required', Rule::enum(MenuLocation::class)],
            'description' => ['nullable', 'string', 'max:300'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Derive the slug from the name when left blank.
     *
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        if (($validated['slug'] ?? '') === '' || $validated['slug'] === null) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $this->boolean('is_active', true);

        return $validated;
    }
}
