<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Models\Plan;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Paginated plan list, optionally scoped to one product.
     */
    public function index(Request $request): View
    {
        $productId = $request->query('product');
        $product = null;

        $plans = Plan::query()
            ->with('product')
            ->when($productId !== null && $productId !== '', function ($query) use ($productId, &$product): void {
                $product = Product::query()->find($productId);

                $query->where('product_id', $productId);
            })
            ->orderBy('product_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.plans.index', [
            'plans' => $plans,
            'products' => Product::query()->ordered()->get(),
            'currentProduct' => $product,
        ]);
    }

    /**
     * Creation form.
     */
    public function create(Request $request): View
    {
        $products = Product::query()->ordered()->get();

        $product = Product::query()->find($request->query('product'));

        return view('admin.plans.create', compact('products', 'product'));
    }

    /**
     * Persist a new plan.
     */
    public function store(StorePlanRequest $request): RedirectResponse
    {
        Plan::create($request->validated());

        return redirect()
            ->route('admin.plans.index')
            ->with('status', 'Plan created.');
    }

    /**
     * Edit form.
     */
    public function edit(Plan $plan): View
    {
        return view('admin.plans.edit', [
            'plan' => $plan,
            'products' => Product::query()->ordered()->get(),
        ]);
    }

    /**
     * Apply edits to an existing plan.
     */
    public function update(StorePlanRequest $request, Plan $plan): RedirectResponse
    {
        $plan->update($request->validated());

        return redirect()
            ->route('admin.plans.index')
            ->with('status', 'Plan updated.');
    }

    /**
     * Remove a plan.
     */
    public function destroy(Plan $plan): RedirectResponse
    {
        $plan->delete();

        return redirect()
            ->route('admin.plans.index')
            ->with('status', 'Plan deleted.');
    }
}
