<?php

namespace App\Http\Controllers\Api\Licensing;

use App\Http\Controllers\Controller;
use App\Models\DomainBinding;
use App\Models\License;
use App\Models\LicenseAuditLog;
use App\Models\SoftwareProduct;
use App\Models\SoftwareRelease;
use App\Services\Licensing\AgapeLicenseService;
use App\Services\Licensing\EnvatoVerificationService;
use App\Services\Licensing\ReleaseManagerService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LicenseApiController extends Controller
{
    public function __construct(
        private readonly EnvatoVerificationService $envatoService,
        private readonly AgapeLicenseService $licenseService,
        private readonly ReleaseManagerService $releaseManager,
    ) {}

    /**
     * POST /api/v1/license/verify
     */
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'license_key' => ['required', 'string'],
            'domain' => ['required', 'string'],
            'product_slug' => ['required', 'string'],
            'installed_version' => ['nullable', 'string'],
        ]);

        $licenseKey = trim($validated['license_key']);
        $product = SoftwareProduct::where('slug', $validated['product_slug'])
            ->where('is_active', true)
            ->first();

        if ($product === null) {
            return response()->json([
                'valid' => false,
                'message' => 'Product not recognized or inactive.',
            ], 404);
        }

        $domain = $this->licenseService->normalizeDomain($validated['domain']);
        $isLocal = $this->licenseService->isLocalOrStaging($domain);

        // 1. Detect Key Channel (Envato vs Agape Direct)
        $isEnvato = (bool) preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $licenseKey);

        $license = License::where('license_key', $licenseKey)->first();

        if ($isEnvato && $license === null) {
            // First-time validation of an Envato purchase code against Envato API
            $saleData = $this->envatoService->verifyPurchaseCode($licenseKey);

            if ($saleData === null || ($product->envato_item_id && ((string) ($saleData['item']['id'] ?? '')) !== (string) $product->envato_item_id)) {
                $this->logAudit(null, 'envato_verification_failed', $domain, $request, 422);

                return response()->json([
                    'valid' => false,
                    'message' => 'Invalid Envato Purchase Code for this item.',
                ], 422);
            }

            $isExtended = str_contains((string) ($saleData['license'] ?? ''), 'Extended');

            $license = License::create([
                'product_id' => $product->id,
                'license_key' => $licenseKey,
                'channel' => 'envato',
                'buyer_email' => ($saleData['buyer'] ?? 'envato_user').'@envato.user',
                'buyer_username' => $saleData['buyer'] ?? 'Envato Buyer',
                'tier' => $isExtended ? 'extended' : 'regular',
                'max_domains' => $isExtended ? 3 : 1,
                'support_until' => isset($saleData['supported_until']) ? Carbon::parse($saleData['supported_until']) : null,
                'status' => 'active',
                'client_secret_salt' => Str::random(32),
            ]);
        }

        if ($license === null || ! $license->isValid()) {
            $this->logAudit($license, 'license_inactive_or_expired', $domain, $request, 403);

            return response()->json([
                'valid' => false,
                'message' => 'License key is invalid, revoked, or expired.',
            ], 403);
        }

        // 2. Domain Binding Enforcement (Bypassed for local / staging environments)
        if (! $isLocal) {
            $binding = DomainBinding::where('license_id', $license->id)
                ->where('domain', $domain)
                ->first();

            if ($binding === null) {
                $activeCount = $license->activeBindings()->count();
                if ($activeCount >= $license->max_domains) {
                    $this->logAudit($license, 'domain_limit_exceeded', $domain, $request, 403);

                    return response()->json([
                        'valid' => false,
                        'message' => "License domain limit reached ({$license->max_domains}). Deactivate an existing domain first.",
                    ], 403);
                }

                DomainBinding::create([
                    'license_id' => $license->id,
                    'domain' => $domain,
                    'ip_address' => $request->ip(),
                    'installed_version' => $validated['installed_version'] ?? '1.0.0',
                    'status' => 'active',
                    'activated_at' => now(),
                    'last_heartbeat_at' => now(),
                ]);
            } else {
                $binding->update([
                    'status' => 'active',
                    'ip_address' => $request->ip(),
                    'installed_version' => $validated['installed_version'] ?? $binding->installed_version,
                    'last_heartbeat_at' => now(),
                ]);
            }
        }

        // 3. Issue Signed Token
        $activationHash = $this->licenseService->generateActivationHash($license, $domain);
        $this->logAudit($license, 'verification_success', $domain, $request, 200);

        return response()->json([
            'valid' => true,
            'message' => 'License successfully verified.',
            'buyer' => $license->buyer_username ?? $license->buyer_email,
            'tier' => $license->tier,
            'channel' => $license->channel,
            'activation_hash' => $activationHash,
            'client_salt' => $license->client_secret_salt,
            'support_until' => $license->support_until?->toIso8601String(),
            'features' => [
                'mcp_gateway' => true,
                'multi_ai_provider' => true,
                'white_label' => in_array($license->tier, ['extended', 'agency', 'enterprise'], true),
            ],
        ]);
    }

    /**
     * POST /api/v1/license/heartbeat
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'license_key' => ['required', 'string'],
            'domain' => ['required', 'string'],
            'version' => ['nullable', 'string'],
        ]);

        $domain = $this->licenseService->normalizeDomain($validated['domain']);
        $license = License::where('license_key', $validated['license_key'])->first();

        if ($license === null || ! $license->isValid()) {
            return response()->json([
                'valid' => false,
                'message' => 'License no longer valid.',
            ], 403);
        }

        DomainBinding::where('license_id', $license->id)
            ->where('domain', $domain)
            ->update([
                'last_heartbeat_at' => now(),
                'installed_version' => $validated['version'] ?? null,
                'ip_address' => $request->ip(),
            ]);

        $this->licenseService->detectAbuse($license);
        $this->logAudit($license, 'heartbeat', $domain, $request, 200);

        return response()->json([
            'valid' => true,
            'status' => 'active',
        ]);
    }

    /**
     * POST /api/v1/license/deactivate
     */
    public function deactivate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'license_key' => ['required', 'string'],
            'domain' => ['required', 'string'],
        ]);

        $domain = $this->licenseService->normalizeDomain($validated['domain']);
        $license = License::where('license_key', $validated['license_key'])->first();

        if ($license === null) {
            return response()->json([
                'success' => false,
                'message' => 'License not found.',
            ], 404);
        }

        DomainBinding::where('license_id', $license->id)
            ->where('domain', $domain)
            ->update(['status' => 'deactivated']);

        $this->logAudit($license, 'deactivated', $domain, $request, 200);

        return response()->json([
            'success' => true,
            'message' => 'Domain unbound successfully. You may re-activate on a new host.',
        ]);
    }

    /**
     * GET /api/v1/license/check-update
     */
    public function checkUpdate(Request $request): JsonResponse
    {
        $productSlug = (string) $request->query('product', 'srs-genie');
        $currentVersion = (string) $request->query('current_version', '1.0.0');
        $licenseKey = (string) $request->query('license_key', '');

        $product = SoftwareProduct::where('slug', $productSlug)->first();
        if ($product === null) {
            return response()->json(['update_available' => false]);
        }

        $latestRelease = $product->latestRelease;
        if ($latestRelease === null) {
            return response()->json(['update_available' => false]);
        }

        $hasUpdate = version_compare($latestRelease->version, $currentVersion, '>');

        $downloadUrl = null;
        if ($hasUpdate) {
            $license = License::where('license_key', $licenseKey)->first();
            if ($license !== null && $license->isValid()) {
                $downloadUrl = $this->releaseManager->generateSignedDownloadUrl($latestRelease, $license, 60);
            }
        }

        return response()->json([
            'update_available' => $hasUpdate,
            'current_version' => $currentVersion,
            'latest_version' => $latestRelease->version,
            'min_php_version' => $latestRelease->min_php_version,
            'changelog' => $latestRelease->changelog,
            'sha256' => $latestRelease->sha256_checksum,
            'download_url' => $downloadUrl,
        ]);
    }

    /**
     * GET /api/v1/license/download-update
     */
    public function downloadUpdate(Request $request): BinaryFileResponse|JsonResponse
    {
        $releaseId = $request->query('release_id');
        $licenseKey = (string) $request->query('license_key');

        if (! $request->hasValidSignature() && empty($licenseKey)) {
            return response()->json(['error' => 'Invalid or expired download link.'], 403);
        }

        $license = License::where('license_key', $licenseKey)->first();
        if ($license === null || ! $license->isValid()) {
            return response()->json(['error' => 'Valid license required for download.'], 403);
        }

        $release = SoftwareRelease::where('id', $releaseId)
            ->where('is_active', true)
            ->first();

        if ($release === null) {
            return response()->json(['error' => 'Release not found.'], 404);
        }

        return $this->releaseManager->serveDownload($release, $license, $request);
    }

    private function logAudit(?License $license, string $event, string $domain, Request $request, int $code): void
    {
        LicenseAuditLog::create([
            'license_id' => $license?->id,
            'event' => $event,
            'domain' => $domain,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => $request->except(['password', 'client_salt']),
            'response_code' => $code,
        ]);
    }
}
