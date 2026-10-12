<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Services\Payments\GatewayManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GatewayController extends Controller
{
    public function __construct(private readonly GatewayManager $gateways) {}

    /**
     * Payment gateway overview: mode, status and default.
     */
    public function index(): View
    {
        $gateways = PaymentGateway::query()->orderBy('id')->get();

        return view('admin.gateways.index', [
            'gateways' => $gateways,
            'descriptions' => GatewayManager::descriptions(),
            'manager' => $this->gateways,
        ]);
    }

    /**
     * Credential form for one gateway.
     */
    public function edit(PaymentGateway $gateway): View
    {
        return view('admin.gateways.edit', [
            'gateway' => $gateway,
            'description' => GatewayManager::descriptions()[$gateway->driver] ?? '',
            'driver' => $this->gateways->make($gateway),
        ]);
    }

    /**
     * Update enablement, mode, default flag and credentials.
     */
    public function update(Request $request, PaymentGateway $gateway): RedirectResponse
    {
        $fields = $this->credentialFields($gateway->driver);

        $validated = $request->validate([
            'is_enabled' => ['boolean'],
            'is_default' => ['boolean'],
            'mode' => ['required', 'in:test,live'],
            ...collect($fields)->mapWithKeys(fn (string $label, string $key): array => [
                $key => ['nullable', 'string', 'max:500'],
            ])->all(),
        ]);

        $credentials = collect($fields)->mapWithKeys(function (string $label, string $key) use ($request, $gateway): array {
            $value = trim((string) $request->input($key, ''));

            // Blank fields keep the stored secret instead of wiping it.
            if ($value === '') {
                $value = (string) ($gateway->credentials()[$key] ?? '');
            }

            return [$key => $value];
        })->all();

        $isDefault = $request->boolean('is_default');

        $gateway->fill([
            'is_enabled' => $request->boolean('is_enabled'),
            'is_default' => $isDefault,
            'mode' => $validated['mode'],
            $validated['mode'] === 'live' ? 'live_config' : 'test_config' => array_filter(
                $credentials,
                fn (string $value): bool => $value !== ''
            ),
        ])->save();

        if ($isDefault) {
            PaymentGateway::query()
                ->whereKeyNot($gateway->getKey())
                ->update(['is_default' => false]);
        }

        return redirect()
            ->route('admin.gateways.index')
            ->with('status', 'Gateway updated.');
    }

    /**
     * Credential fields per driver.
     *
     * @return array<string, string>
     */
    private function credentialFields(string $driver): array
    {
        return match ($driver) {
            'razorpay' => [
                'key_id' => 'Key ID',
                'key_secret' => 'Key Secret',
                'webhook_secret' => 'Webhook Secret',
            ],
            'phonepe' => [
                'merchant_id' => 'Merchant ID',
                'salt' => 'Salt',
                'salt_index' => 'Salt Index',
            ],
            'cashfree' => [
                'client_id' => 'Client ID',
                'client_secret' => 'Client Secret',
            ],
            'payu' => [
                'merchant_key' => 'Merchant Key',
                'salt' => 'Salt',
            ],
            'paypal' => [
                'client_id' => 'Client ID',
                'client_secret' => 'Client Secret',
                'webhook_id' => 'Webhook ID',
            ],
            default => [],
        };
    }
}
