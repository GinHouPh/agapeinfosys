<?php

namespace App\Http\Requests;

use App\Models\Category;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $category = $this->route('category');
        $categoryId = $category instanceof Category ? $category->getKey() : null;

        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id'),
                function (string $attribute, mixed $value, Closure $fail) use ($category): void {
                    if ($value === null || ! $category instanceof Category) {
                        return;
                    }

                    $parent = Category::find((int) $value);

                    if ($parent !== null && $parent->isDescendantOf($category)) {
                        $fail('A category cannot be nested under itself or one of its descendants.');
                    }
                },
            ],
            'name' => ['required', 'string', 'max:160'],
            'slug' => [
                'nullable', 'string', 'max:180', 'alpha_dash',
                Rule::unique('categories', 'slug')->ignore($categoryId),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Coerce empty parent/sort values and the visibility toggle before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'parent_id' => $this->input('parent_id') === '' ? null : $this->input('parent_id'),
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->filled('sort_order') ? $this->input('sort_order') : 0,
        ]);
    }
}
