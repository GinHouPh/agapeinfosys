<?php

namespace App\Services\Payments;

use App\Models\PaymentGateway;
use App\Services\Payments\Contracts\PaymentGatewayDriver;
use App\Services\Payments\Drivers\CashfreeDriver;
use App\Services\Payments\Drivers\PayPalDriver;
use App\Services\Payments\Drivers\PayUDriver;
use App\Services\Payments\Drivers\PhonePeDriver;
use App\Services\Payments\Drivers\SandboxDriver;
use App\Services\Payments\Exceptions\GatewayException;
use InvalidArgumentException;

/**
 * Maps payment gateway records onto their driver implementations.
 */
class GatewayManager
{
    /**
     * Supported drivers in display order.
     *
     * @var array<string, class-string<PaymentGatewayDriver>>
     */
    public const DRIVERS = [
        'cashfree' => CashfreeDriver::class,
        'phonepe' => PhonePeDriver::class,
        'payu' => PayUDriver::class,
        'paypal' => PayPalDriver::class,
        'sandbox' => SandboxDriver::class,
    ];

    /**
     * Short descriptions shown in the admin gateway list.
     *
     * @return array<string, string>
     */
    public static function descriptions(): array
    {
        return [
            'cashfree' => 'Cashfree PG — UPI, cards, wallets, India',
            'phonepe' => 'PhonePe PG — UPI & cards, India',
            'payu' => 'PayU — cards, netbanking, EMI, India',
            'paypal' => 'PayPal — international cards & wallets',
            'sandbox' => 'Built-in simulator for local testing',
        ];
    }

    /**
     * Resolve the driver for a gateway record.
     *
     * @throws GatewayException
     */
    public function make(PaymentGateway $gateway): PaymentGatewayDriver
    {
        $class = self::DRIVERS[$gateway->driver] ?? null;

        if ($class === null) {
            throw new InvalidArgumentException(
                'No driver registered for payment gateway "'.$gateway->driver.'".'
            );
        }

        return new $class($gateway);
    }

    /**
     * The gateway record for a driver name, regardless of enabled state.
     */
    public function defaultGatewayFor(string $driver): ?PaymentGateway
    {
        return PaymentGateway::query()->where('driver', $driver)->first();
    }

    /**
     * The gateway new checkouts should use: the enabled default, falling back
     * to the first enabled gateway.
     */
    public function defaultGateway(): ?PaymentGateway
    {
        return PaymentGateway::query()
            ->enabled()
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();
    }

    /**
     * All enabled gateways that carry credentials for their active mode.
     *
     * @return array<int, PaymentGateway>
     */
    public function availableGateways(): array
    {
        return PaymentGateway::query()
            ->enabled()
            ->orderBy('id')
            ->get()
            ->filter(fn (PaymentGateway $gateway): bool => $this->make($gateway)->isConfigured())
            ->values()
            ->all();
    }

    /**
     * The gateway a checkout must charge through: the requested driver or
     * the enabled default — rejecting missing, disabled or unconfigured
     * records before any order row is written.
     *
     * @throws GatewayException
     */
    public function resolveCheckoutGateway(?string $driver = null): PaymentGateway
    {
        $gateway = $driver !== null
            ? PaymentGateway::query()->where('driver', $driver)->first()
            : $this->defaultGateway();

        if ($gateway === null || ! $gateway->is_enabled) {
            throw new GatewayException('No payment gateway is currently available.');
        }

        if (! $this->make($gateway)->isConfigured()) {
            throw new GatewayException('The "'.$gateway->name.'" gateway is not configured yet.');
        }

        return $gateway;
    }
}
