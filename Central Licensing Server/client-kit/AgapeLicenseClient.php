<?php
/**
 * Agape Central Licensing — Client Drop-in Verification & Auto-Update Kit
 *
 * Standalone, zero-dependency PHP 8.1+ client for CodeCanyon products & SaaS clients.
 * Handles server verification, offline HMAC validation, heartbeat sync, and 1-click updates.
 *
 * @version 1.0.0
 * @author AgapeInfosys Engineering Team
 * @link https://agapeinfosys.com
 */

class AgapeLicenseClient
{
    private string $serverUrl;
    private string $productSlug;
    private int $timeout;

    /**
     * @param string $productSlug The product slug registered on agapeinfosys.com (e.g. 'srs-genie')
     * @param string $serverUrl The central licensing server base URL (default: 'https://agapeinfosys.com/api/v1/license')
     * @param int $timeout HTTP request timeout in seconds
     */
    public function __construct(
        string $productSlug,
        string $serverUrl = 'https://agapeinfosys.com/api/v1/license',
        int $timeout = 10
    ) {
        $this->productSlug = trim($productSlug);
        $this->serverUrl = rtrim($serverUrl, '/');
        $this->timeout = $timeout;
    }

    /**
     * Check if the host domain is a local or staging developer environment.
     * Local environments are automatically exempted from seat counts.
     */
    public function isLocalhost(?string $host = null): bool
    {
        $domain = $this->normalizeDomain($host ?: ($_SERVER['HTTP_HOST'] ?? 'localhost'));

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
     * Clean and normalize a raw URL or domain string down to apex or subdomain format.
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
     * Activate and bind license key to the current host with Central Server.
     *
     * @return array{valid: bool, message: string, activation_hash?: string, client_salt?: string, tier?: string, buyer?: string}
     */
    public function activate(string $licenseKey, ?string $domain = null, string $installedVersion = '1.0.0'): array
    {
        $cleanDomain = $this->normalizeDomain($domain ?: ($_SERVER['HTTP_HOST'] ?? 'localhost'));

        $payload = [
            'license_key' => trim($licenseKey),
            'domain' => $cleanDomain,
            'product_slug' => $this->productSlug,
            'installed_version' => $installedVersion,
        ];

        $response = $this->post('/verify', $payload);

        if (! $response || empty($response['valid'])) {
            return [
                'valid' => false,
                'message' => $response['message'] ?? 'Unable to verify license key with licensing server.',
            ];
        }

        return $response;
    }

    /**
     * Perform instantaneous offline validation (< 0.2ms) using the cryptographic HMAC hash.
     */
    public function verifyLocal(
        string $licenseKey,
        string $domain,
        string $tier,
        string $salt,
        string $expectedHash,
        ?int $expiresAtTimestamp = null
    ): bool {
        if ($this->isLocalhost($domain)) {
            return true;
        }

        $payload = implode('::', [
            trim($licenseKey),
            $this->normalizeDomain($domain),
            $tier,
            $expiresAtTimestamp ? $expiresAtTimestamp : 'perpetual',
        ]);

        $calculated = hash_hmac('sha256', $payload, $salt);

        return hash_equals($expectedHash, $calculated);
    }

    /**
     * Ping the licensing server for periodic heartbeat (recommend: run once every 7 days).
     */
    public function heartbeat(string $licenseKey, ?string $domain = null, ?string $version = null): bool
    {
        $cleanDomain = $this->normalizeDomain($domain ?: ($_SERVER['HTTP_HOST'] ?? 'localhost'));

        $response = $this->post('/heartbeat', [
            'license_key' => trim($licenseKey),
            'domain' => $cleanDomain,
            'version' => $version,
        ]);

        return ! empty($response['valid']);
    }

    /**
     * Release and unbind the domain from the license to allow migration to a new server.
     */
    public function deactivate(string $licenseKey, ?string $domain = null): bool
    {
        $cleanDomain = $this->normalizeDomain($domain ?: ($_SERVER['HTTP_HOST'] ?? 'localhost'));

        $response = $this->post('/deactivate', [
            'license_key' => trim($licenseKey),
            'domain' => $cleanDomain,
        ]);

        return ! empty($response['success']);
    }

    /**
     * Check if a newer version update is available.
     *
     * @return array{update_available: bool, latest_version?: string, changelog?: string, sha256?: string, download_url?: string}
     */
    public function checkUpdate(string $licenseKey, string $currentVersion): array
    {
        $query = http_build_query([
            'product' => $this->productSlug,
            'current_version' => $currentVersion,
            'license_key' => trim($licenseKey),
        ]);

        $response = $this->get('/check-update?'.$query);

        return $response ?: ['update_available' => false];
    }

    /**
     * Download release archive zip, verify SHA-256 checksum, and extract to destination.
     */
    public function downloadAndApplyUpdate(string $downloadUrl, string $expectedSha256, string $destinationDir): bool
    {
        if (! class_exists('ZipArchive')) {
            throw new RuntimeException('ZipArchive extension is required on PHP for 1-click updates.');
        }

        $tempZip = tempnam(sys_get_temp_dir(), 'update_').'.zip';

        // 1. Download file stream
        $fp = fopen($tempZip, 'w+');
        $ch = curl_init($downloadUrl);
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_FAILONERROR => true,
            CURLOPT_USERAGENT => 'AgapeLicensingClient/1.0',
        ]);

        $success = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if (! $success || $httpCode !== 200) {
            @unlink($tempZip);
            return false;
        }

        // 2. Cryptographic Checksum Integrity Check
        $downloadedSha256 = hash_file('sha256', $tempZip);
        if (! hash_equals($expectedSha256, (string) $downloadedSha256)) {
            @unlink($tempZip);
            throw new RuntimeException("Checksum mismatch: Expected {$expectedSha256}, got {$downloadedSha256}");
        }

        // 3. Extract Archive
        $zip = new ZipArchive();
        if ($zip->open($tempZip) === true) {
            $zip->extractTo($destinationDir);
            $zip->close();
            @unlink($tempZip);
            return true;
        }

        @unlink($tempZip);
        return false;
    }

    /**
     * Execute internal HTTP POST request.
     */
    private function post(string $endpoint, array $data): ?array
    {
        $url = $this->serverUrl.'/'.ltrim($endpoint, '/');
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: AgapeLicensingClient/1.0',
            ],
        ]);

        $raw = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (! $raw || $code >= 500) {
            return null;
        }

        return json_decode($raw, true);
    }

    /**
     * Execute internal HTTP GET request.
     */
    private function get(string $endpoint): ?array
    {
        $url = $this->serverUrl.'/'.ltrim($endpoint, '/');
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: AgapeLicensingClient/1.0',
            ],
        ]);

        $raw = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (! $raw || $code >= 500) {
            return null;
        }

        return json_decode($raw, true);
    }
}
