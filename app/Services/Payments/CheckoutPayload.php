<?php

namespace App\Services\Payments;

use App\Models\PaymentGateway;
use App\Services\Payments\Contracts\Payable;

/**
 * Resolves the browser-side checkout payload for a payable.
 *
 * The session flash only survives a single request, so a customer who reloads
 * or returns to the pay page would otherwise find an empty session. The stored
 * provider payload is reused to rebuild the same checkout.
 */
final class CheckoutPayload
{
    /**
     * @return array<string, mixed>
     */
    public static function resolve(Payable $payable, ?PaymentGateway $gateway, mixed $flash): array
    {
        if (is_array($flash) && $flash !== []) {
            return $flash;
        }

        if ($gateway === null) {
            return [];
        }

        if ($gateway->driver === 'sandbox') {
            return app(GatewayManager::class)->make($gateway)->createCheckout($payable)->payload;
        }

        $stored = $payable->paymentGatewayPayload();
        $mode = $stored['checkout_mode'] ?? ($gateway->isLive() ? 'production' : 'sandbox');

        if (isset($stored['payment_session_id'])) {
            return [
                'payment_session_id' => $stored['payment_session_id'],
                'mode' => $mode,
            ];
        }

        if (isset($stored['subscription_session_id'])) {
            return [
                'subscription_session_id' => $stored['subscription_session_id'],
                'subscription_id' => $stored['subscription_id'] ?? $payable->paymentReference(),
                'recurring' => true,
                'mode' => $mode,
            ];
        }

        return [];
    }
}
