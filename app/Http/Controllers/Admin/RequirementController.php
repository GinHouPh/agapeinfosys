<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RequirementStatus;
use App\Http\Controllers\Controller;
use App\Models\RequirementSubmission;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RequirementController extends Controller
{
    /**
     * Paginated requirement inbox with search and status filter.
     */
    public function index(Request $request): View
    {
        $search = (string) $request->query('q', '');
        $status = RequirementStatus::tryFrom((string) $request->query('status', ''));

        $submissions = RequirementSubmission::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('reference', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('project_name', 'like', "%{$search}%");
                });
            })
            ->when($status !== null, fn ($query) => $query->where('status', $status->value))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.requirements.index', compact('submissions', 'search', 'status'));
    }

    /**
     * Full detail of one requirement brief.
     */
    public function show(RequirementSubmission $requirement): View
    {
        return view('admin.requirements.show', compact('requirement'));
    }

    /**
     * Move a submission along the pipeline.
     */
    public function status(Request $request, RequirementSubmission $requirement): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(RequirementStatus::class)],
        ]);

        $requirement->update($validated);

        return redirect()
            ->route('admin.requirements.show', $requirement)
            ->with('status', 'Requirement status updated.');
    }

    /**
     * Remove a submission from the inbox.
     */
    public function destroy(RequirementSubmission $requirement): RedirectResponse
    {
        $requirement->delete();

        return redirect()
            ->route('admin.requirements.index')
            ->with('status', 'Requirement deleted.');
    }
}
