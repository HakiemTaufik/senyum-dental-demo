<?php

namespace App\Filament\Resources\BookingRequests;

use App\Enums\BookingStatus;
use App\Models\BookingRequest;
use App\Models\Dentist;
use App\Services\BookingWorkflow;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

/**
 * The buttons staff use to handle a request. Each one updates the request
 * and (where it makes sense) emails the patient automatically.
 */
class BookingActions
{
    /** @return array<Action|ActionGroup> */
    public static function all(): array
    {
        return [
            self::confirm(),
            self::offerAlternative(),
            ActionGroup::make([
                self::decline(),
                self::cancel(),
                self::markOutcome(BookingStatus::Completed),
                self::markOutcome(BookingStatus::NoShow),
                self::editNotes(),
                self::delete(),
            ])->label('More')->icon(Heroicon::OutlinedEllipsisVertical)->button()->color('gray'),
        ];
    }

    public static function confirm(): Action
    {
        return Action::make('confirm')
            ->label(fn (BookingRequest $record): string => $record->status === BookingStatus::Confirmed ? 'Change time' : 'Confirm')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->visible(fn (BookingRequest $record): bool => self::canHandle($record) && in_array($record->status, [...BookingStatus::open(), BookingStatus::Confirmed], true))
            ->modalHeading('Confirm appointment')
            ->modalDescription('The patient will receive a confirmation email with a calendar file.')
            ->fillForm(fn (BookingRequest $record): array => [
                'appointment_at' => $record->appointment_at ?? $record->alternative_at ?? $record->preferred_date->copy()->setTime(10, 0),
                'dentist_id' => $record->dentist_id,
            ])
            ->schema([
                DateTimePicker::make('appointment_at')
                    ->label('Date & time')
                    ->seconds(false)
                    ->minutesStep(15)
                    ->minDate(now()->startOfDay())
                    ->required(),
                Select::make('dentist_id')
                    ->label('Dentist')
                    ->options(fn () => Dentist::published()->ordered()->pluck('name', 'id'))
                    ->placeholder('Any available dentist'),
                Textarea::make('message')
                    ->label('Message to patient (optional)')
                    ->rows(3)
                    ->maxLength(1000),
            ])
            ->action(function (BookingRequest $record, array $data): void {
                app(BookingWorkflow::class)->confirm($record, Carbon::parse($data['appointment_at']), $data['dentist_id'] ?: null, $data['message'] ?? null, auth()->user());
                self::done('Appointment confirmed – email sent to the patient.');
            });
    }

    public static function offerAlternative(): Action
    {
        return Action::make('offerAlternative')
            ->label('Offer another time')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('info')
            ->visible(fn (BookingRequest $record): bool => self::canHandle($record) && $record->status->isOpen())
            ->modalDescription('The patient will be emailed this time and asked to reply to accept. Confirm it once they agree.')
            ->schema([
                DateTimePicker::make('alternative_at')->label('Suggested date & time')->seconds(false)->minutesStep(15)->minDate(now()->startOfDay())->required(),
                Textarea::make('message')->label('Message to patient (optional)')->rows(3)->maxLength(1000),
            ])
            ->action(function (BookingRequest $record, array $data): void {
                app(BookingWorkflow::class)->offerAlternative($record, Carbon::parse($data['alternative_at']), $data['message'] ?? null, auth()->user());
                self::done('Other time offered – email sent to the patient.');
            });
    }

    public static function decline(): Action
    {
        return Action::make('decline')
            ->label('Decline request')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->visible(fn (BookingRequest $record): bool => self::canHandle($record) && $record->status->isOpen())
            ->requiresConfirmation()
            ->modalDescription('The patient will receive a polite email. Add a reason or suggestion if helpful.')
            ->schema([
                Textarea::make('message')->label('Message to patient (optional)')->rows(3)->maxLength(1000),
            ])
            ->action(function (BookingRequest $record, array $data): void {
                app(BookingWorkflow::class)->decline($record, $data['message'] ?? null, auth()->user());
                self::done('Request declined – email sent to the patient.');
            });
    }

    public static function cancel(): Action
    {
        return Action::make('cancel')
            ->label('Cancel appointment')
            ->icon(Heroicon::OutlinedNoSymbol)
            ->color('danger')
            ->visible(fn (BookingRequest $record): bool => self::canHandle($record) && $record->status === BookingStatus::Confirmed)
            ->requiresConfirmation()
            ->schema([
                Toggle::make('notify')->label('Email the patient')->default(true)
                    ->helperText('Switch off if the patient cancelled by phone and already knows.'),
                Textarea::make('message')->label('Message to patient (optional)')->rows(3)->maxLength(1000),
            ])
            ->action(function (BookingRequest $record, array $data): void {
                app(BookingWorkflow::class)->cancel($record, $data['message'] ?? null, (bool) $data['notify'], auth()->user());
                self::done('Appointment cancelled.');
            });
    }

    public static function markOutcome(BookingStatus $outcome): Action
    {
        return Action::make('mark'.ucfirst(str($outcome->value)->camel()))
            ->label($outcome === BookingStatus::Completed ? 'Mark as attended' : 'Mark as did not attend')
            ->icon($outcome->getIcon())
            ->color($outcome === BookingStatus::Completed ? 'gray' : 'warning')
            ->visible(fn (BookingRequest $record): bool => self::canHandle($record)
                && $record->status === BookingStatus::Confirmed
                && $record->appointment_at?->isPast())
            ->requiresConfirmation()
            ->action(function (BookingRequest $record) use ($outcome): void {
                app(BookingWorkflow::class)->markOutcome($record, $outcome, auth()->user());
                self::done('Saved.');
            });
    }

    public static function editNotes(): Action
    {
        return Action::make('editNotes')
            ->label('Staff notes')
            ->icon(Heroicon::OutlinedPencilSquare)
            ->color('gray')
            ->visible(fn (BookingRequest $record): bool => self::canHandle($record))
            ->fillForm(fn (BookingRequest $record): array => ['staff_notes' => $record->staff_notes])
            ->schema([
                Textarea::make('staff_notes')
                    ->label('Internal notes (never sent to the patient)')
                    ->helperText('Keep it brief and factual. No clinical details – those belong in the patient record system.')
                    ->rows(4)
                    ->maxLength(2000),
            ])
            ->action(function (BookingRequest $record, array $data): void {
                $record->update(['staff_notes' => $data['staff_notes']]);
                self::done('Notes saved.');
            });
    }

    public static function delete(): DeleteAction
    {
        return DeleteAction::make()
            ->label('Delete permanently')
            ->modalDescription('Use this when a patient asks for their data to be erased (PDPA). This cannot be undone.');
    }

    private static function canHandle(BookingRequest $record): bool
    {
        return auth()->user()?->can('update', $record) ?? false;
    }

    private static function done(string $message): void
    {
        Notification::make()->title($message)->success()->send();
    }
}
