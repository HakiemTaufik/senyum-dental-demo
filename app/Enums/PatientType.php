<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PatientType: string implements HasLabel
{
    case New = 'new';
    case Existing = 'existing';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'New patient',
            self::Existing => 'Existing patient',
        };
    }
}
