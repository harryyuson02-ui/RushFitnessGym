<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('subscriptions:send-expiration-alerts')
    ->dailyAt('08:00')
    ->withoutOverlapping();
