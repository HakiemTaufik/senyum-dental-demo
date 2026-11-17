<?php

namespace App\Mail;

use App\Settings\ClinicSettings;
use Illuminate\Mail\Mailables\Attachment;

/** Confirmation email with a calendar file (.ics) so the patient can add it to their phone calendar. */
class BookingConfirmed extends BookingMail
{
    protected function subjectLine(): string
    {
        return 'Your appointment is confirmed';
    }

    protected function template(): string
    {
        return 'confirmed';
    }

    public function attachments(): array
    {
        if (! $this->booking->appointment_at) {
            return [];
        }

        return [
            Attachment::fromData(fn (): string => $this->calendarFile(), 'appointment.ics')
                ->withMime('text/calendar; charset=UTF-8; method=PUBLISH'),
        ];
    }

    private function calendarFile(): string
    {
        $clinic = app(ClinicSettings::class);
        $start = $this->booking->appointment_at->copy()->utc();
        $end = $start->copy()->addMinutes($this->booking->service?->duration_minutes ?: 30);
        $escape = fn (?string $text): string => str_replace(['\\', ';', ',', "\r\n", "\n"], ['\\\\', '\;', '\,', '\n', '\n'], (string) $text);

        return implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Senyum Dental//Bookings//EN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:'.$this->booking->reference.'@'.parse_url(config('app.url'), PHP_URL_HOST),
            'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:'.$start->format('Ymd\THis\Z'),
            'DTEND:'.$end->format('Ymd\THis\Z'),
            'SUMMARY:'.$escape($this->booking->serviceName().' – '.$clinic->name),
            'LOCATION:'.$escape(str_replace("\n", ', ', (string) $clinic->address)),
            'DESCRIPTION:'.$escape('Booking reference '.$this->booking->reference.'. To change your appointment, call '.$clinic->phone.'.'),
            'END:VEVENT',
            'END:VCALENDAR',
        ])."\r\n";
    }
}
