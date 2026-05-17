<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-completar reservas confirmadas cuyo viaje fue hace más de 5 días
Schedule::command('reservas:auto-completar')->dailyAt('03:00');
