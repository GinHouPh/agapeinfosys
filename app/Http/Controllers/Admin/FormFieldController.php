<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FormFieldType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFormFieldRequest;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FormFieldController extends Controller
{
    /**
     * Atomically sync the full ordered field list from the visual form editor.
     */
    public function sync(Request $request, Form $form): JsonResponse
    {
        $validated = $request->validate([
            'fields' => ['present', 'array'],
            'fields.*.id' => ['nullable', 'integer'],
            'fields.*.label' => ['required', 'string', 'max:190'],
            'fields.*.name' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/'],
            'fields.*.type' => ['required', Rule::enum(FormFieldType::class)],
            'fields.*.group' => ['nullable', 'string', 'max:120'],
            'fields.*.section_id' => ['nullable', 'string', 'max:80'],
            'fields.*.options' => ['nullable', 'array'],
            'fields.*.options.*' => ['string', 'max:500'],
            'fields.*.has_text_input' => ['sometimes', 'boolean'],
            'fields.*.text_placeholder' => ['nullable', 'string', 'max:240'],
            'fields.*.placeholder' => ['nullable', 'string', 'max:190'],
            'fields.*.help_text' => ['nullable', 'string', 'max:240'],
            'fields.*.default_value' => ['nullable', 'string', 'max:500'],
            'fields.*.width' => ['nullable', Rule::in(['full', 'half', 'third'])],
            'fields.*.css_class' => ['nullable', 'string', 'max:120'],
            'fields.*.validation' => ['nullable', 'array'],
            'fields.*.validation.*' => ['string', 'max:200'],
            'fields.*.condition' => ['nullable', 'array'],
            'fields.*.condition_match' => ['nullable', Rule::in(['all', 'any'])],
            'fields.*.file_settings' => ['nullable', 'array'],
            'fields.*.is_required' => ['sometimes', 'boolean'],
        ]);

        $synced = DB::transaction(function () use ($form, $validated) {
            $incoming = $validated['fields'];
            $keepIds = [];
            $usedNames = [];

            foreach ($incoming as $row) {
                $id = isset($row['id']) ? (int) $row['id'] : null;
                if ($id !== null) {
                    $keepIds[] = $id;
                }
            }

            $form->fields()
                ->when($keepIds !== [], fn ($q) => $q->whereNotIn('id', $keepIds))
                ->delete();

            foreach ($incoming as $index => $row) {
                $type = FormFieldType::from($row['type']);
                $baseName = isset($row['name']) && $row['name'] !== ''
                    ? $row['name']
                    : FormField::nameFromLabel($row['label']);

                $name = $baseName;
                $suffix = 2;
                while (in_array($name, $usedNames, true)) {
                    $name = $baseName.'_'.$suffix++;
                }
                $usedNames[] = $name;

                $options = null;
                if ($type->hasOptions()) {
                    $options = array_values(array_filter(
                        array_map('trim', $row['options'] ?? []),
                        fn (string $v): bool => $v !== '',
                    ));
                    if ($options === []) {
                        $options = ['Option 1', 'Option 2'];
                    }
                }

                $payload = [
                    'label' => $row['label'],
                    'name' => $name,
                    'type' => $type,
                    'group' => ($row['group'] ?? '') !== '' ? $row['group'] : null,
                    'section_id' => ($row['section_id'] ?? '') !== '' ? $row['section_id'] : null,
                    'options' => $options,
                    'has_text_input' => (bool) ($row['has_text_input'] ?? false),
                    'text_placeholder' => ($row['text_placeholder'] ?? '') !== '' ? $row['text_placeholder'] : null,
                    'placeholder' => ($row['placeholder'] ?? '') !== '' ? $row['placeholder'] : null,
                    'help_text' => ($row['help_text'] ?? '') !== '' ? $row['help_text'] : null,
                    'default_value' => ($row['default_value'] ?? '') !== '' ? $row['default_value'] : null,
                    'width' => $row['width'] ?? 'full',
                    'css_class' => ($row['css_class'] ?? '') !== '' ? $row['css_class'] : null,
                    'validation' => array_values(array_filter($row['validation'] ?? [], fn ($r): bool => is_string($r) && trim($r) !== '')),
                    'condition' => is_array($row['condition'] ?? null) ? array_values($row['condition']) : [],
                    'condition_match' => $row['condition_match'] ?? 'all',
                    'file_settings' => $type->isFile() ? ($row['file_settings'] ?? ['mimes' => ['pdf', 'doc', 'docx'], 'max_kilobytes' => 5120]) : null,
                    'is_required' => $type->isLayoutOnly() ? false : (bool) ($row['is_required'] ?? false),
                    'sort_order' => $index,
                ];

                $existing = isset($row['id'])
                    ? $form->fields()->whereKey((int) $row['id'])->first()
                    : null;

                if ($existing !== null) {
                    $existing->update($payload);
                } else {
                    $form->fields()->create($payload);
                }
            }

            return $form->fields()->get();
        });

        return response()->json([
            'ok' => true,
            'fields' => $synced,
        ]);
    }

    /**
     * Add a question to a form; the sort order appends to the end.
     */
    public function store(StoreFormFieldRequest $request, Form $form): RedirectResponse
    {
        $max = $form->fields()->max('sort_order');
        $next = $max === null ? 0 : ((int) $max) + 1;

        $form->fields()->create($request->validated() + ['sort_order' => $next]);

        return redirect()
            ->route('admin.forms.edit', $form)
            ->with('status', 'Question added.');
    }

    /**
     * Update one question in place.
     */
    public function update(StoreFormFieldRequest $request, Form $form, FormField $field): RedirectResponse
    {
        abort_unless($field->form_id === $form->id, 404);

        $field->update($request->validated());

        return redirect()
            ->route('admin.forms.edit', $form)
            ->with('status', 'Question updated.');
    }

    /**
     * Remove a question from a form.
     */
    public function destroy(Form $form, FormField $field): RedirectResponse
    {
        abort_unless($field->form_id === $form->id, 404);

        $field->delete();

        return redirect()
            ->route('admin.forms.edit', $form)
            ->with('status', 'Question deleted.');
    }
}
