<?php

use App\Models\BookingRequest;
use App\Services\BookingWorkflow;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
 * Automatic background jobs. On the live server, one cron job runs
 * `php artisan schedule:run` every minute, and Laravel runs these at the right times.
 */

// Send queued emails. Shared hosting can't keep a worker running forever,
// so a short-lived worker starts every minute and stops when the queue is empty.
Schedule::command('queue:work --stop-when-empty --tries=3 --max-time=50')
    ->everyMinute()
    ->withoutOverlapping();

// Appointment reminders for tomorrow, sent every morning.
Artisan::command('bookings:send-reminders', function (BookingWorkflow $workflow) {
    $this->info($workflow->sendReminders().' reminder(s) queued.');
})->purpose('Email patients a reminder the day before their appointment');

Schedule::command('bookings:send-reminders')->dailyAt('09:00');

// PDPA: delete old booking requests (after the retention period set in Settings).
Schedule::command('model:prune', ['--model' => [BookingRequest::class]])->dailyAt('03:00');

// Housekeeping: keep the activity log from growing forever (365 days).
Schedule::command('activitylog:clean')->weekly();

// Backups (live site only, see BACKUP_SCHEDULE in .env): tidy old ones, make a new
// encrypted backup, then check every morning that a recent, healthy backup exists.
// Problems are emailed to BACKUP_NOTIFY_EMAIL.
Schedule::command('backup:clean')->dailyAt('01:30')->when(fn () => config('backup.schedule_enabled'));
Schedule::command('backup:run')->dailyAt('02:00')->when(fn () => config('backup.schedule_enabled'));
Schedule::command('backup:monitor')->dailyAt('08:00')->when(fn () => config('backup.schedule_enabled'));
