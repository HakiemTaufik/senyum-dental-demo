<?php

namespace App\Filament\Resources\BookingRequests\Pages;

use App\Filament\Resources\BookingRequests\BookingActions;
use App\Filament\Resources\BookingRequests\BookingRequestResource;
use Filament\Resources\Pages\ViewRecord;

class ViewBookingRequest extends ViewRecord
{
    protected static string $resource = BookingRequestResource::class;

    public function getTitle(): string
    {
        return 'Request '.$this->getRecord()->reference;
    }

    protected function getHeaderActions(): array
    {
        return BookingActions::all();
    }
}
