<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('clinic.name', 'Senyum Dental Clinic');
        $this->migrator->add('clinic.tagline', 'Friendly, unhurried dental care for the whole family.');
        $this->migrator->add('clinic.phone', '+60 3-0000 0000');
        $this->migrator->add('clinic.whatsapp', '60100000000');
        $this->migrator->add('clinic.email', 'hello@example.com');
        $this->migrator->add('clinic.address', "No. 1, Jalan Contoh 1/1\nTaman Contoh\n40000 Shah Alam, Selangor");
        $this->migrator->add('clinic.maps_url', null);
        $this->migrator->add('clinic.opening_hours', [
            ['days' => 'Monday – Friday', 'hours' => '9:00 am – 6:00 pm'],
            ['days' => 'Saturday', 'hours' => '9:00 am – 1:00 pm'],
            ['days' => 'Sunday & public holidays', 'hours' => 'Closed'],
        ]);
        $this->migrator->add('clinic.facebook_url', null);
        $this->migrator->add('clinic.instagram_url', null);
        $this->migrator->add('clinic.registration_note', 'Demo only. A real clinic shows its registration under the Private Healthcare Facilities and Services Act 1998 here.');
        $this->migrator->add('clinic.show_demo_notice', true);
    }
};
