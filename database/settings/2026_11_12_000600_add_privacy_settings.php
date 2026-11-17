<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Data Protection Officer: a named contact for privacy questions and requests (PDPA amendment 2024).
        $this->migrator->add('clinic.dpo_name', 'Data Protection Officer');
        $this->migrator->add('clinic.dpo_email', 'privacy@example.com');
        $this->migrator->add('clinic.dpo_phone', null);

        // Google Analytics 4 measurement ID (e.g. G-ABC123XYZ). Empty = no analytics and no cookie banner.
        $this->migrator->add('clinic.analytics_id', null);
    }
};
