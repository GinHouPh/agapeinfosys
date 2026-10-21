<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SubmissionStatus;
use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormSubmissionController extends Controller
{
    /**
     * Paginated inbox across every form, with search and filters.
     */
    public function index(Request $request): View
    {
        $search = (string) $request->query('q', '');
        $status = SubmissionStatus::tryFrom((string) $request->query('status', ''));
        $formId = (int) $request->query('form', 0);

        $submissions = FormSubmission::query()
            ->with('form')
            ->when($search !== '', fn ($query) => $query->where('reference', 'like', "%{$search}%")
                ->orWhere('answers', 'like', "%{$search}%"))
            ->when($status !== null, fn ($query) => $query->where('status', $status->value))
            ->when($formId > 0, fn ($query) => $query->where('form_id', $formId))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $forms = Form::query()->orderBy('title')->get(['id', 'title']);

        return view('admin.submissions.index', compact('submissions', 'search', 'status', 'forms', 'formId'));
    }

    /**
     * One submission rendered against its form's question schema.
     */
    public function show(FormSubmission $submission): View
    {
        $submission->load(['form.fields']);

        return view('admin.submissions.show', compact('submission'));
    }

    /**
     * Move a submission along the pipeline.
     */
    public function status(Request $request, FormSubmission $submission): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(SubmissionStatus::class)],
        ]);

        $submission->update($validated);

        return redirect()
            ->route('admin.submissions.show', $submission)
            ->with('status', 'Submission status updated.');
    }

    /**
     * Remove a submission from the inbox.
     */
    public function destroy(FormSubmission $submission): RedirectResponse
    {
        $submission->delete();

        return redirect()
            ->route('admin.submissions.index')
            ->with('status', 'Submission deleted.');
    }

    /**
     * Download the submissions of one form as a CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $formId = (int) $request->query('form', 0);

        $form = Form::query()->with('fields')->findOrFail($formId);

        $submissions = FormSubmission::query()
            ->where('form_id', $form->id)
            ->oldest('id')
            ->get();

        $columns = $form->fields->map(fn (FormField $field): string => $field->label)->all();

        $filename = Str::slug($form->title).'-submissions.csv';

        return response()->streamDownload(function () use ($submissions, $columns, $form): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Reference', 'Status', 'Received', ...$columns]);

            foreach ($submissions as $submission) {
                $row = [
                    $submission->reference,
                    $submission->status->value,
                    $submission->created_at?->toDateTimeString(),
                ];

                foreach ($form->fields as $field) {
                    $row[] = $submission->answerFor($field);
                }

                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
