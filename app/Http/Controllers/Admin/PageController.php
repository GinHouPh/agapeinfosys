<?php

namespace App\Http\Controllers\Admin;

use App\Builder\PageRenderer;
use App\Builder\RecordResolver;
use App\Builder\SchemaSanitizer;
use App\Builder\WidgetRegistry;
use App\Enums\PageStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageRequest;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\PageTemplate;
use App\Services\PageDiffService;
use App\Services\TemplateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Paginated list of builder-managed pages.
     */
    public function index(): View
    {
        $pages = Page::query()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(15);

        return view('admin.pages.index', compact('pages'));
    }

    /**
     * Creation form: title + slug + status only; the canvas comes next.
     */
    public function create(TemplateService $templates): View
    {
        return view('admin.pages.create', [
            'pageTemplates' => $templates->pageTemplates(),
        ]);
    }

    /**
     * Persist a new page and land on its canvas.
     *
     * A page template seeds the schema so a new page starts from a layout
     * rather than an empty canvas.
     */
    public function store(StorePageRequest $request, TemplateService $templates): RedirectResponse
    {
        $page = Page::query()->create($request->validated());

        $template = $request->integer('template') > 0
            ? PageTemplate::query()->find($request->integer('template'))
            : null;

        if ($template !== null) {
            $page->update([
                'schema' => ['blocks' => $templates->seedPage($template)],
            ]);
        }

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'Page created — add your sections in the builder.');
    }

    /**
     * The visual builder canvas for one page.
     *
     * Record templates preview against a live record of their type so the
     * canvas shows real content rather than a placeholder.
     */
    public function edit(Page $page, WidgetRegistry $registry, PageRenderer $renderer, RecordResolver $resolver): View
    {
        $context = [
            'record' => $resolver->forPage($page),
            'authoring' => true,
        ];

        $initial = $this->initialBlocks($page->blocks(), $context, $renderer);

        return view('admin.pages.edit', [
            'page' => $page,
            'widgets' => $registry->manifest(),
            'categories' => array_keys($registry->grouped()),
            'initialBlocks' => $initial,
            'template' => $page->template(),
            'maxDepth' => SchemaSanitizer::MAX_DEPTH,
        ]);
    }

    /**
     * Build the editor's block tree: every node carries its rendered HTML.
     *
     * Containers render with empty column slots — the canvas fills them from
     * this same tree, so what the editor draws and what it saves never drift.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<string, mixed>  $context
     * @return array<int, array<string, mixed>>
     */
    private function initialBlocks(array $blocks, array $context, PageRenderer $renderer): array
    {
        $tree = [];

        foreach ($blocks as $block) {
            $node = [
                'id' => $block['id'],
                'widget' => $block['widget'],
                'settings' => $block['settings'],
                'html' => $renderer->renderBlock($block['widget'], $block['settings'], $context),
            ];

            if (isset($block['column'])) {
                $node['column'] = (int) $block['column'];
            }

            if (is_array($block['children'] ?? null)) {
                $node['children'] = $this->initialBlocks($block['children'], $context, $renderer);
            }

            $tree[] = $node;
        }

        return $tree;
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

    /**
     * Copy a page, its layout included, so it can be edited on its own.
     */
    public function duplicate(Page $page): RedirectResponse
    {
        $copy = Page::query()->create([
            'parent_id' => $page->parent_id,
            'title' => $page->title.' (copy)',
            'slug' => $page->slug.'-copy',
            'status' => PageStatus::Draft,
            'sort_order' => $page->sort_order,
            'meta_description' => $page->meta_description,
            'schema' => $page->schema,
        ]);

        return redirect()
            ->route('admin.pages.edit', $copy)
            ->with('status', 'Page duplicated.');
    }

    /**
     * The saved versions of this page's layout.
     */
    public function revisions(Page $page): View
    {
        $revisions = $page->revisions()->with('creator')->limit(30)->get();

        return view('admin.pages.revisions', [
            'page' => $page,
            'revisions' => $revisions,
        ]);
    }

    /**
     * What changed between the page now and one of its saved versions.
     */
    public function diff(Page $page, PageRevision $revision, PageDiffService $diff): View
    {
        return view('admin.pages.diff', [
            'page' => $page,
            'revision' => $revision,
            'changes' => $diff->compare($revision->schema, $page->schema),
        ]);
    }

    /**
     * Put a saved version back.
     */
    public function restore(Page $page, PageRevision $revision): RedirectResponse
    {
        $page->restore($revision);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'The saved version has been restored.');
    }

    /**
     * Set the order the pages are listed in.
     */
    public function reorder(Request $request): RedirectResponse
    {
        $order = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:pages,id'],
        ])['order'];

        foreach (array_values($order) as $index => $id) {
            Page::query()->whereKey($id)->update(['sort_order' => $index]);
        }

        return back()->with('status', 'Page order saved.');
    }
}
