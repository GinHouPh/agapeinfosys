<?php

namespace App\Console\Commands;

use App\Builder\SchemaMigrator;
use App\Models\Page;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('builder:migrate-pages')]
#[Description('Carry every page forward to the current shape of its widgets')]
class MigratePages extends Command
{
    public function __construct(private readonly SchemaMigrator $migrator)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $migrated = 0;

        foreach (Page::query()->orderBy('id')->get() as $page) {
            $blocks = $page->schema['blocks'] ?? [];

            if (! is_array($blocks) || $blocks === []) {
                continue;
            }

            $migratedBlocks = $this->migrator->migrateTree($blocks);

            if ($migratedBlocks === $blocks) {
                continue;
            }

            $page->update(['schema' => ['blocks' => $migratedBlocks]]);

            $migrated++;

            $this->line('Migrated: '.$page->title);
        }

        $this->info($migrated.' page(s) migrated.');

        return self::SUCCESS;
    }
}
