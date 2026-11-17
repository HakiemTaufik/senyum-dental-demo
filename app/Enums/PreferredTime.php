<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PreferredTime: string implements HasLabel
{
    case Morning = 'morning';
    case Afternoon = 'afternoon';
    case Any = 'any';

    public function getLabel(): string
    {
        return match ($this) {
            self::Morning => 'Morning (9 am – 12 pm)',
            self::Afternoon => 'Afternoon (2 pm – 6 pm)',
            self::Any => 'Any time',
        };
    }
}
