<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * A client's checkout orders and receipts. Ownership is keyed on the email
 * the order was placed with — the same identity checkout records.
 */
class OrderController extends Controller
{
    /**
     * Every order placed with the client's email address.
     */
    public function index(Request $request): View
    {
        $orders = $request->user()
            ->orders()
            ->with(['product', 'plan'])
            ->orderByDesc('id')
            ->paginate(12);

        return view('dashboard.orders.index', compact('orders'));
    }

    /**
     * One order in full, as a receipt.
     */
    public function show(Request $request, Order $order): View
    {
        abort_unless($order->email === $request->user()->email, 404);

        $order->load(['product', 'plan', 'gateway', 'subscription.plan']);

        return view('dashboard.orders.show', compact('order'));
    }
}
