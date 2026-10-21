<?php

namespace App\Http\Controllers\Admin;

use App\Builder\SchemaSanitizer;
use App\Http\Controllers\Controller;
use App\Models\ComponentTemplate;
use App\Models\Concerns\RecordsActivity;
use App\Models\PageTemplate;
use App\Services\TemplateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    use RecordsActivity;

    /**
     * Every saved component and page template.
     */
    public function index(TemplateService $templates): View
    {
        return view('admin.templates.index', [
            'components' => $templates->componentCategories(),
            'pageTemplates' => $templates->pageTemplates(),
        ]);
    }

    /**
     * The components the builder palette offers, as JSON.
     */
    public function components(TemplateService $templates): JsonResponse
    {
        $components = $templates->componentCategories()
            ->flatMap(fn ($group): array => $group->all())
            ->map(fn (ComponentTemplate $template): array => [
                'id' => $template->id,
                'name' => $template->name,
                'category' => $template->category ?: 'General',
                'description' => $template->description,
                'icon' => $template->icon,
                'slots' => $template->slotDefinitions(),
            ])
            ->values();

        return response()->json(['components' => $components]);
    }

    /**
     * The blocks a component inserts, with its slots filled in.
     */
    public function insert(Request $request, ComponentTemplate $component, TemplateService $templates): JsonResponse
    {
        $slots = $request->input('slots', []);

        if (! is_array($slots)) {
            $slots = [];
        }

        return response()->json([
            'blocks' => $templates->insertComponent($component, $slots),
        ]);
    }

    /**
     * Save a section from the builder canvas as a reusable component.
     */
    public function storeComponent(Request $request, SchemaSanitizer $sanitizer): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'category' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:300'],
            'icon' => ['nullable', 'string', 'max:40'],
            'schema' => ['required', 'array'],
            'schema.blocks' => ['required', 'array'],
            'slots' => ['nullable', 'array'],
        ]);

        $component = ComponentTemplate::query()->create([
            'name' => $attributes['name'],
            'category' => $attributes['category'] ?? null,
            'description' => $attributes['description'] ?? null,
            'icon' => $attributes['icon'] ?? null,
            // The same whitelist the page save uses — a template is a schema
            // like any other and must not carry settings the builder would
            // never have written.
            'schema' => $sanitizer->sanitize($attributes['schema']),
            'slots' => $attributes['slots'] ?? [],
            'created_by' => $request->user()?->getKey(),
        ]);

        $this->recordActivity('created', $component, 'Saved the component "'.$component->name.'".');

        return redirect()
            ->route('admin.templates.index')
            ->with('status', 'Component "'.$component->name.'" saved.');
    }

    /**
     * Delete a component.
     */
    public function destroyComponent(ComponentTemplate $component): RedirectResponse
    {
        $name = $component->name;

        $component->delete();

        $this->recordActivity('deleted', null, 'Deleted the component "'.$name.'".');

        return redirect()
            ->route('admin.templates.index')
            ->with('status', 'Component deleted.');
    }

    /**
     * Save the current page layout as a starter template.
     */
    public function storePageTemplate(Request $request, SchemaSanitizer $sanitizer): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:300'],
            'schema' => ['required', 'array'],
            'schema.blocks' => ['required', 'array'],
        ]);

        $template = PageTemplate::query()->create([
            'name' => $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'schema' => $sanitizer->sanitize($attributes['schema']),
            'created_by' => $request->user()?->getKey(),
        ]);

        $this->recordActivity('created', $template, 'Saved the page template "'.$template->name.'".');

        return redirect()
            ->route('admin.templates.index')
            ->with('status', 'Page template "'.$template->name.'" saved.');
    }

    /**
     * Delete a page template.
     */
    public function destroyPageTemplate(PageTemplate $template): RedirectResponse
    {
        $name = $template->name;

        $template->delete();

        $this->recordActivity('deleted', null, 'Deleted the page template "'.$name.'".');

        return redirect()
            ->route('admin.templates.index')
            ->with('status', 'Page template deleted.');
    }
}
