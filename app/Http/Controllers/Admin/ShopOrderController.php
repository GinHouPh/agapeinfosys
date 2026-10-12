<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ShopOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShopOrderController extends Controller
{
    /**
     * Paginated cart-order list with status/search filters.
     */
    public function index(Request $request): View
    {
        $search = (string) $request->query('q', '');
        $status = (string) $request->query('status', '');

        $orders = ShopOrder::query()
            ->with('gateway')
            ->withCount('items')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(fn ($builder) => $builder
                    ->where('reference', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('subscriber_key', 'like', "%{$search}%"));
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.shop-orders.index', [
            'orders' => $orders,
            'search' => $search,
            'status' => $status,
            'statuses' => OrderStatus::cases(),
        ]);
    }

    /**
     * Cart order detail: lines, payment state and its event timeline.
     */
    public function show(ShopOrder $order): View
    {
        $order->load(['items.product', 'items.plan', 'gateway', 'events.gateway']);

        $childOrders = Order::query()
            ->where('meta->shop_order_id', $order->getKey())
            ->get();

        return view('admin.shop-orders.show', [
            'order' => $order,
            'childOrders' => $childOrders,
        ]);
    }
}
