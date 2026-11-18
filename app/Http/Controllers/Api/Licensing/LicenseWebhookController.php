<?php

namespace App\Http\Controllers\Api\Licensing;

use App\Http\Controllers\Controller;
use App\Mail\LicenseIssuedMail;
use App\Models\License;
use App\Models\SoftwareProduct;
use App\Services\Licensing\AgapeLicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LicenseWebhookController extends Controller
{
    public function __construct(private readonly AgapeLicenseService $licenseService) {}

    /**
     * Cashfree PG Order Success Webhook
     */
    public function handleCashfreeOrder(Request $request): JsonResponse
    {
        $payload = $request->all();
        $signature = $request->header('x-webhook-signature');

        $secret = (string) config('services.cashfree.secret_key');
        if ($secret !== '') {
            $expectedSignature = base64_encode(hash_hmac('sha256', $request->getContent(), $secret, true));
            if (! hash_equals($expectedSignature, (string) ($signature ?? ''))) {
                Log::warning('Cashfree invalid webhook signature attempt', ['ip' => $request->ip()]);

                return response()->json(['error' => 'Invalid signature'], 401);
            }
        }

        $paymentStatus = $payload['data']['payment']['payment_status'] ?? $payload['order_status'] ?? '';

        if (strtoupper((string) $paymentStatus) === 'SUCCESS' || strtoupper((string) $paymentStatus) === 'PAID') {
            $customerEmail = $payload['data']['customer_details']['customer_email']
                ?? $payload['customer_details']['customer_email']
                ?? 'customer@agapeinfosys.com';

            $productSlug = $payload['data']['order']['order_tags']['product_slug']
                ?? $payload['order_tags']['product_slug']
                ?? 'srs-genie';

            $tier = $payload['data']['order']['order_tags']['tier']
                ?? $payload['order_tags']['tier']
                ?? 'regular';

            $product = SoftwareProduct::where('slug', $productSlug)->first();
            if ($product !== null) {
                $licenseKey = $this->licenseService->generateLicenseKey('SRS');

                $maxDomains = match ($tier) {
                    'agency' => 5,
                    'enterprise' => 999,
                    'extended' => 3,
                    default => 1,
                };

                $license = License::create([
                    'product_id' => $product->id,
                    'license_key' => $licenseKey,
                    'channel' => 'agape_direct',
                    'buyer_email' => $customerEmail,
                    'buyer_username' => Str::before($customerEmail, '@'),
                    'tier' => $tier,
                    'max_domains' => $maxDomains,
                    'status' => 'active',
                    'client_secret_salt' => Str::random(32),
                ]);

                Log::info("License issued via Cashfree checkout: {$licenseKey} for {$customerEmail}");

                try {
                    Mail::to($customerEmail)->queue(new LicenseIssuedMail($license));
                } catch (\Throwable $e) {
                    Log::warning('Failed to queue license email: '.$e->getMessage());
                }

                return response()->json([
                    'status' => 'handled',
                    'license_key' => $license->license_key,
                ]);
            }
        }

        return response()->json(['status' => 'ignored']);
    }

    /**
     * Envato Sale / Refund Webhook Notification
     */
    public function handleEnvatoSale(Request $request): JsonResponse
    {
        $event = $request->input('event');
        $code = $request->input('purchase_code');

        if (! empty($code)) {
            $license = License::where('license_key', $code)->first();

            if ($license !== null && in_array($event, ['refund', 'dispute_created', 'chargeback'], true)) {
                $license->update(['status' => 'refunded']);
                $license->domainBindings()->update(['status' => 'deactivated']);
                Log::info("License {$code} revoked due to Envato {$event} event.");
            }
        }

        return response()->json(['status' => 'handled']);
    }
}
