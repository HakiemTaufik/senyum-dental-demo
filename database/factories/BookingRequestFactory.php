<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Enums\PatientType;
use App\Enums\PreferredTime;
use App\Models\BookingRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRequest>
 */
class BookingRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => '012-345 6789',
            'email' => fake()->unique()->safeEmail(),
            'patient_type' => PatientType::New,
            'preferred_date' => now()->addDays(3)->toDateString(),
            'preferred_time' => PreferredTime::Morning,
            'note' => null,
            'consented_at' => now(),
            'privacy_notice_version' => '2026-11',
            'status' => BookingStatus::Pending,
        ];
    }

    public function confirmed(?\DateTimeInterface $at = null): static
    {
        return $this->state(fn () => [
            'status' => BookingStatus::Confirmed,
            'appointment_at' => $at ?? now()->addDays(3)->setTime(10, 0),
        ]);
    }
}
