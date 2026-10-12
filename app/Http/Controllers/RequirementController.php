<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequirementRequest;
use App\Models\RequirementSubmission;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class RequirementController extends Controller
{
    /**
     * The requirement specification form used to prepare a quotation.
     */
    public function create(): View
    {
        return view('requirements.create', [
            'industries' => RequirementSubmission::INDUSTRY_OPTIONS,
            'solutions' => RequirementSubmission::SOLUTION_OPTIONS,
            'integrations' => RequirementSubmission::INTEGRATION_OPTIONS,
            'scales' => RequirementSubmission::USER_SCALE_OPTIONS,
            'budgets' => RequirementSubmission::BUDGET_OPTIONS,
            'timelines' => RequirementSubmission::TIMELINE_OPTIONS,
        ]);
    }

    /**
     * Persist a requirement brief and hand the client a reference.
     */
    public function store(StoreRequirementRequest $request): RedirectResponse
    {
        $submission = RequirementSubmission::create($request->validated());

        return redirect()
            ->route('requirements.create')
            ->with('status', "Thank you, {$submission->name} — your requirement brief ({$submission->reference}) is with us. Our team will revert with a quotation shortly.");
    }
}
