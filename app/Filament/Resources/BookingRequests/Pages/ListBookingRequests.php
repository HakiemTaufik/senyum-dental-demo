<?php

namespace App\Filament\Resources\BookingRequests\Pages;

use App\Enums\BookingStatus;
use App\Filament\Resources\BookingRequests\BookingRequestResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBookingRequests extends ListRecords
{
    protected static string $resource = BookingRequestResource::class;

    public function getTabs(): array
    {
        $base = fn () => BookingRequestResource::getEloquentQuery();

        return [
            'pending' => Tab::make('Needs reply')
                ->badge(fn () => $base()->where('status', BookingStatus::Pending)->count() ?: null)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', BookingStatus::Pending)->reorder('preferred_date')),
            'waiting' => Tab::make('Waiting for patient')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', BookingStatus::AlternativeOffered)),
            'upcoming' => Tab::make('Upcoming')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', BookingStatus::Confirmed)
                    ->where('appointment_at', '>=', now()->startOfDay())
                    ->reorder('appointment_at')),
            'all' => Tab::make('All'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'pending';
    }
}
