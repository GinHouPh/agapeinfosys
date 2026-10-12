<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShowcaseProjectRequest;
use App\Models\ShowcaseProject;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShowcaseProjectController extends Controller
{
    use HandlesImageUploads;

    /**
     * Paginated showcase project list with search and status filter.
     */
    public function index(Request $request): View
    {
        $search = (string) $request->query('q', '');
        $status = ProjectStatus::tryFrom((string) $request->query('status', ''));

        $projects = ShowcaseProject::query()
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when($status !== null, fn ($query) => $query->where('status', $status->value))
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return view('admin.showcase-projects.index', compact('projects', 'search', 'status'));
    }

    /**
     * Creation form.
     */
    public function create(): View
    {
        return view('admin.showcase-projects.create');
    }

    /**
     * Persist a new showcase project.
     */
    public function store(StoreShowcaseProjectRequest $request): RedirectResponse
    {
        $attributes = $request->validated();

        $attributes['poster'] = $this->storeUploadedImage($request->file('poster'), 'uploads/showcase');
        $attributes['stack'] = $this->parseStack($request->input('stack'));

        ShowcaseProject::create($attributes);

        return redirect()
            ->route('admin.showcase-projects.index')
            ->with('status', 'Project created.');
    }

    /**
     * Edit form.
     */
    public function edit(ShowcaseProject $showcase_project): View
    {
        return view('admin.showcase-projects.edit', ['project' => $showcase_project]);
    }

    /**
     * Apply edits to an existing showcase project.
     */
    public function update(StoreShowcaseProjectRequest $request, ShowcaseProject $showcase_project): RedirectResponse
    {
        $attributes = $request->validated();

        $attributes['poster'] = $this->replaceUploadedImage(
            $request->file('poster'),
            $showcase_project->poster,
            'uploads/showcase'
        );
        $attributes['stack'] = $this->parseStack($request->input('stack'));

        $showcase_project->update($attributes);

        return redirect()
            ->route('admin.showcase-projects.index')
            ->with('status', 'Project updated.');
    }

    /**
     * Remove a showcase project and its poster.
     */
    public function destroy(ShowcaseProject $showcase_project): RedirectResponse
    {
        $this->forgetUploadedImage($showcase_project->poster);
        $showcase_project->delete();

        return redirect()
            ->route('admin.showcase-projects.index')
            ->with('status', 'Project deleted.');
    }

    /**
     * Split the comma-separated stack input into a clean list.
     *
     * @return array<int, string>
     */
    private function parseStack(?string $stack): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $stack))));
    }
}
