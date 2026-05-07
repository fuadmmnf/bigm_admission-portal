<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Queue Worker Schedule (cPanel-friendly)
|--------------------------------------------------------------------------
| Since cPanel hosting doesn't support persistent daemon processes, we
| run the queue worker in --stop-when-empty mode every minute via the
| Laravel scheduler. The scheduler itself is triggered by a single cron:
|
|   * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
|
*/
Schedule::command('queue:work --stop-when-empty --tries=3 --timeout=120')
    ->everyMinute()
    ->withoutOverlapping(5)
    ->runInBackground();

