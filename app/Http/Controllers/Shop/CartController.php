<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Shop\Cart;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Public shop cart: add, list and remove plans.
 */
class CartController extends Controller
{
    public function __construct(private readonly Cart $cart) {}

    /**
     * Cart contents with live plan prices.
     */
    public function index(): View
    {
        $lines = $this->cart->lines();

        return view('shop.cart', [
            'lines' => $lines,
            'subtotal' => (int) $lines->sum('price'),
            'cartCount' => $lines->count(),
        ]);
    }

    /**
     * Add a plan to the cart — one plan per product, so the entitlement
     * granted at checkout stays unambiguous.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product' => ['required', 'string', 'max:180'],
            'plan' => ['required', 'string', 'max:120'],
        ]);

        $product = Product::query()
            ->active()
            ->where('slug', $validated['product'])
            ->first();

        $plan = $product?->plans()
            ->active()
            ->where('code', $validated['plan'])
            ->first();

        if ($plan === null) {
            return back()->withErrors(['plan' => 'That plan is no longer available.']);
        }

        if ($this->cart->hasProduct($product->getKey())) {
            return back()->withErrors([
                'plan' => 'This product is already in your cart — remove it first to pick another plan.',
            ]);
        }

        $this->cart->add($product, $plan);

        return back()->with('status', $plan->name.' added to your cart.');
    }

    /**
     * Remove one plan line from the cart.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'plan_id' => ['required', 'integer'],
        ]);

        $this->cart->remove((int) $validated['product_id'], (int) $validated['plan_id']);

        return redirect()
            ->route('shop.cart.index')
            ->with('status', 'Removed from your cart.');
    }
}
