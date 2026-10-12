<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use HandlesImageUploads;

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
        $attributes = $request->validated();

        $attributes['logo'] = $this->storeUploadedImage($request->file('logo'), 'uploads/products');

        Product::create($attributes);

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
        $attributes = $request->validated();

        $attributes['logo'] = $this->replaceUploadedImage(
            $request->file('logo'),
            $product->logo,
            'uploads/products'
        );

        $product->update($attributes);

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product updated.');
    }

    /**
     * Remove a product and its plans/orders cascade.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->forgetUploadedImage($product->logo);

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
