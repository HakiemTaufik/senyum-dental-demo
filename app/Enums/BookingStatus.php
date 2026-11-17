<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum BookingStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case AlternativeOffered = 'alternative_offered';
    case Confirmed = 'confirmed';
    case Declined = 'declined';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
    case NoShow = 'no_show';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'New – needs reply',
            self::AlternativeOffered => 'Other time offered',
            self::Confirmed => 'Confirmed',
            self::Declined => 'Declined',
            self::Cancelled => 'Cancelled',
            self::Completed => 'Attended',
            self::NoShow => 'Did not attend',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::AlternativeOffered => 'info',
            self::Confirmed => 'success',
            self::Declined, self::Cancelled, self::NoShow => 'danger',
            self::Completed => 'gray',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Pending => Heroicon::OutlinedBellAlert,
            self::AlternativeOffered => Heroicon::OutlinedArrowPath,
            self::Confirmed => Heroicon::OutlinedCheckCircle,
            self::Declined, self::Cancelled => Heroicon::OutlinedXCircle,
            self::Completed => Heroicon::OutlinedCheckBadge,
            self::NoShow => Heroicon::OutlinedNoSymbol,
        };
    }

    /** Still waiting for the clinic or patient to act. */
    public function isOpen(): bool
    {
        return in_array($this, [self::Pending, self::AlternativeOffered], true);
    }

    /** @return array<self> */
    public static function open(): array
    {
        return [self::Pending, self::AlternativeOffered];
    }
}
