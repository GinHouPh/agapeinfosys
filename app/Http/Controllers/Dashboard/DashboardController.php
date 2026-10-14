<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\OrderStatus;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Landing screen of the client dashboard: a snapshot of everything the
 * signed-in client owns — orders, subscriptions and active access.
 */
class DashboardController extends Controller
{
    /**
     * Overview of the client's account.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $orders = $user->orders()->with(['product', 'plan'])->orderByDesc('id')->get();

        $subscriptions = $user->subscriptions()
            ->with(['product', 'plan'])
            ->orderByDesc('id')
            ->get();

        return view('dashboard.index', [
            'paidOrderCount' => $orders->where('status', OrderStatus::Paid)->count(),
            'pendingOrderCount' => $orders->where('status', OrderStatus::Pending)->count(),
            'activeSubscriptionCount' => $subscriptions
                ->filter(fn ($subscription) => $subscription->status === SubscriptionStatus::Active)
                ->count(),
            'recentOrders' => $orders->take(5)->values(),
            'activeSubscriptions' => $subscriptions
                ->filter(fn ($subscription) => $subscription->isEntitled())
                ->take(5)
                ->values(),
        ]);
    }
}
