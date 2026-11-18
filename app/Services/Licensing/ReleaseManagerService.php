<?php

namespace App\Services\Licensing;

use App\Models\License;
use App\Models\LicenseAuditLog;
use App\Models\SoftwareProduct;
use App\Models\SoftwareRelease;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReleaseManagerService
{
    private string $disk;

    public function __construct()
    {
        $this->disk = (string) config('services.licensing.release_disk', 'local');
    }

    /**
     * Store uploaded zip release archive and calculate size + sha256 checksum.
     *
     * @return array{file_path: string, file_size_bytes: int, sha256_checksum: string}
     */
    public function storeReleaseArchive(UploadedFile $file, SoftwareProduct $product, string $version): array
    {
        $cleanVersion = (string) preg_replace('/[^0-9a-zA-Z\.\-]/', '', $version);
        $filename = "{$product->slug}-v{$cleanVersion}.zip";
        $directory = "releases/{$product->slug}";

        $path = $file->storeAs($directory, $filename, $this->disk);
        $sha256 = hash_file('sha256', $file->getRealPath()) ?: '';
        $fileSizeBytes = $file->getSize();

        return [
            'file_path' => $path,
            'file_size_bytes' => $fileSizeBytes,
            'sha256_checksum' => $sha256,
        ];
    }

    /**
     * Check if physical release package exists and verify SHA-256 integrity.
     */
    public function verifyReleaseIntegrity(SoftwareRelease $release): bool
    {
        if (Storage::disk($this->disk)->exists($release->file_path)) {
            $fullPath = Storage::disk($this->disk)->path($release->file_path);
            $calculatedHash = hash_file('sha256', $fullPath);

            return hash_equals($release->sha256_checksum, (string) $calculatedHash);
        }

        $legacyPath = storage_path('app/'.$release->file_path);
        if (file_exists($legacyPath)) {
            $calculatedHash = hash_file('sha256', $legacyPath);

            return hash_equals($release->sha256_checksum, (string) $calculatedHash);
        }

        return false;
    }

    /**
     * Generate secure, temporary signed download URL for an active license.
     */
    public function generateSignedDownloadUrl(SoftwareRelease $release, License $license, int $expiresInMinutes = 60): string
    {
        return URL::temporarySignedRoute(
            'api.license.download',
            now()->addMinutes($expiresInMinutes),
            [
                'release_id' => $release->id,
                'license_key' => $license->license_key,
            ]
        );
    }

    /**
     * Serve authenticated download stream with proper binary headers and audit logging.
     */
    public function serveDownload(SoftwareRelease $release, License $license, Request $request): BinaryFileResponse
    {
        LicenseAuditLog::create([
            'license_id' => $license->id,
            'event' => 'update_download',
            'domain' => $request->query('domain') ?: $request->ip(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => [
                'release_id' => $release->id,
                'version' => $release->version,
                'product' => $release->product?->slug,
            ],
            'response_code' => 200,
        ]);

        $productSlug = $release->product?->slug ?? 'release';
        $filename = "{$productSlug}-v{$release->version}.zip";

        if (Storage::disk($this->disk)->exists($release->file_path)) {
            return response()->download(
                Storage::disk($this->disk)->path($release->file_path),
                $filename,
                [
                    'Content-Type' => 'application/zip',
                    'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                    'X-Checksum-SHA256' => $release->sha256_checksum,
                ]
            );
        }

        $fallbackPath = storage_path('app/'.$release->file_path);
        if (file_exists($fallbackPath)) {
            return response()->download(
                $fallbackPath,
                $filename,
                [
                    'Content-Type' => 'application/zip',
                    'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                    'X-Checksum-SHA256' => $release->sha256_checksum,
                ]
            );
        }

        abort(404, 'Release file archive not found on server.');
    }
}
