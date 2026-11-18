# Agape Central Licensing Server — Laravel API Implementation Specification

**Document Type:** API Specification & Implementation Code  
**Framework:** Laravel 11+ / PHP 8.3  
**Target Project:** `D:\PROJECTS\agapeinfosys`  
**Endpoint Prefix:** `/api/v1/license`  
**Version:** 1.0.0  

---

## 1. API Route Definitions

Add to `routes/api.php`:

```php
use App\Http\Controllers\Api\Licensing\LicenseApiController;
use App\Http\Controllers\Api\Licensing\LicenseWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/license')->middleware(['throttle:30,1'])->group(function () {
    // Client Verification & Heartbeat
    Route::post('/verify', [LicenseApiController::class, 'verify']);
    Route::post('/heartbeat', [LicenseApiController::class, 'heartbeat']);
    Route::post('/deactivate', [LicenseApiController::class, 'deactivate']);

    // Software Releases & Updates
    Route::get('/check-update', [LicenseApiController::class, 'checkUpdate']);
    Route::get('/download-update', [LicenseApiController::class, 'downloadUpdate']);

    // Ingestion Webhooks
    Route::post('/webhooks/cashfree', [LicenseWebhookController::class, 'handleCashfreeOrder']);
    Route::post('/webhooks/envato', [LicenseWebhookController::class, 'handleEnvatoSale']);
});
```

---

## 2. Core Service Classes

### 1. `EnvatoVerificationService.php`
Place in `app/Services/Licensing/EnvatoVerificationService.php`:

```php
namespace App\Services\Licensing;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EnvatoVerificationService
{
    private string $apiToken;

    public function __construct()
    {
        $this->apiToken = config('services.envato.api_token', env('ENVATO_API_TOKEN', ''));
    }

    /**
     * Verifies purchase code against Envato Market API v3.
     */
    public function verifyPurchaseCode(string $code): ?array
    {
        $code = trim($code);
        $cacheKey = "envato_code_verify_{$code}";

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($code) {
            try {
                $response = Http::withToken($this->apiToken)
                    ->withHeaders(['User-Agent' => 'AgapeLicensing/1.0'])
                    ->timeout(10)
                    ->get("https://api.envato.com/v3/market/author/sale", ['code' => $code]);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::warning("Envato verification failed", [
                    'code' => $code,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            } catch (\Exception $e) {
                Log::error("Envato API connection error: " . $e->getMessage());
                return null;
            }
        });
    }
}
```

### 2. `AgapeLicenseService.php`
Place in `app/Services/Licensing/AgapeLicenseService.php`:

```php
namespace App\Services\Licensing;

use App\Models\License;
use App\Models\SoftwareProduct;
use Illuminate\Support\Str;

class AgapeLicenseService
{
    /**
     * Generate an Agape formatted license key: AGAPE-SRS-XXXX-XXXX-XXXX
     */
    public function generateLicenseKey(string $productPrefix = 'SRS'): string
    {
        do {
            $block1 = strtoupper(Str::random(4));
            $block2 = strtoupper(Str::random(4));
            $block3 = strtoupper(Str::random(4));
            $key = "AGAPE-{$productPrefix}-{$block1}-{$block2}-{$block3}";
        } while (License::where('license_key', $key)->exists());

        return $key;
    }

    /**
     * Normalize domain to apex format (stripping protocol, port, path, www).
     */
    public function normalizeDomain(string $rawDomain): string
    {
        $host = parse_url($rawDomain, PHP_URL_HOST) ?: $rawDomain;
        $host = strtolower(trim($host));
        $host = preg_replace('/:\d+$/', '', $host); // strip port
        $host = preg_replace('/^www\./', '', $host); // strip www
        return $host;
    }

    /**
     * Check if domain qualifies as a developer localhost/staging environment.
     */
    public function isLocalOrStaging(string $domain): bool
    {
        $whitelist = ['localhost', '127.0.0.1', '::1'];
        if (in_array($domain, $whitelist)) return true;

        $patterns = [
            '/\.test$/',
            '/\.local$/',
            '/\.invalid$/',
            '/\.example$/',
            '/^dev\./',
            '/^staging\./',
            '/^test\./'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $domain)) return true;
        }

        return false;
    }

    /**
     * Generate tamper-evident activation hash.
     */
    public function generateActivationHash(License $license, string $domain): string
    {
        $payload = implode('::', [
            $license->license_key,
            $domain,
            $license->tier,
            $license->expires_at ? $license->expires_at->timestamp : 'perpetual'
        ]);

        return hash_hmac('sha256', $payload, $license->client_secret_salt);
    }
}
```

---

## 3. Main License API Controller (`LicenseApiController.php`)

Place in `app/Http/Controllers/Api/Licensing/LicenseApiController.php`:

