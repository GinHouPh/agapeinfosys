<?php

namespace App\Console\Commands;

use App\Models\SoftwareProduct;
use App\Models\SoftwareRelease;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PublishReleaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'release:publish 
                            {product : Software product slug (e.g. srs-genie)}
                            {version : Release version string (e.g. 2.6.0)}
                            {file : Path to release zip archive}
                            {--min-php=8.2.0 : Minimum PHP version}
                            {--changelog= : Changelog summary}
                            {--prerelease : Mark release as pre-release}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish a new software release zip from the command line';

    public function handle(): int
    {
        $productSlug = (string) $this->argument('product');
        $version = (string) $this->argument('version');
        $sourcePath = (string) $this->argument('file');

        $product = SoftwareProduct::where('slug', $productSlug)->first();

        if ($product === null) {
            $this->error("Product [{$productSlug}] not found.");

            return self::FAILURE;
        }

        if (! file_exists($sourcePath)) {
            $this->error("File [{$sourcePath}] does not exist.");

            return self::FAILURE;
        }

        $existing = SoftwareRelease::where('product_id', $product->id)
            ->where('version', $version)
            ->first();

        if ($existing !== null) {
            $this->error("Release [v{$version}] already exists for {$product->name}.");

            return self::FAILURE;
        }

        $cleanVersion = (string) preg_replace('/[^0-9a-zA-Z\.\-]/', '', $version);
        $targetFilename = "{$product->slug}-v{$cleanVersion}.zip";
        $targetDirectory = "releases/{$product->slug}";
        $targetPath = "{$targetDirectory}/{$targetFilename}";

        // Copy file to local private storage disk
        $stream = fopen($sourcePath, 'r');
        Storage::disk('local')->put($targetPath, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        $sha256 = hash_file('sha256', $sourcePath) ?: '';
        $fileSize = filesize($sourcePath) ?: 0;

        $release = SoftwareRelease::create([
            'product_id' => $product->id,
            'version' => $version,
            'min_php_version' => (string) $this->option('min-php'),
            'changelog' => (string) ($this->option('changelog') ?: "Release v{$version}"),
            'file_path' => $targetPath,
            'file_size_bytes' => $fileSize,
            'sha256_checksum' => $sha256,
            'is_prerelease' => (bool) $this->option('prerelease'),
            'is_active' => true,
        ]);

        $this->info("Release v{$version} published for {$product->name}!");
        $this->table(
            ['Version', 'File Path', 'Size', 'SHA-256 Checksum', 'Active'],
            [[
                $release->version,
                $release->file_path,
                number_format($release->file_size_bytes / 1024, 1).' KB',
                $release->sha256_checksum,
                $release->is_active ? 'Yes' : 'No',
            ]]
        );

        return self::SUCCESS;
    }
}
