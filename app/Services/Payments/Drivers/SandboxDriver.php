<?php

namespace App\Services\Payments\Drivers;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\Payments\Contracts\PaymentGatewayDriver;
use App\Services\Payments\DTOs\CheckoutSession;
use App\Services\Payments\Exceptions\GatewayException;
use App\Services\Payments\Exceptions\WebhookVerificationException;
use Illuminate\Http\Request;

/**
 * Built-in simulator so the full checkout can be exercised without provider keys.
 *
 * Credentials are optional; the application key is used as the HMAC secret
 * when no explicit secret has been saved in the admin panel.
 */
class SandboxDriver implements PaymentGatewayDriver
{
    public function __construct(private readonly PaymentGateway $gateway) {}

    public function driver(): string
    {
        return 'sandbox';
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function createCheckout(Order $order): CheckoutSession
    {
        return CheckoutSession::inline('sandbox', [
            'reference' => $order->reference,
            'amount' => $order->amount,
            'currency' => $order->currency,
            'amount_display' => $order->formattedAmount(),
            'plan' => $order->plan?->name,
            'token' => $this->tokenFor($order),
        ]);
    }

    /**
     * Accept a simulated success or failure posted by the payment view.
     */
    public function verifyCallback(Order $order, Request $request): void
    {
        $token = (string) $request->input('token', '');

        if (! hash_equals($this->tokenFor($order), $token)) {
            throw new WebhookVerificationException('The sandbox payment token is not valid.');
        }

        if ($request->input('outcome') === 'failure') {
            throw new GatewayException('Sandbox payment was declined.');
        }

        $order->forceFill([
            'gateway_payment_id' => 'sandbox_'.bin2hex(random_bytes(8)),
            'gateway_payload' => array_merge($order->gateway_payload ?? [], [
                'verified_via' => 'sandbox',
            ]),
        ])->save();
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $signature = (string) $request->header('X-Sandbox-Signature', '');

        if ($signature === '') {
            return false;
        }

        return hash_equals(
            hash_hmac('sha256', $request->getContent(), $this->secret()),
            $signature
        );
    }

    /**
     * The simulator always confirms a pending order as paid.
     */
    public function confirm(Order $order): ?bool
    {
        return true;
    }

    public function isRedirectBased(): bool
    {
        return false;
    }

    /**
     * Tamper-proof token binding the payment view to this order.
     */
    public function tokenFor(Order $order): string
    {
        return hash_hmac('sha256', $order->reference.'|'.$order->amount, $this->secret());
    }

    private function secret(): string
    {
        $credentials = $this->gateway->credentials();

        return $credentials['secret'] ?? (string) config('app.key');
    }
}
