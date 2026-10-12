<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Paginated digital product list.
     */
    public function index(Request $request): View
    {
        $search = (string) $request->query('q', '');

        $products = Product::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'search'));
    }

    /**
     * Creation form.
     */
    public function create(): View
    {
        return view('admin.products.create');
    }

    /**
     * Persist a new digital product.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        Product::create($request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product created.');
    }

    /**
     * Edit form.
     */
    public function edit(Product $product): View
    {
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Apply edits to an existing product.
     */
    public function update(StoreProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product updated.');
    }

    /**
     * Remove a product and its plans/orders cascade.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product deleted.');
    }

    /**
     * Issue a fresh API key/secret pair for the product.
     */
    public function rotate(Product $product): RedirectResponse
    {
        $secret = $product->rotateApiCredentials();

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('rotated_secret', $secret)
            ->with('status', 'API credentials rotated.');
    }
}
