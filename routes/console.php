<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Central Licensing Automated Monitoring & Maintenance
*/
Schedule::command('license:detect-abuse')->hourly();
Schedule::command('license:prune-logs --days=90')->daily();
