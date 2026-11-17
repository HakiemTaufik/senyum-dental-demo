<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\PatientType;
use App\Enums\PreferredTime;
use App\Models\BookingRequest;
use App\Models\Dentist;
use App\Models\Service;
use Illuminate\Database\Seeder;

/** Made-up appointment requests for practising in the admin panel. Local PC only. */
class DemoBookingsSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        $services = Service::pluck('id')->all();
        $dentists = Dentist::pluck('id')->all();

        $samples = [
            ['Nurul Huda', BookingStatus::Pending, 2, PreferredTime::Morning, PatientType::New, 'Prefer a female dentist if possible.'],
            ['Tan Wei Ming', BookingStatus::Pending, 4, PreferredTime::Afternoon, PatientType::Existing, null],
            ['Kavitha Ramasamy', BookingStatus::Pending, 6, PreferredTime::Any, PatientType::New, 'Bringing my 6-year-old son.'],
            ['Ahmad Faizal', BookingStatus::AlternativeOffered, 3, PreferredTime::Morning, PatientType::Existing, null],
            ['Lee Mei Ling', BookingStatus::Confirmed, 1, PreferredTime::Afternoon, PatientType::Existing, null],
            ['Muthu Krishnan', BookingStatus::Confirmed, 5, PreferredTime::Morning, PatientType::New, 'Need wheelchair access.'],
        ];

        foreach ($samples as $i => [$name, $status, $days, $time, $type, $note]) {
            $date = now()->addDays($days);

            BookingRequest::create([
                'service_id' => $services[$i % count($services)] ?? null,
                'name' => $name.' (demo)',
                'phone' => '012-000 00'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'email' => 'demo'.$i.'@example.com',
                'patient_type' => $type,
                'preferred_date' => $date->toDateString(),
                'preferred_time' => $time,
                'note' => $note,
                'consented_at' => now(),
                'privacy_notice_version' => config('booking.privacy_notice_version'),
                'status' => $status,
                'dentist_id' => $status === BookingStatus::Confirmed ? ($dentists[$i % count($dentists)] ?? null) : null,
                'appointment_at' => $status === BookingStatus::Confirmed ? $date->copy()->setTime(10 + $i % 5, 0) : null,
                'alternative_at' => $status === BookingStatus::AlternativeOffered ? $date->copy()->addDay()->setTime(15, 0) : null,
            ]);
        }
    }
}
