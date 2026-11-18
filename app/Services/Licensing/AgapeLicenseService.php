<?php

namespace App\Services\Licensing;

use App\Models\License;
use App\Models\LicenseAuditLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AgapeLicenseService
{
    /**
     * Generate an Agape formatted license key: AGAPE-SRS-XXXX-XXXX-XXXX
     */
    public function generateLicenseKey(string $productPrefix = 'SRS'): string
    {
        $prefix = strtoupper(trim($productPrefix));

        do {
            $block1 = strtoupper(Str::random(4));
            $block2 = strtoupper(Str::random(4));
            $block3 = strtoupper(Str::random(4));
            $key = "AGAPE-{$prefix}-{$block1}-{$block2}-{$block3}";
        } while (License::where('license_key', $key)->exists());

        return $key;
    }

    /**
     * Normalize domain to apex or subdomain format (stripping protocol, port, path, www).
     */
    public function normalizeDomain(string $rawDomain): string
    {
        $raw = trim($rawDomain);
        if (! str_contains($raw, '://') && ! str_starts_with($raw, '//')) {
            $raw = 'http://'.$raw;
        }

        $host = parse_url($raw, PHP_URL_HOST) ?: $rawDomain;
        $host = strtolower(trim((string) $host));
        $host = preg_replace('/:\d+$/', '', $host);
        $host = preg_replace('/^www\./', '', $host);

        return (string) $host;
    }

    /**
     * Check if domain qualifies as a developer localhost or staging environment.
     */
    public function isLocalOrStaging(string $domain): bool
    {
        $domain = strtolower(trim($domain));
        $whitelist = ['localhost', '127.0.0.1', '::1'];

        if (in_array($domain, $whitelist, true)) {
            return true;
        }

        $patterns = [
            '/\.test$/',
            '/\.local$/',
            '/\.invalid$/',
            '/\.example$/',
            '/^dev\./',
            '/^staging\./',
            '/^test\./',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $domain)) {
                return true;
            }
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
            $license->expires_at ? $license->expires_at->timestamp : 'perpetual',
        ]);

        return hash_hmac('sha256', $payload, $license->client_secret_salt);
    }

    /**
     * Inspect audit logs for potential abuse and take defensive actions if necessary.
     */
    public function detectAbuse(License $license): bool
    {
        if ($license->status !== 'active') {
            return false;
        }

        $recentLogs = LicenseAuditLog::query()
            ->where('license_id', $license->id)
            ->where('created_at', '>=', now()->subHours(24))
            ->get();

        $distinctIps = $recentLogs->pluck('ip_address')->filter()->unique()->count();

        if ($distinctIps > 10 && $license->tier === 'regular') {
            $license->update(['status' => 'suspended']);
            Log::warning("License [{$license->license_key}] suspended due to excessive IP velocity ({$distinctIps} IPs in 24h).");

            return true;
        }

        return false;
    }
}
