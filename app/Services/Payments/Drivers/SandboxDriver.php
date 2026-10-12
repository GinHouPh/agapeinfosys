<?php

namespace App\Services\Payments\Drivers;

use App\Models\PaymentGateway;
use App\Services\Payments\Contracts\Payable;
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

    public function createCheckout(Payable $payable): CheckoutSession
    {
        return CheckoutSession::inline('sandbox', [
            'reference' => $payable->paymentReference(),
            'amount' => $payable->paymentAmount(),
            'currency' => $payable->paymentCurrency(),
            'amount_display' => $payable->paymentAmountDisplay(),
            'plan' => $payable->paymentDescription(),
            'token' => $this->tokenFor($payable),
        ]);
    }

    /**
     * Accept a simulated success or failure posted by the payment view.
     */
    public function verifyCallback(Payable $payable, Request $request): void
    {
        $token = (string) $request->input('token', '');

        if (! hash_equals($this->tokenFor($payable), $token)) {
            throw new WebhookVerificationException('The sandbox payment token is not valid.');
        }

        if ($request->input('outcome') === 'failure') {
            throw new GatewayException('Sandbox payment was declined.');
        }

        $payable->recordGatewayResult(
            ['gateway_payment_id' => 'sandbox_'.bin2hex(random_bytes(8))],
            ['verified_via' => 'sandbox']
        );
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
     * The simulator always confirms a pending payment as paid.
     */
    public function confirm(Payable $payable): ?bool
    {
        return true;
    }

    public function isRedirectBased(): bool
    {
        return false;
    }

    /**
     * Tamper-proof token binding the payment view to this payable.
     */
    public function tokenFor(Payable $payable): string
    {
        return hash_hmac('sha256', $payable->paymentReference().'|'.$payable->paymentAmount(), $this->secret());
    }

    private function secret(): string
    {
        $credentials = $this->gateway->credentials();

        return $credentials['secret'] ?? (string) config('app.key');
    }
}
