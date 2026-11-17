<?php

namespace App\Filament\Resources\BookingRequests\Schemas;

use App\Enums\BookingStatus;
use App\Models\BookingRequest;
use App\Settings\ClinicSettings;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Activitylog\Models\Activity;

class BookingRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Request')
                            ->columnSpan(2)
                            ->columns(2)
                            ->schema([
                                TextEntry::make('status')->badge(),
                                TextEntry::make('reference')->copyable()->fontFamily('mono'),
                                TextEntry::make('service.name')->label('Service')->placeholder('General check-up or advice'),
                                TextEntry::make('patient_type')->label('Patient'),
                                TextEntry::make('preferred_date')->label('Preferred date')->date('l, j F Y'),
                                TextEntry::make('preferred_time')->label('Preferred time'),
                                TextEntry::make('note')->label('Patient note')->placeholder('—')->columnSpanFull(),
                            ]),
                        Section::make('Contact')
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('name'),
                                TextEntry::make('phone')
                                    ->url(fn (BookingRequest $record): string => 'tel:'.preg_replace('/[^\d+]/', '', $record->phone))
                                    ->color('primary'),
                                TextEntry::make('whatsapp')
                                    ->label('WhatsApp')
                                    ->state('Open chat')
                                    ->url(fn (BookingRequest $record): string => 'https://wa.me/'.self::whatsappNumber($record->phone).'?text='.rawurlencode('Hi '.$record->name.', this is '.app(ClinicSettings::class)->name.' about your appointment request '.$record->reference.'.'), shouldOpenInNewTab: true)
                                    ->color('primary'),
                                TextEntry::make('email')
                                    ->url(fn (BookingRequest $record): string => 'mailto:'.$record->email)
                                    ->color('primary'),
                            ]),
                    ]),
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Appointment')
                            ->columnSpan(2)
                            ->columns(2)
                            ->schema([
                                TextEntry::make('appointment_at')->label('Confirmed time')->dateTime('l, j F Y, g:i a')->placeholder('Not confirmed yet')
                                    ->visible(fn (BookingRequest $record): bool => $record->status !== BookingStatus::AlternativeOffered),
                                TextEntry::make('alternative_at')->label('Time offered to patient')->dateTime('l, j F Y, g:i a')
                                    ->visible(fn (BookingRequest $record): bool => $record->status === BookingStatus::AlternativeOffered),
                                TextEntry::make('dentist.name')->label('Dentist')->placeholder('Any available'),
                                TextEntry::make('message_to_patient')->label('Last message to patient')->placeholder('—')->columnSpanFull(),
                                TextEntry::make('staff_notes')->label('Staff notes')->placeholder('—')->columnSpanFull(),
                            ]),
                        Section::make('Record')
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('created_at')->label('Received')->dateTime('j M Y, g:i a'),
                                TextEntry::make('handler.name')->label('Last handled by')->placeholder('—'),
                                TextEntry::make('consented_at')->label('Privacy consent given')->dateTime('j M Y, g:i a')
                                    ->helperText(fn (BookingRequest $record): string => 'Privacy notice version '.$record->privacy_notice_version),
                                TextEntry::make('history')
                                    ->label('History')
                                    ->state(fn (BookingRequest $record): array => self::history($record))
                                    ->listWithLineBreaks()
                                    ->placeholder('—'),
                            ]),
                    ]),
            ]);
    }

    /** Converts a Malaysian local number (012-345 6789) into WhatsApp's format (60123456789). */
    public static function whatsappNumber(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        return str_starts_with($digits, '0') ? '6'.$digits : $digits;
    }

    /** @return array<string> */
    private static function history(BookingRequest $record): array
    {
        return $record->activitiesAsSubject()
            ->with('causer')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function (Activity $activity): string {
                $status = $activity->attribute_changes['attributes']['status'] ?? null;
                $label = $status ? BookingStatus::tryFrom($status)?->getLabel() : null;
                $what = match (true) {
                    $activity->event === 'created' => 'Received',
                    $label !== null => $label,
                    default => 'Updated',
                };

                return $activity->created_at->format('j M, g:i a').' – '.$what.($activity->causer ? ' by '.$activity->causer->name : '');
            })
            ->all();
    }
}
