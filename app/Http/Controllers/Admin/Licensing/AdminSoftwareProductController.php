<?php

namespace App\Http\Controllers\Admin\Licensing;

use App\Http\Controllers\Controller;
use App\Models\SoftwareProduct;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminSoftwareProductController extends Controller
{
    /**
     * Display a listing of registered software products.
     */
    public function index(): View
    {
        $products = SoftwareProduct::query()
            ->withCount(['licenses', 'releases'])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.licensing.products.index', [
            'products' => $products,
        ]);
    }

    /**
     * Show the form for registering a new software product.
     */
    public function create(): View
    {
        return view('admin.licensing.products.create', [
            'product' => new SoftwareProduct(['is_active' => true]),
        ]);
    }

    /**
     * Store a newly created software product.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:80', 'alpha_dash', 'unique:software_products,slug'],
            'envato_item_id' => ['nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
            'category' => ['nullable', 'string', 'max:50'],
            'currency' => ['nullable', 'string', 'max:10'],
        ]);

        $metadata = [
            'category' => $validated['category'] ?? 'software',
            'currency' => $validated['currency'] ?? 'USD',
        ];

        SoftwareProduct::create([
            'name' => $validated['name'],
            'slug' => strtolower($validated['slug']),
            'envato_item_id' => $validated['envato_item_id'] ?: null,
            'is_active' => $request->boolean('is_active', true),
            'metadata' => $metadata,
        ]);

        return redirect()
            ->route('admin.licensing.products.index')
            ->with('status', 'Software product registered successfully.');
    }

    /**
     * Show the form for editing the software product.
     */
    public function edit(SoftwareProduct $product): View
    {
        return view('admin.licensing.products.edit', [
            'product' => $product,
        ]);
    }

    /**
     * Update the software product in storage.
     */
    public function update(Request $request, SoftwareProduct $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:80', 'alpha_dash', Rule::unique('software_products')->ignore($product->id)],
            'envato_item_id' => ['nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
            'category' => ['nullable', 'string', 'max:50'],
            'currency' => ['nullable', 'string', 'max:10'],
        ]);

        $metadata = array_merge($product->metadata ?? [], [
            'category' => $validated['category'] ?? ($product->metadata['category'] ?? 'software'),
            'currency' => $validated['currency'] ?? ($product->metadata['currency'] ?? 'USD'),
        ]);

        $product->update([
            'name' => $validated['name'],
            'slug' => strtolower($validated['slug']),
            'envato_item_id' => $validated['envato_item_id'] ?: null,
            'is_active' => $request->boolean('is_active'),
            'metadata' => $metadata,
        ]);

        return redirect()
            ->route('admin.licensing.products.index')
            ->with('status', 'Software product updated successfully.');
    }

    /**
     * Remove the software product from storage.
     */
    public function destroy(SoftwareProduct $product): RedirectResponse
    {
        if ($product->licenses()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete product with existing issued licenses. Deactivate it instead.']);
        }

        $product->delete();

        return redirect()
            ->route('admin.licensing.products.index')
            ->with('status', 'Software product removed.');
    }
}
