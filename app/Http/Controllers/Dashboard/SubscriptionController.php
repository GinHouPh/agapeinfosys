<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * A client's subscriptions, including self-service cancellation.
 */
class SubscriptionController extends Controller
{
    /**
     * Every subscription held under the client's email address.
     */
    public function index(Request $request): View
    {
        $subscriptions = $request->user()
            ->subscriptions()
            ->with(['product', 'plan', 'lastOrder'])
            ->orderByDesc('id')
            ->paginate(12);

        return view('dashboard.subscriptions.index', compact('subscriptions'));
    }

    /**
     * Cancel the auto-renewal of an active subscription.
     *
     * Access continues until the end of the period already paid for; the
     * cancellation is also reflected in the entitlement API so connected
     * product sites stop renewing the subscriber.
     */
    public function cancel(Request $request, Subscription $subscription): RedirectResponse
    {
        abort_unless($subscription->email === $request->user()->email, 404);

        if ($subscription->status !== SubscriptionStatus::Active) {
            return back()->with('status', 'This subscription is not active, so there is nothing to cancel.');
        }

        $subscription->update([
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        $message = 'Subscription cancelled. No further renewals will be taken';

        if ($subscription->ends_at !== null) {
            $message .= ', and your access continues until '.$subscription->ends_at->format('d M Y').'.';
        } else {
            $message .= '.';
        }

        return back()->with('status', $message);
    }
}
