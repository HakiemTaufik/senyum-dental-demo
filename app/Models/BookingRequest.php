<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\PatientType;
use App\Enums\PreferredTime;
use App\Enums\Role;
use App\Settings\ClinicSettings;
use Database\Factories\BookingRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * An appointment *request* sent from the website. It becomes an appointment
 * only when staff confirm it in the admin panel.
 */
#[Fillable([
    'service_id', 'name', 'phone', 'email', 'patient_type', 'preferred_date', 'preferred_time', 'note',
    'consented_at', 'privacy_notice_version',
    'status', 'dentist_id', 'appointment_at', 'alternative_at', 'message_to_patient', 'staff_notes',
    'handled_by', 'handled_at', 'reminder_sent_at',
])]
class BookingRequest extends Model
{
    /** @use HasFactory<BookingRequestFactory> */
    use HasFactory, LogsActivity, Prunable;

    protected $attributes = [
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'patient_type' => PatientType::class,
            'preferred_time' => PreferredTime::class,
            'preferred_date' => 'date',
            'appointment_at' => 'datetime',
            'alternative_at' => 'datetime',
            'consented_at' => 'datetime',
            'handled_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (BookingRequest $booking): void {
            $booking->reference ??= static::newReference();
        });
    }

    /** A short, hard-to-guess reference patients can quote, e.g. SNY-7K3D9Q. */
    public static function newReference(): string
    {
        // No 0/O or 1/I/L, so references are easy to read out over the phone.
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $reference = 'SNY-'.$code;
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function dentist(): BelongsTo
    {
        return $this->belongsTo(Dentist::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /** Dentists only see bookings assigned to them; other booking roles see everything. */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->role === Role::Dentist) {
            return $query->whereHas('dentist', fn (Builder $q) => $q->where('user_id', $user->id));
        }

        return $user->role->canViewAllBookings() ? $query : $query->whereRaw('1 = 0');
    }

    public function serviceName(): string
    {
        return $this->service?->name ?? 'General appointment';
    }

    /**
     * PDPA: don't keep personal data longer than needed.
     * Requests are deleted automatically N months after the requested/appointment date.
     */
    public function prunable(): Builder
    {
        $months = app(ClinicSettings::class)->booking_retention_months ?: 6;
        $cutoff = now()->subMonths($months)->startOfDay();

        return static::query()
            ->where('preferred_date', '<', $cutoff)
            ->where(fn (Builder $q) => $q->whereNull('appointment_at')->orWhere('appointment_at', '<', $cutoff));
    }

    /** Audit trail of who changed what. Patient contact details are deliberately not copied into the log. */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('bookings')
            ->logOnly(['status', 'dentist_id', 'appointment_at', 'alternative_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
