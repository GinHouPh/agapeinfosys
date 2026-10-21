<?php

namespace App\Console\Commands;

use App\Enums\PageStatus;
use App\Models\Page;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('pages:publish-scheduled')]
#[Description('Publish the pages whose scheduled date has arrived')]
class PublishScheduledPages extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $due = Page::query()
            ->where('status', PageStatus::Scheduled)
            ->whereNotNull('scheduled_for')
            ->where('scheduled_for', '<=', now())
            ->get();

        foreach ($due as $page) {
            $page->update(['status' => PageStatus::Published]);

            $this->line('Published: '.$page->title);
        }

        $this->info($due->count().' page(s) published.');

        return self::SUCCESS;
    }
}
