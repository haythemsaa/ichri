<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your console based routes
| including Artisan commands and task scheduling. These routes are
| loaded by the Application's bootstrap/app.php file.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/*
|--------------------------------------------------------------------------
| Task Scheduling
|--------------------------------------------------------------------------
|
| Here you may schedule Artisan commands and other tasks to run at
| specific intervals. The scheduler will automatically execute these
| tasks based on the schedule you define.
|
*/

// Phase 2: Loyalty Program - Expire old loyalty points daily at 2 AM
Schedule::command('loyalty:expire-points')->dailyAt('02:00');

// Phase 2: Loyalty Program - Generate monthly report on first day of month
Schedule::command('loyalty:monthly-report')->monthlyOn(1, '09:00');

// Queue worker monitoring (if using supervisor, this ensures queue is always processing)
Schedule::command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping();

// Clean up old telescope entries (if telescope is installed)
// Schedule::command('telescope:prune')->daily();

// Clean up old failed jobs after 7 days
Schedule::command('queue:prune-failed --hours=168')->daily();
