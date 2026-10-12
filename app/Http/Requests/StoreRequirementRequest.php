<?php

namespace App\Http\Requests;

use App\Models\RequirementSubmission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequirementRequest extends FormRequest
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
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:120'],
            'industry' => ['required', Rule::in(RequirementSubmission::INDUSTRY_OPTIONS)],
            'project_name' => ['required', 'string', 'max:160'],
            'brief' => ['required', 'string', 'min:20', 'max:4000'],
            'solutions' => ['required', 'array', 'min:1'],
            'solutions.*' => [Rule::in(RequirementSubmission::SOLUTION_OPTIONS)],
            'features' => ['required', 'string', 'min:20', 'max:4000'],
            'integrations' => ['nullable', 'array'],
            'integrations.*' => [Rule::in(RequirementSubmission::INTEGRATION_OPTIONS)],
            'user_scale' => ['required', Rule::in(RequirementSubmission::USER_SCALE_OPTIONS)],
            'budget' => ['required', Rule::in(RequirementSubmission::BUDGET_OPTIONS)],
            'timeline' => ['required', Rule::in(RequirementSubmission::TIMELINE_OPTIONS)],
            'notes' => ['nullable', 'string', 'max:4000'],
        ];
    }
}
