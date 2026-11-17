<?php

namespace App\Filament\Resources\BookingRequests\Tables;

use App\Enums\BookingStatus;
use App\Filament\Resources\BookingRequests\BookingRequestResource;
use App\Models\BookingRequest;
use App\Models\Dentist;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookingRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (BookingRequest $record): string => BookingRequestResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('reference')->searchable()->copyable()->fontFamily('mono')->toggleable(),
                TextColumn::make('name')->label('Patient')->searchable()->weight('medium')
                    ->description(fn (BookingRequest $record): string => $record->patient_type->getLabel()),
                TextColumn::make('service.name')->label('Service')->placeholder('General'),
                TextColumn::make('when')
                    ->label('Requested / booked for')
                    ->state(fn (BookingRequest $record): string => $record->appointment_at && $record->status === BookingStatus::Confirmed
                        ? $record->appointment_at->format('D j M, g:i a')
                        : $record->preferred_date->format('D j M').' · '.str($record->preferred_time->getLabel())->before(' ('))
                    ->description(fn (BookingRequest $record): ?string => $record->status === BookingStatus::Confirmed ? 'Confirmed time' : 'Preferred'),
                TextColumn::make('dentist.name')->label('Dentist')->placeholder('—')->toggleable(),
                TextColumn::make('created_at')->label('Received')->since()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(BookingStatus::class)->multiple(),
                SelectFilter::make('dentist_id')->label('Dentist')->options(fn () => Dentist::ordered()->pluck('name', 'id')),
            ])
            ->recordActions([
                ViewAction::make()->label('Open'),
            ])
            ->emptyStateHeading('No appointment requests here')
            ->emptyStateDescription('New requests from the website will appear here.');
    }
}
