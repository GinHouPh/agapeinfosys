<?php

namespace App\Http\Requests;

use App\Enums\MenuItemType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuItemRequest extends FormRequest
{
    /**
     * Only signed-in admins manage menu items.
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
        $menuId = $this->route('menu')?->id;

        return [
            'label' => ['required', 'string', 'max:160'],
            'link_type' => ['required', Rule::enum(MenuItemType::class)],
            'link_value' => ['required', 'string', 'max:255'],
            'parent_id' => [
                'nullable', 'integer',
                Rule::exists('menu_items', 'id')->where('menu_id', $menuId),
            ],
            'icon' => ['nullable', 'string', 'max:40', Rule::in(array_keys(config('navigation.icons', [])))],
            'badge' => ['nullable', 'string', 'max:40'],
            'css_class' => ['nullable', 'string', 'max:160'],
            'target' => ['required', Rule::in(['_self', '_blank'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Coerce the toggles and the optional order before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'sort_order' => $this->input('sort_order') === null || $this->input('sort_order') === ''
                ? 0
                : $this->input('sort_order'),
            'parent_id' => $this->input('parent_id') === '' ? null : $this->input('parent_id'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        $validated['sort_order'] = (int) $validated['sort_order'];

        return $validated;
    }
}
