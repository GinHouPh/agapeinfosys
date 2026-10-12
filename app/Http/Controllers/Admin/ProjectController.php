<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesImageUploads;
use App\Http\Controllers\Concerns\SplitsListInput;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use HandlesImageUploads, SplitsListInput;

    /**
     * Paginated case study list.
     */
    public function index(Request $request): View
    {
        $search = (string) $request->query('q', '');

        $projects = Project::query()
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return view('admin.projects.index', compact('projects', 'search'));
    }

    /**
     * Creation form.
     */
    public function create(): View
    {
        return view('admin.projects.create');
    }

    /**
     * Persist a new case study.
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $attributes = $this->attributesFrom($request);
        $attributes['cover_image'] = $this->storeUploadedImage($request->file('cover_image'), 'uploads/projects');

        Project::create($attributes);

        return redirect()
            ->route('admin.projects.index')
            ->with('status', 'Case study created.');
    }

    /**
     * Edit form.
     */
    public function edit(Project $project): View
    {
        return view('admin.projects.edit', compact('project'));
    }

    /**
     * Apply edits to an existing case study.
     */
    public function update(StoreProjectRequest $request, Project $project): RedirectResponse
    {
        $attributes = $this->attributesFrom($request);
        $attributes['cover_image'] = $this->replaceUploadedImage(
            $request->file('cover_image'),
            $project->cover_image,
            'uploads/projects',
        );

        $project->update($attributes);

        return redirect()
            ->route('admin.projects.index')
            ->with('status', 'Case study updated.');
    }

    /**
     * Remove a case study and its media.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $this->forgetUploadedImage($project->cover_image);
        $project->delete();

        return redirect()
            ->route('admin.projects.index')
            ->with('status', 'Case study deleted.');
    }

    /**
     * Shape validated input into the attribute shape the model expects.
     *
     * @return array<string, mixed>
     */
    private function attributesFrom(StoreProjectRequest $request): array
    {
        $attributes = $request->validated();
        $attributes['stack'] = $this->splitList($request->input('stack'));
        $attributes['outcomes'] = $this->splitList($request->input('outcomes'));

        return $attributes;
    }
}
