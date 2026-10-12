<?php

namespace App\Services\Payments\Drivers;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\Payments\Contracts\PaymentGatewayDriver;
use App\Services\Payments\DTOs\CheckoutSession;
use App\Services\Payments\Exceptions\GatewayException;
use App\Services\Payments\Exceptions\GatewayNotConfiguredException;
use App\Services\Payments\Exceptions\WebhookVerificationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * PayU — signed form POST to the hosted payment page (India).
 *
 * Config-ready: add merchant_key + salt in the admin panel to activate.
 * Requests carry a SHA-512 hash; returns and webhooks are verified with the
 * same scheme before the order status API confirms the payment.
 */
class PayUDriver implements PaymentGatewayDriver
{
    private const SANDBOX_URL = 'https://test.payu.in/_payment';

    private const LIVE_URL = 'https://secure.payu.in/_payment';

    private const STATUS_ENDPOINT = 'https://info.payu.in/merchant/postservice?form=2';

    public function __construct(private readonly PaymentGateway $gateway) {}

    public function driver(): string
    {
        return 'payu';
    }

    public function isConfigured(): bool
    {
        $credentials = $this->gateway->credentials();

        return ($credentials['merchant_key'] ?? '') !== '' && ($credentials['salt'] ?? '') !== '';
    }

    /**
     * Build the signed form fields PayU expects.
     */
    public function createCheckout(Order $order): CheckoutSession
    {
        $credentials = $this->requireCredentials();
        $key = (string) $credentials['merchant_key'];
        $salt = (string) $credentials['salt'];

        $txnid = $order->reference;
        $amount = number_format($order->amount / 100, 2, '.', '');
        $productinfo = (string) ($order->plan?->name ?? 'Plan');
        $firstname = (string) ($order->name ?? 'Customer');
        $email = (string) ($order->email ?? 'no-reply@example.com');

        $udf1 = $order->product?->slug ?? '';
        $udf2 = $order->subscriber_key;

        $hashString = implode('|', [
            $key, $txnid, $amount, $productinfo, $firstname, $email,
            $udf1, $udf2, '', '', '', $salt,
        ]);

        $fields = [
            'key' => $key,
            'txnid' => $txnid,
            'amount' => $amount,
            'productinfo' => $productinfo,
            'firstname' => $firstname,
            'email' => $email,
            'phone' => (string) ($order->phone ?? ''),
            'udf1' => $udf1,
            'udf2' => $udf2,
            'surl' => route('checkout.result', [$order->product, $order]),
            'furl' => route('checkout.result', [$order->product, $order]),
            'hash' => hash('sha512', $hashString),
        ];

        return CheckoutSession::redirectForm(
            'payu',
            $this->gateway->isLive() ? self::LIVE_URL : self::SANDBOX_URL,
            $fields
        );
    }

    /**
     * Verify the SHA-512 hash PayU appends to return and webhook payloads.
     */
    public function verifyCallback(Order $order, Request $request): void
    {
        $credentials = $this->requireCredentials();
        $salt = (string) $credentials['salt'];

        if ($request->input('txnid') !== $order->reference) {
            throw new GatewayException('The PayU callback references a different order.');
        }

        if ($this->hashFrom($request, $salt) === null) {
            throw new WebhookVerificationException('The PayU callback is missing its hash.');
        }

        $expected = $this->expectedReturnHash($request, $salt);

        if (! hash_equals($expected, (string) $request->input('hash'))) {
            throw new WebhookVerificationException('The PayU signature could not be verified.');
        }

        $status = (string) $request->input('status', '');

        if ($status !== 'success') {
            throw new GatewayException('PayU reported the payment as "'.$status.'".');
        }

        $order->forceFill([
            'gateway_payment_id' => (string) $request->input('mihpayid', ''),
            'gateway_payload' => array_merge($order->gateway_payload ?? [], [
                'verified_via' => 'return',
            ]),
        ])->save();
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $credentials = $this->gateway->credentials();
        $salt = $credentials['salt'] ?? '';

        if ($salt === '' || $request->input('hash') === null) {
            return false;
        }

        return hash_equals($this->expectedReturnHash($request, $salt), (string) $request->input('hash'));
    }

    /**
     * PayU exposes a transaction-status post service for confirmation.
     */
    public function confirm(Order $order): ?bool
    {
        $credentials = $this->gateway->credentials();
        $key = $credentials['merchant_key'] ?? '';
        $salt = $credentials['salt'] ?? '';

        if ($key === '' || $salt === '') {
            return null;
        }

        $command = 'txnStatus';
        $hash = hash('sha512', $key.'|'.$command.'|'.$order->reference.'|'.$salt);

        $response = Http::connectTimeout(5)
            ->timeout(15)
            ->asForm()
            ->post(self::STATUS_ENDPOINT, [
                'key' => $key,
                'command' => $command,
                'var1' => $order->reference,
                'hash' => $hash,
            ]);

        if ($response->failed()) {
            return null;
        }

        $status = (string) $response->json('status', $response->json('msg', ''));

        if ($status === '') {
            return null;
        }

        return in_array($status, ['success', 'partial', 'capture'], true);
    }

    public function isRedirectBased(): bool
    {
        return true;
    }

    /**
     * PayU's return hash: sha512(salt|status||udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|key).
     */
    private function expectedReturnHash(Request $request, string $salt): string
    {
        $parts = [
            $salt,
            (string) $request->input('status', ''),
            '',
            (string) $request->input('udf5', ''),
            (string) $request->input('udf4', ''),
            (string) $request->input('udf3', ''),
            (string) $request->input('udf2', ''),
            (string) $request->input('udf1', ''),
            (string) $request->input('email', ''),
            (string) $request->input('firstname', ''),
            (string) $request->input('productinfo', ''),
            (string) $request->input('amount', ''),
            (string) $request->input('txnid', ''),
            (string) $this->gateway->credentials()['merchant_key'],
        ];

        return hash('sha512', implode('|', $parts));
    }

    private function hashFrom(Request $request, string $salt): ?string
    {
        $hash = $request->input('hash');

        return is_string($hash) && $hash !== '' ? $hash : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function requireCredentials(): array
    {
        if (! $this->isConfigured()) {
            throw new GatewayNotConfiguredException(
                'PayU is missing merchant_key/salt for the active gateway mode.'
            );
        }

        return $this->gateway->credentials();
    }
}
