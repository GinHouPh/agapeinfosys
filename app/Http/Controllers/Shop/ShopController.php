<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Shop\Cart;
use Illuminate\Contracts\View\View;

/**
 * Public shop: product browsing.
 */
class ShopController extends Controller
{
    public function __construct(private readonly Cart $cart) {}

    /**
     * All active products with their purchasable plans.
     */
    public function index(): View
    {
        $products = Product::query()
            ->active()
            ->ordered()
            ->with(['plans' => fn ($query) => $query->where('is_active', true)])
            ->get();

        return view('shop.index', [
            'products' => $products,
            'cartLines' => $this->cart->lines(),
            'cartCount' => $this->cart->count(),
        ]);
    }

    /**
     * One product's plans, ready to add to the cart.
     */
    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        return view('shop.product', [
            'product' => $product,
            'plans' => $product->activePlans,
            'cartLines' => $this->cart->lines(),
        ]);
    }
}
