<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FormFieldType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFormRequest;
use App\Models\Form;
use App\Services\FormTemplates;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FormController extends Controller
{
    /**
     * List every builder-managed form.
     */
    public function index(): View
    {
        $forms = Form::query()
            ->withCount(['fields', 'submissions'])
            ->orderBy('title')
            ->paginate(15);

        return view('admin.forms.index', compact('forms'));
    }

    /**
     * Create form: blank meta + no questions yet.
     */
    public function create(): View
    {
        return view('admin.forms.create');
    }

    /**
     * Persist a new form.
     */
    public function store(StoreFormRequest $request): RedirectResponse
    {
        $form = Form::query()->create($request->validated());

        return redirect()
            ->route('admin.forms.edit', $form)
            ->with('status', 'Form created — add the questions below.');
    }

    /**
     * Form meta editor plus the visual question builder.
     */
    public function edit(Form $form): View
    {
        $form->load('fields')->loadCount('submissions');

        $templates = FormTemplates::all();
        $fieldTypes = array_map(fn (FormFieldType $type): array => [
            'value' => $type->value,
            'label' => $type->label(),
            'category' => $type->category(),
            'hasOptions' => $type->hasOptions(),
            'isFile' => $type->isFile(),
            'isLayoutOnly' => $type->isLayoutOnly(),
            'isConsent' => $type->isConsent(),
        ], FormFieldType::cases());

        return view('admin.forms.edit', compact('form', 'templates', 'fieldTypes'));
    }

    /**
     * Duplicate a form and all its fields as a draft.
     */
    public function duplicate(Form $form): RedirectResponse
    {
        $copy = DB::transaction(function () use ($form): Form {
            $baseSlug = Str::slug($form->slug.'-copy');
            $slug = $baseSlug;
            $suffix = 2;

            while (Form::query()->where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$suffix++;
            }

            $copy = $form->replicate(['slug', 'is_active']);
            $copy->title = $form->title.' (Copy)';
            $copy->slug = $slug;
            $copy->is_active = false;
            $copy->save();

            foreach ($form->fields as $field) {
                $fieldCopy = $field->replicate(['form_id']);
                $fieldCopy->form_id = $copy->id;
                $fieldCopy->save();
            }

            return $copy;
        });

        return redirect()
            ->route('admin.forms.edit', $copy)
            ->with('status', 'Form duplicated as a draft.');
    }

    /**
     * Update the form's meta.
     */
    public function update(StoreFormRequest $request, Form $form): RedirectResponse
    {
        $form->update($request->validated());

        return redirect()
            ->route('admin.forms.edit', $form)
            ->with('status', 'Form updated.');
    }

    /**
     * Delete a form together with its questions and submissions.
     */
    public function destroy(Form $form): RedirectResponse
    {
        $form->delete();

        return redirect()
            ->route('admin.forms.index')
            ->with('status', 'Form and its submissions deleted.');
    }
}