```php
namespace App\Http\Controllers\Api\Licensing;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\DomainBinding;
use App\Models\SoftwareProduct;
use App\Models\SoftwareRelease;
use App\Models\LicenseAuditLog;
use App\Services\Licensing\EnvatoVerificationService;
use App\Services\Licensing\AgapeLicenseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class LicenseApiController extends Controller
{
    public function __construct(
        private EnvatoVerificationService $envatoService,
        private AgapeLicenseService $licenseService
    ) {}

    /**
     * POST /api/v1/license/verify
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'license_key'       => 'required|string',
            'domain'            => 'required|string',
            'product_slug'      => 'required|string',
            'installed_version' => 'nullable|string',
        ]);

        $licenseKey = trim($validated['license_key']);
        $product = SoftwareProduct::where('slug', $validated['product_slug'])->where('is_active', true)->first();

        if (!$product) {
            return response()->json(['valid' => false, 'message' => 'Product not recognized or inactive.'], 404);
        }

        $domain = $this->licenseService->normalizeDomain($validated['domain']);
        $isLocal = $this->licenseService->isLocalOrStaging($domain);

        // 1. Detect Key Channel
        $isEnvato = preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $licenseKey);

        $license = License::where('license_key', $licenseKey)->first();

        if ($isEnvato && !$license) {
            // First-time validation of an Envato purchase code
            $saleData = $this->envatoService->verifyPurchaseCode($licenseKey);

            if (!$saleData || ($product->envato_item_id && ($saleData['item']['id'] ?? '') != $product->envato_item_id)) {
                $this->logAudit(null, 'envato_verification_failed', $domain, $request, 422);
                return response()->json(['valid' => false, 'message' => 'Invalid Envato Purchase Code for this item.'], 422);
            }

            $license = License::create([
                'product_id'         => $product->id,
                'license_key'        => $licenseKey,
                'channel'            => 'envato',
                'buyer_email'        => $saleData['buyer'] . '@envato.user',
                'buyer_username'     => $saleData['buyer'] ?? 'Envato Buyer',
                'tier'               => str_contains($saleData['license'] ?? '', 'Extended') ? 'extended' : 'regular',
                'max_domains'        => str_contains($saleData['license'] ?? '', 'Extended') ? 3 : 1,
                'support_until'      => isset($saleData['supported_until']) ? \Carbon\Carbon::parse($saleData['supported_until']) : null,
                'status'             => 'active',
                'client_secret_salt' => Str::random(32),
            ]);
        }

        if (!$license || !$license->isValid()) {
            $this->logAudit($license, 'license_inactive_or_expired', $domain, $request, 403);
            return response()->json(['valid' => false, 'message' => 'License key is invalid, revoked, or expired.'], 403);
        }

        // 2. Domain Binding Enforcement
        if (!$isLocal) {
            $binding = DomainBinding::where('license_id', $license->id)->where('domain', $domain)->first();

            if (!$binding) {
                $activeCount = $license->activeBindings()->count();
                if ($activeCount >= $license->max_domains) {
                    $this->logAudit($license, 'domain_limit_exceeded', $domain, $request, 403);
                    return response()->json([
                        'valid' => false,
                        'message' => "License domain limit reached ({$license->max_domains}). Deactivate an existing domain first."
                    ], 403);
                }

                $binding = DomainBinding::create([
                    'license_id'        => $license->id,
                    'domain'            => $domain,
                    'ip_address'        => $request->ip(),
                    'installed_version' => $validated['installed_version'] ?? '1.0.0',
                    'status'            => 'active',
                    'activated_at'      => now(),
                    'last_heartbeat_at' => now(),
                ]);
            } else {
                $binding->update([
                    'status'            => 'active',
                    'ip_address'        => $request->ip(),
                    'installed_version' => $validated['installed_version'] ?? $binding->installed_version,
                    'last_heartbeat_at' => now(),
                ]);
            }
        }

        // 3. Issue Signed Token
        $activationHash = $this->licenseService->generateActivationHash($license, $domain);
        $this->logAudit($license, 'verification_success', $domain, $request, 200);

        return response()->json([
            'valid'           => true,
            'message'         => 'License successfully verified.',
            'buyer'           => $license->buyer_username ?? $license->buyer_email,
            'tier'            => $license->tier,
            'channel'         => $license->channel,
            'activation_hash' => $activationHash,
            'client_salt'     => $license->client_secret_salt,
            'support_until'   => $license->support_until?->toIso8601String(),
            'features'        => [
                'mcp_gateway'       => true,
                'multi_ai_provider' => true,
                'white_label'       => in_array($license->tier, ['extended', 'agency', 'enterprise']),
            ]
        ]);
    }

    /**
     * POST /api/v1/license/heartbeat
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'license_key' => 'required|string',
            'domain'      => 'required|string',
            'version'     => 'nullable|string',
        ]);

        $domain = $this->licenseService->normalizeDomain($validated['domain']);
        $license = License::where('license_key', $validated['license_key'])->first();

        if (!$license || !$license->isValid()) {
            return response()->json(['valid' => false, 'message' => 'License no longer valid.'], 403);
        }

        DomainBinding::where('license_id', $license->id)
            ->where('domain', $domain)
            ->update([
                'last_heartbeat_at' => now(),
                'installed_version' => $validated['version'] ?? null,
                'ip_address'        => $request->ip()
            ]);

        $this->logAudit($license, 'heartbeat', $domain, $request, 200);
        return response()->json(['valid' => true, 'status' => 'active']);
    }

    /**
     * POST /api/v1/license/deactivate
     */
    public function deactivate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'license_key' => 'required|string',
            'domain'      => 'required|string',
        ]);

        $domain = $this->licenseService->normalizeDomain($validated['domain']);
        $license = License::where('license_key', $validated['license_key'])->first();

        if (!$license) {
            return response()->json(['success' => false, 'message' => 'License not found.'], 404);
        }

        DomainBinding::where('license_id', $license->id)
            ->where('domain', $domain)
            ->update(['status' => 'deactivated']);

        $this->logAudit($license, 'deactivated', $domain, $request, 200);
        return response()->json(['success' => true, 'message' => 'Domain unbound successfully. You may re-activate on a new host.']);
    }

    /**
     * GET /api/v1/license/check-update
     */
    public function checkUpdate(Request $request): JsonResponse
    {
        $productSlug = $request->query('product', 'srs-genie');
        $currentVersion = $request->query('current_version', '1.0.0');

        $product = SoftwareProduct::where('slug', $productSlug)->first();
        if (!$product) {
            return response()->json(['update_available' => false]);
        }

        $latestRelease = $product->latestRelease;
        if (!$latestRelease) {
            return response()->json(['update_available' => false]);
        }

        $hasUpdate = version_compare($latestRelease->version, $currentVersion, '>');

        return response()->json([
            'update_available' => $hasUpdate,
            'current_version'  => $currentVersion,
            'latest_version'   => $latestRelease->version,
            'min_php_version'  => $latestRelease->min_php_version,
            'changelog'        => $latestRelease->changelog,
            'sha256'           => $latestRelease->sha256_checksum,
            'download_url'     => $hasUpdate ? route('api.license.download', [
                'release_id'  => $latestRelease->id,
                'license_key' => $request->query('license_key'),
                'expires'     => now()->addHour()->timestamp,
            ]) : null
        ]);
    }

    private function logAudit(?License $license, string $event, string $domain, Request $request, int $code): void
    {
        LicenseAuditLog::create([
            'license_id'    => $license?->id,
            'event'         => $event,
            'domain'        => $domain,
            'ip_address'    => $request->ip(),
            'user_agent'    => $request->userAgent(),
            'payload'       => $request->except(['password', 'client_salt']),
            'response_code' => $code,
        ]);
    }
}
```

