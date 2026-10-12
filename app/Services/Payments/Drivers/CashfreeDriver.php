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
use Ramsey\Uuid\Uuid;

/**
 * Cashfree PG — hosted checkout opened through the Cashfree JS SDK.
 *
 * Cashfree no longer issues payment links; the create-order API returns a
 * payment_session_id that the browser SDK turns into the hosted checkout
 * page (redirectTarget "_self"). Webhooks are signed with
 * base64(HMAC-SHA256(secret, timestamp.rawBody)) and every completion is
 * confirmed against the Cashfree order status API.
 *
 * @see https://www.cashfree.com/docs/payments/online/web/redirect
 * @see https://www.cashfree.com/docs/payments/online/webhooks/signature-verification
 */
class CashfreeDriver implements PaymentGatewayDriver
{
    private const SANDBOX_BASE = 'https://sandbox.cashfree.com/pg';

    private const LIVE_BASE = 'https://api.cashfree.com/pg';

    private const API_VERSION = '2026-01-01';

    public function __construct(private readonly PaymentGateway $gateway) {}

    public function driver(): string
    {
        return 'cashfree';
    }

    public function isConfigured(): bool
    {
        $credentials = $this->gateway->credentials();

        return ($credentials['client_id'] ?? '') !== '' && ($credentials['client_secret'] ?? '') !== '';
    }

    /**
     * Create the Cashfree order and hand the browser SDK its session id.
     */
    public function createCheckout(Payable $payable): CheckoutSession
    {
        $credentials = $this->requireCredentials();

        $response = Http::withHeaders([
            'x-client-id' => $credentials['client_id'],
            'x-client-secret' => $credentials['client_secret'],
            'x-api-version' => self::API_VERSION,
            // Deterministic per order, so retried checkouts stay idempotent.
            'x-idempotency-key' => (string) Uuid::uuid5(Uuid::NAMESPACE_URL, 'agape.checkout.'.$payable->paymentReference()),
        ])
            ->connectTimeout(5)
            ->timeout(20)
            ->acceptJson()
            ->post($this->base().'/orders', [
                'order_id' => $payable->paymentReference(),
                'order_amount' => round($payable->paymentAmount() / 100, 2),
                'order_currency' => $payable->paymentCurrency(),
                'customer_details' => $this->customerDetails($payable),
                'order_meta' => [
                    'return_url' => $payable->paymentReturnUrl(),
                    'notify_url' => route('checkout.webhook', 'cashfree'),
                ],
            ]);

        if ($response->failed()) {
            throw new GatewayException(
                'Cashfree rejected the order: '.mb_substr($response->body(), 0, 300)
            );
        }

        $session = $response->json('payment_session_id');

        if (! is_string($session) || $session === '') {
            throw new GatewayException('Cashfree did not return a payment session id.');
        }

        $payable->recordGatewayResult(['gateway_order_id' => (string) $response->json('cf_order_id')]);

        return CheckoutSession::inline('cashfree', [
            'payment_session_id' => $session,
            'mode' => $this->gateway->isLive() ? 'production' : 'sandbox',
        ]);
    }

    /**
     * Hosted return URLs carry no signature, so the status API is the only
     * trustworthy source — confirm here and reject unless fully paid.
     */
    public function verifyCallback(Payable $payable, Request $request): void
    {
        $returnedOrderId = (string) $request->query('order_id', $request->input('order_id', ''));

        if ($returnedOrderId !== '' && $returnedOrderId !== $payable->paymentReference()) {
            throw new GatewayException('The Cashfree callback references a different order.');
        }

        if ($this->confirm($payable) !== true) {
            throw new GatewayException('Cashfree has not confirmed this payment yet.');
        }

        $payable->recordGatewayResult([], ['verified_via' => 'status_api']);
    }

    /**
     * Cashfree signs "timestamp.rawBody" with the client secret and
     * base64-encodes the digest.
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        $credentials = $this->gateway->credentials();
        $secret = $credentials['client_secret'] ?? '';
        $signature = (string) $request->header('x-webhook-signature', '');
        $timestamp = (string) $request->header('x-webhook-timestamp', '');

        if ($secret === '' || $signature === '' || $timestamp === '') {
            return false;
        }

        $expected = base64_encode(
            hash_hmac('sha256', $timestamp.$request->getContent(), $secret, true)
        );

        return hash_equals($expected, $signature);
    }

    /**
     * Ask Cashfree for the authoritative order state.
     */
    public function confirm(Payable $payable): ?bool
    {
        $credentials = $this->gateway->credentials();
        $clientId = $credentials['client_id'] ?? '';
        $clientSecret = $credentials['client_secret'] ?? '';

        if ($clientId === '' || $clientSecret === '') {
            return null;
        }

        $response = Http::withHeaders([
            'x-client-id' => $clientId,
            'x-client-secret' => $clientSecret,
            'x-api-version' => self::API_VERSION,
        ])
            ->connectTimeout(5)
            ->timeout(15)
            ->acceptJson()
            ->get($this->base().'/orders/'.$payable->paymentReference());

        if ($response->failed()) {
            return null;
        }

        return ($response->json('order_status') ?? '') === 'PAID';
    }

    public function isRedirectBased(): bool
    {
        return true;
    }

    private function base(): string
    {
        return $this->gateway->isLive() ? self::LIVE_BASE : self::SANDBOX_BASE;
    }

    /**
     * Customer fields shaped to Cashfree's schema: alphanumeric id, name
     * and email at most 100 chars, phone exactly 10 digits.
     *
     * @return array<string, string>
     */
    private function customerDetails(Payable $payable): array
    {
        $customer = strtoupper(
            preg_replace('/[^A-Za-z0-9]/', '', (string) ($payable->paymentSubscriberKey() ?: $payable->paymentReference())) ?? ''
        );

        if (strlen($customer) < 3) {
            $customer = 'CUST'.strtoupper(
                preg_replace('/[^A-Za-z0-9]/', '', $payable->paymentReference()) ?? ''
            );
        }

        $name = trim((string) $payable->paymentName());

        $digits = preg_replace('/\D/', '', (string) $payable->paymentPhone()) ?? '';

        return array_filter([
            'customer_id' => substr($customer, 0, 50),
            // The API rejects names shorter than three characters, so very
            // short names are simply omitted (the field is optional).
            'customer_name' => mb_strlen($name) >= 3 ? mb_substr($name, 0, 100) : null,
            'customer_email' => $payable->paymentEmail() ?: 'no-reply@example.com',
            'customer_phone' => strlen($digits) >= 10 ? substr($digits, -10) : '9999999999',
        ]);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws GatewayNotConfiguredException
     */
    private function requireCredentials(): array
    {
        if (! $this->isConfigured()) {
            throw new GatewayNotConfiguredException(
                'Cashfree is missing client_id/client_secret for the active gateway mode.'
            );
        }

        return $this->gateway->credentials();
    }
}
