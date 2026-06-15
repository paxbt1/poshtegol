<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('family-cup:football-data:sync-fixtures')->everySixHours();
Schedule::command('family-cup:football-data:sync-live')->everyTwoMinutes();
Schedule::command('family-cup:football-data:sync-results')->everyTenMinutes();

Schedule::command('family-cup:news:sync')->everySixHours()->withoutOverlapping();
