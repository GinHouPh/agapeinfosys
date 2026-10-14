<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * The digital access a client has unlocked: every product they can use,
 * whether the access comes from a subscription or a one-off purchase.
 */
class EntitlementController extends Controller
{
    /**
     * Access summary across subscriptions and paid one-off orders.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $subscriptions = $user->subscriptions()
            ->with(['product', 'plan'])
            ->orderByDesc('id')
            ->get();

        $subscribedProductIds = $subscriptions->pluck('product_id')->filter()->unique();

        $oneTimeOrders = $this->oneTimeOrders(
            $user->orders()->with(['product', 'plan'])->paid()->orderByDesc('id')->get(),
            $subscribedProductIds,
        );

        return view('dashboard.access.index', [
            'subscriptions' => $subscriptions,
            'oneTimeOrders' => $oneTimeOrders,
            'hasAccess' => $subscriptions->isNotEmpty() || $oneTimeOrders->isNotEmpty(),
        ]);
    }

    /**
     * The newest paid order per product that no subscription already covers.
     *
     * @param  Collection<int, Order>  $orders
     * @param  Collection<int, int>  $subscribedProductIds
     * @return Collection<int, Order>
     */
    private function oneTimeOrders(Collection $orders, Collection $subscribedProductIds): Collection
    {
        return $orders
            ->reject(fn (Order $order) => $subscribedProductIds->contains($order->product_id))
            ->unique('product_id')
            ->values();
    }
}
