<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Mail\AlternativeTimeOffered;
use App\Mail\AppointmentReminder;
use App\Mail\BookingCancelled;
use App\Mail\BookingConfirmed;
use App\Mail\BookingDeclined;
use App\Mail\BookingReceived;
use App\Mail\NewBookingAlert;
use App\Models\BookingRequest;
use App\Models\User;
use App\Settings\ClinicSettings;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Every step of a booking request in one place: what changes, and which email is sent.
 */
class BookingWorkflow
{
    public function __construct(private ClinicSettings $clinic) {}

    /** A patient sent the website form. */
    public function submit(array $data): BookingRequest
    {
        return DB::transaction(function () use ($data): BookingRequest {
            $booking = BookingRequest::create([
                ...$data,
                'status' => BookingStatus::Pending,
                'consented_at' => now(),
                'privacy_notice_version' => config('booking.privacy_notice_version'),
            ]);

            Mail::to($booking->email, $booking->name)->queue(new BookingReceived($booking));

            if (filled($this->clinic->booking_notification_email)) {
                Mail::to($this->clinic->booking_notification_email)->queue(new NewBookingAlert($booking));
            }

            return $booking;
        });
    }

    public function confirm(BookingRequest $booking, CarbonInterface $at, ?int $dentistId, ?string $message, User $by): void
    {
        $this->update($booking, $by, [
            'status' => BookingStatus::Confirmed,
            'appointment_at' => $at,
            'alternative_at' => null,
            'dentist_id' => $dentistId,
            'message_to_patient' => $message,
            'reminder_sent_at' => null,
        ]);

        Mail::to($booking->email, $booking->name)->queue(new BookingConfirmed($booking));
    }

    public function offerAlternative(BookingRequest $booking, CarbonInterface $at, ?string $message, User $by): void
    {
        $this->update($booking, $by, [
            'status' => BookingStatus::AlternativeOffered,
            'alternative_at' => $at,
            'message_to_patient' => $message,
        ]);

        Mail::to($booking->email, $booking->name)->queue(new AlternativeTimeOffered($booking));
    }

    public function decline(BookingRequest $booking, ?string $message, User $by): void
    {
        $this->update($booking, $by, [
            'status' => BookingStatus::Declined,
            'message_to_patient' => $message,
        ]);

        Mail::to($booking->email, $booking->name)->queue(new BookingDeclined($booking));
    }

    public function cancel(BookingRequest $booking, ?string $message, bool $notifyPatient, User $by): void
    {
        $this->update($booking, $by, [
            'status' => BookingStatus::Cancelled,
            'message_to_patient' => $message,
        ]);

        if ($notifyPatient) {
            Mail::to($booking->email, $booking->name)->queue(new BookingCancelled($booking));
        }
    }

    /** After the visit: attended or did not attend. No email. */
    public function markOutcome(BookingRequest $booking, BookingStatus $outcome, User $by): void
    {
        abort_unless(in_array($outcome, [BookingStatus::Completed, BookingStatus::NoShow], true), 422);

        $this->update($booking, $by, ['status' => $outcome]);
    }

    /** Sends tomorrow's reminders. Runs automatically every day (see routes/console.php). */
    public function sendReminders(): int
    {
        $bookings = BookingRequest::query()
            ->where('status', BookingStatus::Confirmed)
            ->whereNull('reminder_sent_at')
            ->whereBetween('appointment_at', [now()->addDay()->startOfDay(), now()->addDay()->endOfDay()])
            ->get();

        foreach ($bookings as $booking) {
            Mail::to($booking->email, $booking->name)->queue(new AppointmentReminder($booking));
            $booking->forceFill(['reminder_sent_at' => now()])->saveQuietly();
        }

        return $bookings->count();
    }

    private function update(BookingRequest $booking, User $by, array $changes): void
    {
        $booking->fill([...$changes, 'handled_by' => $by->id, 'handled_at' => now()])->save();
    }
}
