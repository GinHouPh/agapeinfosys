<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\Shop\Cart;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;

/**
 * Public shop: marketplace browsing, category listings and product detail.
 */
class ShopController extends Controller
{
    public function __construct(private readonly Cart $cart) {}

    /**
     * Marketplace home — the market menu plus every active product.
     */
    public function index(): View
    {
        $products = Product::query()
            ->active()
            ->ordered()
            ->with([
                'category.parent',
                'plans' => fn ($query) => $query->where('is_active', true),
            ])
            ->get();

        return view('shop.index', [
            'markets' => $this->markets(),
            'products' => $products,
            'cartCount' => $this->cart->count(),
        ]);
    }

    /**
     * Products filed under a market, category or subcategory.
     */
    public function category(Category $category): View
    {
        abort_unless($category->is_active, 404);

        $category->load(['parent.parent', 'children']);

        $products = Product::query()
            ->active()
            ->inCategory($category)
            ->ordered()
            ->with(['plans' => fn ($query) => $query->where('is_active', true)])
            ->get();

        return view('shop.category', [
            'markets' => $this->markets(),
            'category' => $category,
            'subcategories' => $category->children,
            'products' => $products,
            'cartCount' => $this->cart->count(),
        ]);
    }

    /**
     * One product's detail page, ready to add a plan to the cart.
     */
    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'category.parent.parent',
            'plans' => fn ($query) => $query->where('is_active', true),
        ]);

        $related = Product::query()
            ->active()
            ->whereKeyNot($product->getKey())
            ->when(
                $product->category_id !== null,
                fn ($query) => $query->inCategory($product->category)
            )
            ->ordered()
            ->with(['plans' => fn ($query) => $query->where('is_active', true)])
            ->take(3)
            ->get();

        return view('shop.product', [
            'markets' => $this->markets(),
            'product' => $product,
            'plans' => $product->plans,
            'related' => $related,
            'cartLines' => $this->cart->lines(),
        ]);
    }

    /**
     * Top-level markets with their two descendant levels, for the mega menu.
     *
     * @return Collection<int, Category>
     */
    private function markets(): Collection
    {
        return Category::query()
            ->roots()
            ->active()
            ->ordered()
            ->with(['children' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->with(['children' => fn ($nested) => $nested
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
                ]),
            ])
            ->get();
    }
}
