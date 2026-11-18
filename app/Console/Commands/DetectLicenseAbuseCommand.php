<?php

namespace App\Console\Commands;

use App\Models\License;
use App\Models\LicenseAuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DetectLicenseAbuseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:detect-abuse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan licensing telemetry logs for piracy, IP velocity anomalies, and domain flooding';

    public function handle(): int
    {
        $this->info('Scanning licensing audit logs for anomalies…');

        $activeLicenses = License::query()
            ->where('status', 'active')
            ->where('tier', 'regular')
            ->with('domainBindings')
            ->get();

        $suspendedCount = 0;

        foreach ($activeLicenses as $license) {
            // 1. IP Velocity check: > 10 distinct IPs in 24 hours
            $distinctIps = LicenseAuditLog::query()
                ->where('license_id', $license->id)
                ->where('created_at', '>=', now()->subHours(24))
                ->distinct('ip_address')
                ->count('ip_address');

            if ($distinctIps > 10) {
                $license->update(['status' => 'suspended']);
                $license->domainBindings()->update(['status' => 'deactivated']);

                LicenseAuditLog::create([
                    'license_id' => $license->id,
                    'event' => 'abuse_ip_velocity_auto_suspend',
                    'domain' => $license->domainBindings->first()?->domain,
                    'ip_address' => '0.0.0.0',
                    'payload' => ['distinct_ips_24h' => $distinctIps],
                    'response_code' => 403,
                ]);

                Log::warning("License [{$license->license_key}] auto-suspended due to high IP velocity ({$distinctIps} IPs in 24h).");
                $this->warn("Suspended [{$license->license_key}]: {$distinctIps} distinct IPs in 24 hours.");
                $suspendedCount++;

                continue;
            }

            // 2. Domain Flooding check: > 5 domain limit breaches in 48 hours
            $limitBreaches = LicenseAuditLog::query()
                ->where('license_id', $license->id)
                ->where('event', 'domain_limit_exceeded')
                ->where('created_at', '>=', now()->subHours(48))
                ->count();

            if ($limitBreaches >= 5) {
                $license->update(['status' => 'suspended']);
                $license->domainBindings()->update(['status' => 'deactivated']);

                LicenseAuditLog::create([
                    'license_id' => $license->id,
                    'event' => 'abuse_domain_flooding_auto_suspend',
                    'domain' => $license->domainBindings->first()?->domain,
                    'ip_address' => '0.0.0.0',
                    'payload' => ['limit_breaches_48h' => $limitBreaches],
                    'response_code' => 403,
                ]);

                Log::warning("License [{$license->license_key}] auto-suspended due to repeated domain flooding ({$limitBreaches} breaches).");
                $this->warn("Suspended [{$license->license_key}]: {$limitBreaches} domain breaches in 48 hours.");
                $suspendedCount++;
            }
        }

        if ($suspendedCount === 0) {
            $this->info('No license abuse or piracy velocity anomalies detected.');
        } else {
            $this->warn("Total licenses suspended due to piracy/abuse: {$suspendedCount}");
        }

        return self::SUCCESS;
    }
}
