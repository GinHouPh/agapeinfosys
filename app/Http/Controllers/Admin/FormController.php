<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFormRequest;
use App\Models\Form;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

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
     * Form meta editor plus the question builder.
     */
    public function edit(Form $form): View
    {
        $form->load('fields');

        return view('admin.forms.edit', compact('form'));
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
