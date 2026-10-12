<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Paginated order list with status/product/search filters.
     */
    public function index(Request $request): View
    {
        $search = (string) $request->query('q', '');
        $status = (string) $request->query('status', '');
        $productId = $request->query('product');

        $orders = Order::query()
            ->with(['product', 'plan', 'gateway'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(fn ($builder) => $builder
                    ->where('reference', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('subscriber_key', 'like', "%{$search}%"));
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($productId !== null && $productId !== '', fn ($query) => $query->where('product_id', $productId))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'search' => $search,
            'status' => $status,
            'products' => Product::query()->ordered()->get(),
            'currentProduct' => Product::query()->find($productId),
            'statuses' => OrderStatus::cases(),
        ]);
    }

    /**
     * Order detail with its payment event timeline.
     */
    public function show(Order $order): View
    {
        $order->load(['product', 'plan', 'gateway', 'subscription.plan', 'events.gateway']);

        return view('admin.orders.show', compact('order'));
    }
}
