<?php

namespace App\Http\Controllers\Admin;

use App\Builder\PageRenderer;
use App\Builder\SchemaSanitizer;
use App\Builder\WidgetRegistry;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageRequest;
use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class PageController extends Controller
{
    /**
     * Paginated list of builder-managed pages.
     */
    public function index(): View
    {
        $pages = Page::query()
            ->orderByDesc('updated_at')
            ->paginate(15);

        return view('admin.pages.index', compact('pages'));
    }

    /**
     * Creation form: title + slug + status only; the canvas comes next.
     */
    public function create(): View
    {
        return view('admin.pages.create');
    }

    /**
     * Persist a new page and land on its canvas.
     */
    public function store(StorePageRequest $request): RedirectResponse
    {
        $page = Page::query()->create($request->validated());

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'Page created — add your sections in the builder.');
    }

    /**
     * The visual builder canvas for one page.
     */
    public function edit(Page $page, WidgetRegistry $registry, PageRenderer $renderer): View
    {
        $initial = [];

        foreach ($page->blocks() as $block) {
            $initial[] = [
                'id' => $block['id'],
                'widget' => $block['widget'],
                'settings' => $block['settings'],
                'html' => $renderer->renderBlock($block['widget'], $block['settings']),
            ];
        }

        return view('admin.pages.edit', [
            'page' => $page,
            'widgets' => $registry->manifest(),
            'categories' => array_keys($registry->grouped()),
            'initialBlocks' => $initial,
        ]);
    }

    /**
     * Save page meta and/or the full block schema.
     *
     * The builder posts the whole schema as JSON; a plain meta form posts
     * without it, in which case the existing schema is preserved.
     */
    public function update(StorePageRequest $request, Page $page, SchemaSanitizer $sanitizer): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

        if ($request->filled('schema')) {
            $data['schema'] = $sanitizer->sanitize($request->input('schema'));
        } else {
            unset($data['schema']);
        }

        $page->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'saved',
                'slug' => $page->slug,
                'isPublished' => $page->status->isPublished(),
            ]);
        }

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'Page saved.');
    }

    /**
     * Delete a page.
     */
    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()
            ->route('admin.pages.index')
            ->with('status', 'Page deleted.');
    }
}
