<?php

namespace App\Services\Licensing;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class EnvatoVerificationService
{
    private string $apiToken;

    public function __construct(?string $apiToken = null)
    {
        $this->apiToken = $apiToken ?? (string) config('services.envato.api_token', '');
    }

    /**
     * Verifies purchase code against Envato Market API v3.
     *
     * @return array<string, mixed>|null
     */
    public function verifyPurchaseCode(string $code): ?array
    {
        $code = trim($code);
        if ($code === '') {
            return null;
        }

        $cacheKey = "envato_code_verify_{$code}";

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($code) {
            try {
                $response = Http::withToken($this->apiToken)
                    ->withHeaders(['User-Agent' => 'AgapeLicensing/1.0'])
                    ->timeout(10)
                    ->get('https://api.envato.com/v3/market/author/sale', ['code' => $code]);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::warning('Envato verification failed', [
                    'code' => $code,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            } catch (Throwable $e) {
                Log::error('Envato API connection error: '.$e->getMessage());

                return null;
            }
        });
    }
}
