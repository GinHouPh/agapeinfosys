<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Models\ShowcaseProject;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShowcaseController extends Controller
{
    /**
     * The poster wall of ongoing and upcoming projects, with filters.
     */
    public function index(Request $request): View
    {
        $activeStatus = ProjectStatus::tryFrom((string) $request->query('status', ''));
        $activeIndustry = (string) $request->query('industry', '');

        $industries = ShowcaseProject::industries();

        if (! in_array($activeIndustry, $industries, true)) {
            $activeIndustry = '';
        }

        $projects = ShowcaseProject::query()
            ->active()
            ->when($activeStatus !== null, fn ($query) => $query->where('status', $activeStatus->value))
            ->when($activeIndustry !== '', fn ($query) => $query->where('industry', $activeIndustry))
            ->ordered()
            ->get();

        return view('projects.index', compact('projects', 'industries', 'activeStatus', 'activeIndustry'));
    }
}
