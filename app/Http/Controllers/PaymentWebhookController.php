<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Payments\CheckoutService;
use App\Services\Payments\GatewayManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Inbound payment-provider webhooks.
 *
 * Signature verification is defence-in-depth; confirm() against the
 * provider's own status API is the trust root, so a forged webhook can
 * never mark an order paid.
 */
class PaymentWebhookController extends Controller
{
    public function __construct(
        private readonly GatewayManager $gateways,
        private readonly CheckoutService $checkout,
    ) {}

    public function __invoke(Request $request, string $driver): Response
    {
        $gateway = $this->gateways->defaultGatewayFor($driver);

        if ($gateway === null) {
            abort(404);
        }

        $resolved = $this->gateways->make($gateway);

        $signatureValid = $resolved->verifyWebhookSignature($request);

        [$orderId, $eventType] = $this->extractOrder($driver, $request);

        $order = $orderId !== null
            ? Order::query()->where('reference', $orderId)->first()
            : null;

        if ($order === null) {
            return response('ignored', 200);
        }

        // Never trust the payload alone — ask the provider.
        if ($resolved->confirm($order) !== true) {
            return response('unconfirmed', 200);
        }

        $this->checkout->complete($order, [
            'event' => $eventType,
            'driver' => $driver,
            'signature_valid' => $signatureValid,
            'reference' => $order->reference,
            'signature' => $request->header('x-webhook-signature') ?? $request->header('X-Signature'),
        ], 'inbound');

        return response('ok', 200);
    }

    /**
     * Pull the order reference and event name out of a provider payload.
     *
     * @return array{0: ?string, 1: string}
     */
    private function extractOrder(string $driver, Request $request): array
    {
        $payload = $request->json()->all();

        return match ($driver) {
            'phonepe' => [
                data_get($payload, 'data.merchantOrderId') ?? data_get($payload, 'merchantOrderId'),
                (string) ($payload['code'] ?? 'phonepe.event'),
            ],
            'cashfree' => [
                data_get($payload, 'data.order.order_id') ?? data_get($payload, 'data.payment.order_id'),
                (string) ($payload['type'] ?? 'cashfree.event'),
            ],
            'payu' => [
                $request->input('txnid') ?? data_get($payload, 'txnid'),
                'payu.transaction',
            ],
            'paypal' => [
                data_get($payload, 'resource.custom_id') ?? data_get($payload, 'resource.invoice_id'),
                (string) ($payload['event_type'] ?? 'paypal.event'),
            ],
            default => [data_get($payload, 'reference'), 'sandbox.event'],
        };
    }
}
