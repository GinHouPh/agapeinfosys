<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessDetailsRequest extends FormRequest
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
            'brand_name' => ['required', 'string', 'max:120'],
            'legal_name' => ['nullable', 'string', 'max:160'],
            'tagline' => ['nullable', 'string', 'max:240'],
            'founded_year' => ['nullable', 'integer', 'min:1900', 'max:'.now()->year],
            'industry' => ['nullable', 'string', 'max:80'],
            'registration_number' => ['nullable', 'string', 'max:80'],
            'tax_number' => ['nullable', 'string', 'max:80'],
            'website' => ['nullable', 'url', 'max:190'],
            'support_hours' => ['nullable', 'string', 'max:120'],
            'contact_email' => ['required', 'email', 'max:190'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'office_address' => ['nullable', 'string', 'max:400'],
            'mailing_address' => ['nullable', 'string', 'max:400'],
            'socials.linkedin' => ['nullable', 'url', 'max:190'],
            'socials.twitter' => ['nullable', 'url', 'max:190'],
            'socials.facebook' => ['nullable', 'url', 'max:190'],
            'socials.instagram' => ['nullable', 'url', 'max:190'],
            'socials.youtube' => ['nullable', 'url', 'max:190'],
        ];
    }

    /**
     * Normalise missing social fields before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'socials' => array_merge(
                ['linkedin' => '', 'twitter' => '', 'facebook' => '', 'instagram' => '', 'youtube' => ''],
                is_array($this->input('socials')) ? $this->input('socials') : [],
            ),
        ]);
    }
}
