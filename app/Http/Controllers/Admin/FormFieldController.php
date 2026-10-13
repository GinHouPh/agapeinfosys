<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFormFieldRequest;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\RedirectResponse;

class FormFieldController extends Controller
{
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
