<?php

use App\Enums\BookingStatus;
use App\Mail;
use App\Models\BookingRequest;
use App\Models\Dentist;
use App\Models\Service;
use Illuminate\Support\Facades\Route;

/*
 * Email previews – ONLY on your own PC (APP_ENV=local). Never loaded on the live site.
 * Open https://senyum.test/dev/mail to see what each booking email looks like.
 */
Route::prefix('dev/mail')->group(function () {
    $sample = function (): BookingRequest {
        $booking = BookingRequest::make([
            'name' => 'Siti Aminah',
            'phone' => '012-345 6789',
            'email' => 'siti@example.com',
            'patient_type' => 'new',
            'preferred_date' => now()->addDays(3)->toDateString(),
            'preferred_time' => 'morning',
            'note' => 'Bringing my two children as well.',
            'status' => BookingStatus::Confirmed,
            'appointment_at' => now()->addDays(3)->setTime(10, 30),
            'alternative_at' => now()->addDays(4)->setTime(15, 0),
            'message_to_patient' => 'Please park at the back of the building.',
            'consented_at' => now(),
            'privacy_notice_version' => config('booking.privacy_notice_version'),
        ]);
        $booking->id = 0;
        $booking->reference = 'SNY-DEMO42';
        $booking->created_at = now();
        $booking->setRelation('service', Service::first());
        $booking->setRelation('dentist', Dentist::first());

        return $booking;
    };

    $emails = [
        'received' => Mail\BookingReceived::class,
        'new-alert' => Mail\NewBookingAlert::class,
        'confirmed' => Mail\BookingConfirmed::class,
        'alternative' => Mail\AlternativeTimeOffered::class,
        'declined' => Mail\BookingDeclined::class,
        'cancelled' => Mail\BookingCancelled::class,
        'reminder' => Mail\AppointmentReminder::class,
    ];

    Route::get('/', fn () => response(
        '<h1 style="font-family:sans-serif">Booking email previews</h1><ul style="font-family:sans-serif;line-height:2">'
        .collect($emails)->keys()->map(fn ($key) => "<li><a href=\"/dev/mail/{$key}\">{$key}</a></li>")->implode('')
        .'</ul>'
    ));

    Route::get('/{type}', function (string $type) use ($emails, $sample) {
        abort_unless(isset($emails[$type]), 404);

        return new $emails[$type]($sample());
    });
});
