<?php

namespace App\Http\Controllers;

use App\Enums\FormFieldType;
use App\Http\Controllers\Concerns\RendersBuilderPages;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Services\FormConditionService;
use App\Services\FormSubmissionNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
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
     * The name of the hidden field a bot fills in and a person does not.
     */
    public const HONEYPOT = 'hp';

    public function __construct(
        private readonly FormConditionService $conditions,
        private readonly FormSubmissionNotifier $notifier,
    ) {}

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
            'honeypot' => self::HONEYPOT,
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
        $this->guardThrottle($request, $form);

        // A filled honeypot means a bot. It is recorded as spam and told
        // everything went well, so it learns nothing.
        if ($this->isSpam($request)) {
            $submission = FormSubmission::query()->create([
                'form_id' => $form->id,
                'answers' => [],
                'meta' => $this->submissionMeta($request),
                'page_url' => $request->fullUrl(),
                'is_spam' => true,
            ]);

            return $back
                ->with('status', $form->successMessageFor($submission))
                ->with('reference', $submission->reference);
        }

        $form->loadMissing('fields');

        $visible = $this->conditions->visibleFields(
            $form->fields,
            $request->input('answers', []),
        );

        $validated = $request->validate(
            $this->buildValidationRules($visible),
            [],
            $this->buildAttributeNames($visible),
        );

        $answers = $this->storeUploads($request, $form, $visible);

        foreach (($validated['answers'] ?? []) as $key => $value) {
            if (array_key_exists($key, $answers)) {
                continue;
            }

            $answers[$key] = is_array($value) ? array_values($value) : $value;
        }

        $submission = FormSubmission::query()->create([
            'form_id' => $form->id,
            'answers' => $answers,
            'meta' => $this->submissionMeta($request),
            'page_url' => $request->fullUrl(),
            'is_spam' => false,
        ]);

        $this->notifier->notify($form, $submission);

        return $back
            ->with('status', $form->successMessageFor($submission))
            ->with('reference', $submission->reference);
    }

    /**
     * Move the uploaded files into storage and return their paths.
     *
     * @param  array<string, FormField>  $visible
     * @return array<string, string>
     */
    private function storeUploads(Request $request, Form $form, array $visible): array
    {
        $paths = [];

        foreach ($visible as $field) {
            if (! $field->type->isFile() || ! $request->hasFile('answers.'.$field->name)) {
                continue;
            }

            $file = $request->file('answers.'.$field->name);

            if ($file === null || ! $file->isValid()) {
                continue;
            }

            $paths[$field->name] = $file->store('uploads/forms/'.$form->slug, 'public');
        }

        return $paths;
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
            'honeypot' => self::HONEYPOT,
        ]);
    }

    /**
     * Build validation rules from the form's field definitions.
     *
     * @param  array<string, FormField>  $visible
     * @return array<string, ValidationRule|array<int, mixed>|string>
     */
    private function buildValidationRules(array $visible): array
    {
        $rules = [];

        foreach ($visible as $field) {
            $key = "answers.{$field->name}";
            $base = $field->is_required ? 'required' : 'nullable';

            if ($field->type->isFile()) {
                $rules[$key] = array_filter([
                    $base,
                    'file',
                    'max:'.$field->maxUploadKilobytes(),
                    ($mimes = $field->acceptedMimes()) !== [] ? 'mimes:'.implode(',', $mimes) : null,
                ]);

                continue;
            }

            if ($field->type->isMultivalue()) {
                $rules[$key] = [$base, 'array'];
                $rules["{$key}.*"] = [Rule::in($field->options ?? [])];

                continue;
            }

            $rules[$key] = array_filter(array_merge([
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
                    FormFieldType::File => 'string',
                },
                'max:'.($field->type === FormFieldType::Textarea ? 5000 : 500),
            ], $field->validationRules()));
        }

        return $rules;
    }

    /**
     * Friendly validation labels: "Full Name" instead of "answers.name".
     *
     * @param  array<string, FormField>  $visible
     * @return array<string, string>
     */
    private function buildAttributeNames(array $visible): array
    {
        $names = [];

        foreach ($visible as $field) {
            $names["answers.{$field->name}"] = $field->label;

            if ($field->type->isMultivalue()) {
                $names["answers.{$field->name}.*"] = $field->label;
            }
        }

        return $names;
    }

    /**
     * Whether the submission looks like it came from a bot.
     */
    private function isSpam(Request $request): bool
    {
        return trim((string) $request->input(self::HONEYPOT, '')) !== '';
    }

    /**
     * Where the submission came from.
     *
     * @return array<string, mixed>
     */
    private function submissionMeta(Request $request): array
    {
        return [
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 400, ''),
            'referer' => Str::limit((string) $request->header('referer'), 400, ''),
        ];
    }

    /**
     * Hold the form down to its own submission rate.
     */
    private function guardThrottle(Request $request, Form $form): void
    {
        $limit = max(1, (int) $form->throttle_per_minute);
        $key = 'form:'.$form->id.':'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            abort(429, 'Too many submissions. Please try again shortly.');
        }

        RateLimiter::hit($key, 60);
    }
}
