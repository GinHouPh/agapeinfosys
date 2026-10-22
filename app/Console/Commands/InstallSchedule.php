<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

#[Signature('schedule:install {--check : Report the current state without printing the install lines}')]
#[Description('Print the cron lines this server needs, and report whether the queue and scheduler are running')]
class InstallSchedule extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->reportState();

        if ($this->option('check')) {
            return self::SUCCESS;
        }

        $this->newLine();
        $this->line('Add these two lines to the control panel, under');
        $this->line('  Manage Hosting → (package) → Web Tools → Scheduled Tasks (Cron)');
        $this->newLine();
        $this->line($this->cronLine('schedule:run'));
        $this->line($this->cronLine('queue:work --stop-when-empty --max-time=55 --tries=3'));
        $this->newLine();
        $this->line('The first runs the scheduler — the pages that publish themselves, and the');
        $this->line('traffic prune. The second drains the queue — the entitlement webhooks.');
        $this->newLine();
        $this->line('A shared host cannot run a daemon, so the queue is driven by cron rather');
        $this->line('than a worker process. That is expected, not a fault.');

        return self::SUCCESS;
    }

    /**
     * Report what the scheduler and the queue are doing right now.
     */
    private function reportState(): void
    {
        $pending = $this->countRows('jobs');
        $failed = $this->countRows('failed_jobs');
        $oldest = $this->oldestPendingJob();

        $this->line('Current state');
        $this->line('  Scheduled tasks    '.$this->scheduledTaskCount());
        $this->line('  Pending jobs       '.$pending);
        $this->line('  Failed jobs        '.$failed);
        $this->line('  Oldest pending job '.($oldest ?? 'none'));

        if ($pending > 0 && $oldest !== null && $oldest->diffInMinutes(now()) >= 5) {
            $this->newLine();
            $this->warn('Jobs are sitting in the queue. Either the queue cron is missing, or it is not running.');
        }
    }

    /**
     * The number of scheduled tasks the app registers.
     */
    private function scheduledTaskCount(): int
    {
        $schedule = app(Schedule::class);

        return count($schedule->events());
    }

    /**
     * How many rows a table holds.
     */
    private function countRows(string $table): int
    {
        try {
            return DB::table($table)->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * When the oldest queued job was created, if there is one.
     */
    private function oldestPendingJob(): ?string
    {
        try {
            $created = DB::table('jobs')->min('created_at');
        } catch (\Throwable) {
            return null;
        }

        if ($created === null) {
            return null;
        }

        try {
            $moment = Carbon::createFromTimestamp((int) $created);

            return $moment->diffForHumans().' ('.$moment->toDateTimeString().')';
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * The cron line that runs one artisan command every minute.
     */
    private function cronLine(string $command): string
    {
        $binary = $this->phpBinary();

        return '* * * * * '.$binary.' '.$this->artisanPath().' '.$command.' >> /dev/null 2>&1';
    }

    /**
     * The path of the PHP binary to use.
     *
     * The running binary is always right, but a shared host resolves it to
     * a long version-prefixed path. A shorter name in /usr/bin pointing at
     * the same file reads better in a cron line, so that is preferred.
     */
    private function phpBinary(): string
    {
        $binary = PHP_BINARY;

        if ($binary === '' || ! is_file($binary)) {
            return 'php';
        }

        $real = realpath($binary) ?: $binary;

        foreach (glob('/usr/bin/php*') ?: [] as $candidate) {
            if ((realpath($candidate) ?: $candidate) === $real) {
                return $candidate;
            }
        }

        return $binary;
    }

    /**
     * The absolute path of the artisan file.
     */
    private function artisanPath(): string
    {
        return base_path('artisan');
    }
}
