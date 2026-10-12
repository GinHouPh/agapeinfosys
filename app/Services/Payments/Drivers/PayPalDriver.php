<?php

namespace App\Services\Payments\Drivers;

use App\Models\PaymentGateway;
use App\Services\Payments\Contracts\Payable;
use App\Services\Payments\Contracts\PaymentGatewayDriver;
use App\Services\Payments\DTOs\CheckoutSession;
use App\Services\Payments\Exceptions\GatewayException;
use App\Services\Payments\Exceptions\GatewayNotConfiguredException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * PayPal Orders v2 — international card & PayPal-wallet checkout.
 *
 * Config-ready: add client_id + client_secret in the admin panel to activate.
 * The browser return captures the approved order server-side; webhook
 * verification defers to PayPal's verify-webhook-signature endpoint.
 */
class PayPalDriver implements PaymentGatewayDriver
{
    private const SANDBOX_BASE = 'https://api-m.sandbox.paypal.com';

    private const LIVE_BASE = 'https://api-m.paypal.com';

    public function __construct(private readonly PaymentGateway $gateway) {}

    public function driver(): string
    {
        return 'paypal';
    }

    public function isConfigured(): bool
    {
        $credentials = $this->gateway->credentials();

        return ($credentials['client_id'] ?? '') !== '' && ($credentials['client_secret'] ?? '') !== '';
    }

    /**
     * Create a PayPal order and return the approval redirect link.
     */
    public function createCheckout(Payable $payable): CheckoutSession
    {
        $token = $this->accessToken();

        $response = Http::withToken($token)
            ->connectTimeout(5)
            ->timeout(20)
            ->acceptJson()
            ->post($this->base().'/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $payable->paymentReference(),
                    'custom_id' => $payable->paymentReference(),
                    'description' => $payable->paymentDescription(),
                    'amount' => [
                        'currency_code' => $payable->paymentCurrency(),
                        'value' => number_format($payable->paymentAmount() / 100, 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'brand_name' => $payable->paymentBrand(),
                    'return_url' => $payable->paymentReturnUrl(),
                    'cancel_url' => $payable->paymentReturnUrl(),
                    'user_action' => 'PAY_NOW',
                ],
            ]);

        if ($response->failed()) {
            throw new GatewayException(
                'PayPal rejected the order: '.mb_substr($response->body(), 0, 300)
            );
        }

        $approveUrl = collect($response->json('links', []))
            ->firstWhere('rel', 'approve')['href'] ?? null;

        if (! is_string($approveUrl) || $approveUrl === '') {
            throw new GatewayException('PayPal did not return an approval link.');
        }

        $payable->recordGatewayResult(['gateway_order_id' => (string) $response->json('id')]);

        return CheckoutSession::redirect('paypal', $approveUrl);
    }

    /**
     * Capture the approved PayPal order; without capture no money moves.
     */
    public function verifyCallback(Payable $payable, Request $request): void
    {
        if ($payable->gatewayOrderId() === null) {
            throw new GatewayException('This order was never opened at PayPal.');
        }

        $response = Http::withToken($this->accessToken())
            ->connectTimeout(5)
            ->timeout(20)
            ->acceptJson()
            ->post($this->base().'/v2/checkout/orders/'.$payable->gatewayOrderId().'/capture');

        $status = (string) $response->json('status', '');

        if ($status !== 'COMPLETED') {
            throw new GatewayException(
                'PayPal capture did not complete ('.($status ?: $response->body()).').'
            );
        }

        $captureId = data_get($response, 'purchase_units.0.payments.captures.0.id');

        $payable->recordGatewayResult(
            ['gateway_payment_id' => is_string($captureId) ? $captureId : null],
            ['verified_via' => 'capture']
        );
    }

    /**
     * Delegate webhook verification to PayPal's own verification endpoint.
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        $credentials = $this->gateway->credentials();
        $webhookId = $credentials['webhook_id'] ?? '';

        if ($webhookId === '' || ! $request->header('PAYPAL-TRANSMISSION-ID')) {
            return false;
        }

        $response = Http::withToken($this->accessToken())
            ->connectTimeout(5)
            ->timeout(15)
            ->acceptJson()
            ->post($this->base().'/v1/notifications/verify-webhook-signature', [
                'auth_algo' => (string) $request->header('PAYPAL-AUTH-ALGO', ''),
                'cert_url' => (string) $request->header('PAYPAL-CERT-URL', ''),
                'transmission_id' => (string) $request->header('PAYPAL-TRANSMISSION-ID', ''),
                'transmission_sig' => (string) $request->header('PAYPAL-TRANSMISSION-SIG', ''),
                'transmission_time' => (string) $request->header('PAYPAL-TRANSMISSION-TIME', ''),
                'webhook_id' => $webhookId,
                'webhook_event' => $request->json()->all(),
            ]);

        return ($response->json('verify_result') ?? '') === 'SUCCESS';
    }

    /**
     * Read the capture state straight from PayPal.
     */
    public function confirm(Payable $payable): ?bool
    {
        if ($payable->gatewayOrderId() === null) {
            return null;
        }

        $response = Http::withToken($this->accessToken())
            ->connectTimeout(5)
            ->timeout(15)
            ->acceptJson()
            ->get($this->base().'/v2/checkout/orders/'.$payable->gatewayOrderId());

        if ($response->failed()) {
            return null;
        }

        return ($response->json('status') ?? '') === 'COMPLETED';
    }

    public function isRedirectBased(): bool
    {
        return true;
    }

    private function base(): string
    {
        return $this->gateway->isLive() ? self::LIVE_BASE : self::SANDBOX_BASE;
    }

    private function accessToken(): string
    {
        $credentials = $this->requireCredentials();

        $response = Http::asForm()
            ->withBasicAuth($credentials['client_id'], $credentials['client_secret'])
            ->connectTimeout(5)
            ->timeout(15)
            ->acceptJson()
            ->post($this->base().'/v1/oauth2/token', ['grant_type' => 'client_credentials']);

        $token = $response->json('access_token');

        if (! is_string($token) || $token === '') {
            throw new GatewayException('PayPal rejected the API credentials.');
        }

        return $token;
    }

    /**
     * @return array<string, mixed>
     */
    private function requireCredentials(): array
    {
        if (! $this->isConfigured()) {
            throw new GatewayNotConfiguredException(
                'PayPal is missing client_id/client_secret for the active gateway mode.'
            );
        }

        return $this->gateway->credentials();
    }
}
