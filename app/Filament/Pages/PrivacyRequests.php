<?php

namespace App\Filament\Pages;

use App\Models\BookingRequest;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

/**
 * Settings → Privacy requests (Owner only).
 * Handles PDPA requests from patients: "send me a copy of my data" (access / portability)
 * and "delete my data" (erasure / withdrawal of consent).
 */
class PrivacyRequests extends Page
{
    protected string $view = 'filament.pages.privacy-requests';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Privacy requests';

    protected static ?string $title = 'Privacy requests (PDPA)';

    protected static ?int $navigationSort = 3;

    public ?array $data = [];

    public ?string $searchedEmail = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->isOwner() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Patient\'s email address')
                    ->helperText('Use the email address the request came from, after confirming the person\'s identity.')
                    ->email()
                    ->required(),
            ])
            ->statePath('data');
    }

    public function search(): void
    {
        $this->searchedEmail = mb_strtolower(trim($this->form->getState()['email']));
    }

    /** @return Collection<int, BookingRequest> */
    public function getRecordsProperty(): Collection
    {
        if (blank($this->searchedEmail)) {
            return new Collection;
        }

        return BookingRequest::query()->with(['service', 'dentist'])->where('email', $this->searchedEmail)->latest()->get();
    }

    public function exportAction(): Action
    {
        return Action::make('export')
            ->label('Download a copy (JSON)')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->visible(fn (): bool => $this->records->isNotEmpty())
            ->action(function (): StreamedResponse {
                $records = $this->records;

                activity('privacy')->causedBy(auth()->user())
                    ->withProperties(['request' => 'access', 'records' => $records->pluck('reference')])
                    ->log('Personal data exported for a data subject request');

                $export = [
                    'exported_at' => now()->toIso8601String(),
                    'controller' => config('app.name'),
                    'booking_requests' => $records->map(fn (BookingRequest $b): array => [
                        'reference' => $b->reference,
                        'received_at' => $b->created_at->toIso8601String(),
                        'name' => $b->name,
                        'phone' => $b->phone,
                        'email' => $b->email,
                        'patient_type' => $b->patient_type->getLabel(),
                        'service' => $b->serviceName(),
                        'preferred_date' => $b->preferred_date->toDateString(),
                        'preferred_time' => $b->preferred_time->getLabel(),
                        'note' => $b->note,
                        'status' => $b->status->getLabel(),
                        'appointment_at' => $b->appointment_at?->toIso8601String(),
                        'dentist' => $b->dentist?->name,
                        'messages_sent_to_you' => $b->message_to_patient,
                        'consent_given_at' => $b->consented_at->toIso8601String(),
                        'privacy_notice_version' => $b->privacy_notice_version,
                    ])->values(),
                ];

                return response()->streamDownload(
                    fn () => print (json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
                    'personal-data-'.now()->format('Y-m-d').'.json',
                    ['Content-Type' => 'application/json'],
                );
            });
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->label('Delete all of this person\'s booking data')
            ->icon(Heroicon::OutlinedTrash)
            ->color('danger')
            ->visible(fn (): bool => $this->records->isNotEmpty())
            ->requiresConfirmation()
            ->modalHeading('Permanently delete this person\'s data?')
            ->modalDescription(fn (): string => 'This deletes '.$this->records->count().' appointment request(s) for '.$this->searchedEmail.'. It cannot be undone. Clinical records kept elsewhere are not affected.')
            ->modalSubmitActionLabel('Yes, delete permanently')
            ->action(function (): void {
                $references = $this->records->pluck('reference');

                $this->records->each(function (BookingRequest $booking): void {
                    // Remove the audit entries about this request too, then the request itself.
                    $booking->activitiesAsSubject()->delete();
                    $booking->delete();
                });

                activity('privacy')->causedBy(auth()->user())
                    ->withProperties(['request' => 'erasure', 'records' => $references])
                    ->log('Personal data deleted for a data subject request');

                Notification::make()
                    ->title('Deleted '.$references->count().' request(s)')
                    ->body('Reply to the person to confirm their data has been deleted.')
                    ->success()
                    ->send();

                $this->searchedEmail = null;
                $this->form->fill();
            });
    }
}
