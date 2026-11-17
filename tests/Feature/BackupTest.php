<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification;
use Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification;
use Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification;
use Tests\TestCase;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    private function scheduledBackupTasks(): array
    {
        return collect(app(Schedule::class)->events())
            ->filter(fn ($event) => str_contains($event->command ?? '', 'backup:'))
            ->filter(fn ($event) => $event->filtersPass(app()))
            ->map(fn ($event) => trim(str($event->command)->after('artisan')->replace(["'", '"'], '')))
            ->values()
            ->all();
    }

    public function test_backups_only_run_where_switched_on(): void
    {
        config(['backup.schedule_enabled' => false]);
        $this->assertSame([], $this->scheduledBackupTasks());

        config(['backup.schedule_enabled' => true]);
        $this->assertSame(['backup:clean', 'backup:run', 'backup:monitor'], $this->scheduledBackupTasks());
    }

    public function test_backups_are_encrypted_and_include_database_and_uploads(): void
    {
        $this->assertSame('default', config('backup.backup.encryption'));
        $this->assertContains(config('database.default'), config('backup.backup.source.databases'));
        $this->assertStringEndsWith('app'.DIRECTORY_SEPARATOR.'public', str_replace('/', DIRECTORY_SEPARATOR, config('backup.backup.source.files.include')[0]));
    }

    public function test_problems_are_emailed_but_success_is_not(): void
    {
        $channels = config('backup.notifications.notifications');

        $this->assertSame(['mail'], $channels[BackupHasFailedNotification::class]);
        $this->assertSame(['mail'], $channels[UnhealthyBackupWasFoundNotification::class]);
        $this->assertSame([], $channels[BackupWasSuccessfulNotification::class]);
    }

    public function test_restoring_over_the_live_site_needs_an_explicit_flag(): void
    {
        $this->app['env'] = 'production';

        $this->artisan('app:restore-backup', ['--force' => true])
            ->expectsOutputToContain('This is the LIVE site')
            ->assertFailed();
    }

    public function test_restore_explains_when_no_backup_exists(): void
    {
        $this->artisan('app:restore-backup', ['--disk' => 'local', '--force' => true])
            ->expectsOutputToContain('No backups found')
            ->assertFailed();
    }
}
