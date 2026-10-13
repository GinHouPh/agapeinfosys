<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersBuilderPages;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use RendersBuilderPages;

    /**
     * Case study grid, optionally narrowed to one discipline.
     */
    public function index(Request $request): View
    {
        if ($builder = $this->builderPage('portfolio')) {
            return $builder;
        }

        $category = $request->query('category');
        $categories = Project::query()
            ->published()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $query = Project::query()->published()->ordered();

        if (is_string($category) && $category !== '') {
            $query->where('category', $category);
        }

        return view('portfolio.index', [
            'projects' => $query->get(),
            'categories' => $categories,
            'activeCategory' => is_string($category) && $category !== '' ? $category : null,
        ]);
    }

    /**
     * Detail page for a single case study.
     */
    public function show(Project $project): View
    {
        if ($builder = $this->builderPage('case-study-detail', ['record' => $project], [
            'pageTitle' => $project->title.' | Agape Infosys Case Study',
            'pageDescription' => $project->excerpt ?: $project->title.' — a case study by Agape Infosys.',
        ])) {
            return $builder;
        }

        return view('portfolio.show', [
            'project' => $project,
            'others' => $project->related(3),
        ]);
    }
}