---

## 4. Webhook Ingestion Controller (`LicenseWebhookController.php`)

Place in `app/Http/Controllers/Api/Licensing/LicenseWebhookController.php`:

```php
namespace App\Http\Controllers\Api\Licensing;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\SoftwareProduct;
use App\Services\Licensing\AgapeLicenseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LicenseWebhookController extends Controller
{
    public function __construct(private AgapeLicenseService $licenseService) {}

    /**
     * Cashfree PG Order Success Webhook
     */
    public function handleCashfreeOrder(Request $request): JsonResponse
    {
        $payload = $request->all();
        $signature = $request->header('x-webhook-signature');

        // Verify Cashfree webhook HMAC signature
        $secret = config('services.cashfree.secret_key');
        $expectedSignature = base64_encode(hash_hmac('sha256', $request->getContent(), $secret, true));

        if (!hash_equals($expectedSignature, $signature ?? '')) {
            Log::warning('Cashfree invalid webhook signature attempt', ['ip' => $request->ip()]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $orderData = $payload['data']['order'] ?? [];
        $paymentStatus = $payload['data']['payment']['payment_status'] ?? '';

        if ($paymentStatus === 'SUCCESS') {
            $customerEmail = $payload['data']['customer_details']['customer_email'] ?? '';
            $productSlug = $payload['data']['order']['order_tags']['product_slug'] ?? 'srs-genie';
            $tier = $payload['data']['order']['order_tags']['tier'] ?? 'regular';

            $product = SoftwareProduct::where('slug', $productSlug)->firstOrFail();
            $licenseKey = $this->licenseService->generateLicenseKey('SRS');

            $license = License::create([
                'product_id'         => $product->id,
                'license_key'        => $licenseKey,
                'channel'            => 'agape_direct',
                'buyer_email'        => $customerEmail,
                'tier'               => $tier,
                'max_domains'        => ($tier === 'agency') ? 5 : 1,
                'status'             => 'active',
                'client_secret_salt' => Str::random(32),
            ]);

            // Dispatch welcome & key email to buyer
            Log::info("License issued via Cashfree checkout: {$licenseKey} for {$customerEmail}");
        }

        return response()->json(['status' => 'handled']);
    }
}
```
