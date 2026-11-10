<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwalkan kalkulasi insight bisnis AI setiap malam setelah jam operasional
Schedule::command('app:generate-ai-insights')->dailyAt('23:30');
