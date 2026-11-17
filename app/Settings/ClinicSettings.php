<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/** Clinic-wide details the owner edits under Settings → Clinic details. */
class ClinicSettings extends Settings
{
    public string $name;

    public ?string $tagline;

    public ?string $phone;

    /** WhatsApp number in international format without "+" or spaces, e.g. 60123456789. */
    public ?string $whatsapp;

    public ?string $email;

    public ?string $address;

    public ?string $maps_url;

    /** Rows of days + hours, e.g. ["days" => "Monday – Friday", "hours" => "9 am – 6 pm"]. */
    public array $opening_hours;

    public ?string $facebook_url;

    public ?string $instagram_url;

    public ?string $registration_note;

    public bool $show_demo_notice;

    /** Switch the online booking form on/off (e.g. during holidays). */
    public bool $bookings_enabled;

    /** Where "new booking request" alerts are sent. */
    public ?string $booking_notification_email;

    /** PDPA: booking requests are deleted this many months after the requested date. */
    public int $booking_retention_months;

    /** How far ahead patients may ask for a date. */
    public int $booking_max_days_ahead;

    public ?string $dpo_name;

    public ?string $dpo_email;

    public ?string $dpo_phone;

    /** Google Analytics 4 ID. Analytics only load after the visitor accepts cookies. */
    public ?string $analytics_id;

    public function analyticsEnabled(): bool
    {
        return (bool) preg_match('/^G-[A-Z0-9]{4,20}$/', (string) $this->analytics_id);
    }

    public static function group(): string
    {
        return 'clinic';
    }

    public function whatsappUrl(?string $message = null): ?string
    {
        if (blank($this->whatsapp)) {
            return null;
        }

        return 'https://wa.me/'.preg_replace('/\D/', '', $this->whatsapp).($message ? '?text='.rawurlencode($message) : '');
    }

    public function phoneHref(): ?string
    {
        return blank($this->phone) ? null : 'tel:'.preg_replace('/[^\d+]/', '', $this->phone);
    }
}
