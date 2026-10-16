<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CategoryController extends Controller
{
    /**
     * The category tree in depth order, or a flat list of search matches.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $rows = $search !== ''
            ? $this->searchRows($search)
            : Category::flattenedTree(Category::query()->withCount('products')->with('parent'));

        return view('admin.categories.index', compact('rows', 'search'));
    }

    /**
     * Creation form.
     */
    public function create(): View
    {
        return view('admin.categories.create', ['categories' => Category::selectOptions()]);
    }

    /**
     * Persist a new category.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Category created.');
    }

    /**
     * Edit form.
     */
    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'categories' => Category::selectOptions($category),
        ]);
    }

    /**
     * Apply edits to an existing category.
     */
    public function update(StoreCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Category updated.');
    }

    /**
     * Remove a category, refusing when it still holds subcategories or products.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->children()->exists() || $category->products()->exists()) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', 'Move or delete this category\'s subcategories and products first.');
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Category deleted.');
    }

    /**
     * Flat list of categories matching a name or slug search.
     *
     * @return Collection<int, array{category: Category, depth: int}>
     */
    private function searchRows(string $search): Collection
    {
        return Category::query()
            ->withCount('products')
            ->with('parent')
            ->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%"))
            ->ordered()
            ->get()
            ->map(fn (Category $category): array => ['category' => $category, 'depth' => 0]);
    }
}
