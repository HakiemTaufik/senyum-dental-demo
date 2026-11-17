<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

/**
 * The four staff roles. Each role's abilities live here in one place,
 * so it is easy to see (and change) who can do what.
 */
enum Role: string implements HasColor, HasDescription, HasLabel
{
    case Owner = 'owner';
    case FrontDesk = 'front_desk';
    case ContentEditor = 'content_editor';
    case Dentist = 'dentist';

    public function getLabel(): string
    {
        return match ($this) {
            self::Owner => 'Owner / admin',
            self::FrontDesk => 'Front desk',
            self::ContentEditor => 'Content editor',
            self::Dentist => 'Dentist',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::Owner => 'Everything: website content, bookings, settings and staff accounts.',
            self::FrontDesk => 'Sees and manages booking requests. Cannot edit the website or settings.',
            self::ContentEditor => 'Edits pages, services, dentists and FAQs. Cannot see patient bookings.',
            self::Dentist => 'Views booking requests assigned to them (read-only).',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Owner => 'danger',
            self::FrontDesk => 'info',
            self::ContentEditor => 'success',
            self::Dentist => 'warning',
        };
    }

    public function canManageStaff(): bool
    {
        return $this === self::Owner;
    }

    public function canManageSettings(): bool
    {
        return $this === self::Owner;
    }

    public function canEditContent(): bool
    {
        return in_array($this, [self::Owner, self::ContentEditor], true);
    }

    public function canManageBookings(): bool
    {
        return in_array($this, [self::Owner, self::FrontDesk], true);
    }

    /** Dentists may view only bookings assigned to them. */
    public function canViewAllBookings(): bool
    {
        return in_array($this, [self::Owner, self::FrontDesk], true);
    }
}
