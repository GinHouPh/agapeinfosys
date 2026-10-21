<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'is_admin' => ['boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')],
        ];
    }

    /**
     * Coerce the admin toggle before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_admin' => $this->boolean('is_admin'),
        ]);
    }
}
