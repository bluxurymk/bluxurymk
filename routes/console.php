<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('bookings:expire-unpaid')->everyFifteenMinutes()->withoutOverlapping();

Schedule::command('bookings:send-reminders')->hourly()->withoutOverlapping();

/*
| Shared hosting has no long-running queue worker: the scheduler (cron, every
| minute) drains the queue and exits, so queued emails go out within a minute.
*/
Schedule::command('queue:work --stop-when-empty --tries=3 --max-time=50')
    ->everyMinute()
    ->withoutOverlapping();
