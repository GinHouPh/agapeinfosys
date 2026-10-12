<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Paginated entitlement list with status/product/search filters.
     */
    public function index(Request $request): View
    {
        $search = (string) $request->query('q', '');
        $status = (string) $request->query('status', '');
        $productId = $request->query('product');

        $subscriptions = Subscription::query()
            ->with(['product', 'plan'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(fn ($builder) => $builder
                    ->where('subscriber_key', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($productId !== null && $productId !== '', fn ($query) => $query->where('product_id', $productId))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.subscriptions.index', [
            'subscriptions' => $subscriptions,
            'search' => $search,
            'status' => $status,
            'products' => Product::query()->ordered()->get(),
            'currentProduct' => Product::query()->find($productId),
            'statuses' => SubscriptionStatus::cases(),
        ]);
    }
}
