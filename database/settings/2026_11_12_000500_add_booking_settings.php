<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('clinic.bookings_enabled', true);
        $this->migrator->add('clinic.booking_notification_email', 'hello@example.com');
        $this->migrator->add('clinic.booking_retention_months', 6);
        $this->migrator->add('clinic.booking_max_days_ahead', 90);
    }
};
