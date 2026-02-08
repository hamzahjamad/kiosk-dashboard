<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sync weather every 30 minutes
Schedule::command('sync:weather')->everyThirtyMinutes();

// Sync prayer times daily at midnight
Schedule::command('sync:prayer')->dailyAt('00:05');
