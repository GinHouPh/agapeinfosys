<?php

namespace App\Console\Commands;

use App\Models\PageView;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('traffic:prune {--days=90 : How many days of traffic to keep} {--dry-run : Report what would be removed, without removing it}')]
#[Description('Remove page views older than the retention window')]
class PrunePageViews extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $stale = PageView::query()->where('created_at', '<', $cutoff);

        $count = (clone $stale)->count();

        if ($count === 0) {
            $this->info('Nothing to prune — every view is inside the '.$days.' day window.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->line($count.' view(s) are older than '.$cutoff->toDateTimeString().'.');

            return self::SUCCESS;
        }

        $stale->delete();

        $this->info('Pruned '.$count.' page view(s) older than '.$days.' days.');

        return self::SUCCESS;
    }
}
