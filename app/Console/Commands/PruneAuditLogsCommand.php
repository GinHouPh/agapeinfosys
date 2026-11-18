<?php

namespace App\Console\Commands;

use App\Models\LicenseAuditLog;
use Illuminate\Console\Command;

class PruneAuditLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:prune-logs {--days=90 : Days of audit logs to retain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune old license telemetry and audit logs';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $deleted = LicenseAuditLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Pruned {$deleted} license audit log records older than {$days} days.");

        return self::SUCCESS;
    }
}
