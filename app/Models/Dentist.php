<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Database\Factories\DentistFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'position', 'qualifications', 'mdc_registration', 'bio', 'languages', 'photo', 'user_id', 'is_published', 'sort_order'])]
class Dentist extends Model
{
    /** @use HasFactory<DentistFactory> */
    use HasFactory, Publishable;

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** The staff login linked to this dentist (used later to show them their own bookings). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function initials(): string
    {
        return collect(explode(' ', preg_replace('/^(Dr\.?|Dr)\s+/i', '', $this->name)))
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }
}
