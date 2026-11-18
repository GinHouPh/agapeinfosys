<?php

namespace App\Console\Commands;

use App\Mail\LicenseIssuedMail;
use App\Models\License;
use App\Models\SoftwareProduct;
use App\Services\Licensing\AgapeLicenseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class IssueLicenseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:issue 
                            {product : The slug of the software product (e.g. srs-genie)}
                            {email : Buyer email address}
                            {--tier=regular : License tier (regular, extended, agency, enterprise, developer)}
                            {--channel=agape_direct : Sales channel (agape_direct, partner, envato)}
                            {--username= : Buyer display name or company}
                            {--domains= : Custom max domains count}
                            {--months=6 : Support duration in months}
                            {--send-mail : Send license key email to buyer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Issue a new license key from the command line';

    public function handle(AgapeLicenseService $licenseService): int
    {
        $productSlug = (string) $this->argument('product');
        $email = (string) $this->argument('email');
        $tier = (string) $this->option('tier');
        $channel = (string) $this->option('channel');
        $username = $this->option('username') ?: Str::before($email, '@');
        $months = (int) $this->option('months');

        $product = SoftwareProduct::where('slug', $productSlug)->first();

        if ($product === null) {
            $this->error("Software product [{$productSlug}] not found.");

            return self::FAILURE;
        }

        $defaultMaxDomains = match ($tier) {
            'agency' => 5,
            'enterprise' => 999,
            'extended' => 3,
            default => 1,
        };

        $maxDomains = $this->option('domains') ? (int) $this->option('domains') : $defaultMaxDomains;
        $productPrefix = strtoupper(substr($product->slug, 0, 3));
        $licenseKey = $licenseService->generateLicenseKey($productPrefix);

        $license = License::create([
            'product_id' => $product->id,
            'license_key' => $licenseKey,
            'channel' => $channel,
            'buyer_email' => $email,
            'buyer_username' => $username,
            'tier' => $tier,
            'max_domains' => $maxDomains,
            'support_until' => now()->addMonths($months),
            'expires_at' => null,
            'status' => 'active',
            'client_secret_salt' => Str::random(32),
        ]);

        $this->info('License issued successfully!');
        $this->table(
            ['Key', 'Product', 'Tier', 'Channel', 'Buyer', 'Max Domains', 'Support Until'],
            [[
                $license->license_key,
                $product->name,
                $license->tier,
                $license->channel,
                "{$license->buyer_username} <{$license->buyer_email}>",
                $license->max_domains,
                $license->support_until->format('d M Y'),
            ]]
        );

        if ($this->option('send-mail')) {
            try {
                Mail::to($email)->send(new LicenseIssuedMail($license));
                $this->info("Email dispatched to {$email}.");
            } catch (\Throwable $e) {
                $this->warn("Failed to send email: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
