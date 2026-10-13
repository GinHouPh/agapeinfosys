<?php

namespace App\Http\Controllers;

use App\Enums\FormFieldType;
use App\Http\Controllers\Concerns\RendersBuilderPages;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationRule;
use Illuminate\View\View;

class FormController extends Controller
{
    use RendersBuilderPages;

    /**
     * The slug of the requirement specification form behind /requirements.
     */
    public const REQUIREMENT_FORM_SLUG = 'requirement-specification';

    /**
     * Render any active form by its public slug.
     */
    public function show(Request $request, Form $form): View
    {
        $this->ensureVisible($form);

        $form->load('fields');

        if ($builder = $this->builderPage('form-detail', ['record' => $form], [
            'pageTitle' => $form->title.' | Agape Infosys',
            'pageDescription' => Str::limit(strip_tags($form->description ?? ''), 155, '…'),
        ])) {
            return $builder;
        }

        return view('forms.show', [
            'form' => $form,
            'groups' => FormField::groupForRenderer($form->fields),
        ]);
    }

    /**
     * Alias endpoint: store a requirement submission.
     */
    public function storeRequirement(Request $request): RedirectResponse
    {
        $form = Form::query()
            ->active()
            ->where('slug', self::REQUIREMENT_FORM_SLUG)
            ->firstOrFail();

        return $this->storeSubmission($request, $form, redirect()->route('requirements.create'));
    }

    /**
     * Validate answers against the form's field definitions and record them.
     */
    public function store(Request $request, Form $form): RedirectResponse
    {
        $this->ensureVisible($form);

        return $this->storeSubmission($request, $form, redirect()->route('forms.show', $form));
    }

    /**
     * @param  RedirectResponse  $back  where to land on success
     */
    private function storeSubmission(Request $request, Form $form, RedirectResponse $back): RedirectResponse
    {
        $validated = $request->validate(
            $this->buildValidationRules($form),
            [],
            $this->buildAttributeNames($form),
        );

        $submission = FormSubmission::query()->create([
            'form_id' => $form->id,
            'answers' => array_map(
                fn ($value) => is_array($value) ? array_values($value) : $value,
                $validated['answers'] ?? [],
            ),
        ]);

        return $back
            ->with('status', $form->successMessageFor($submission))
            ->with('reference', $submission->reference);
    }

    /**
     * Inactive forms are hidden from the public (admins excepted).
     */
    private function ensureVisible(Form $form): void
    {
        if (! $form->is_active && ! Auth::user()?->isAdmin()) {
            abort(404);
        }
    }

    /**
     * Alias entry point: the requirement form at /requirements.
     */
    public function showRequirement(): View
    {
        $form = Form::query()
            ->active()
            ->where('slug', self::REQUIREMENT_FORM_SLUG)
            ->with('fields')
            ->firstOrFail();

        if ($builder = $this->builderPage('requirements')) {
            return $builder;
        }

        return view('forms.show', [
            'form' => $form,
            'groups' => FormField::groupForRenderer($form->fields),
        ]);
    }

    /**
     * Build validation rules from the form's field definitions.
     *
     * @return array<string, ValidationRule|array<int, mixed>|string>
     */
    private function buildValidationRules(Form $form): array
    {
        $rules = [];

        foreach ($form->fields as $field) {
            $key = "answers.{$field->name}";
            $base = $field->is_required ? 'required' : 'nullable';

            if ($field->type->isMultivalue()) {
                $rules[$key] = [$base, 'array'];
                $rules["{$key}.*"] = [Rule::in($field->options ?? [])];

                continue;
            }

            $rules[$key] = array_filter([
                $base,
                match ($field->type) {
                    FormFieldType::Text => 'string',
                    FormFieldType::Email => 'email',
                    FormFieldType::Phone => 'string',
                    FormFieldType::Number => 'numeric',
                    FormFieldType::Textarea => 'string',
                    FormFieldType::Select,
                    FormFieldType::Radio => Rule::in($field->options ?? []),
                    FormFieldType::Date => 'date',
                },
                'max:'.($field->type === FormFieldType::Textarea ? 5000 : 500),
            ]);
        }

        return $rules;
    }

    /**
     * Friendly validation labels: "Full Name" instead of "answers.name".
     *
     * @return array<string, string>
     */
    private function buildAttributeNames(Form $form): array
    {
        $names = [];

        foreach ($form->fields as $field) {
            $names["answers.{$field->name}"] = $field->label;

            if ($field->type->isMultivalue()) {
                $names["answers.{$field->name}.*"] = $field->label;
            }
        }

        return $names;
    }
}
