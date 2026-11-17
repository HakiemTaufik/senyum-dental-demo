<?php

namespace App\Filament\Pages;

use App\Settings\ClinicSettings;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/** Settings → Clinic details: phone, address, hours, social links. Owner only. */
class ManageClinicSettings extends SettingsPage
{
    protected static string $settings = ClinicSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Clinic details';

    protected static ?string $title = 'Clinic details';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->role->canManageSettings() ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Clinic')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required()->maxLength(100),
                        TextInput::make('tagline')->maxLength(160),
                        Textarea::make('address')->rows(3)->maxLength(300)->columnSpanFull(),
                        TextInput::make('maps_url')
                            ->label('Google Maps link')
                            ->url()
                            ->helperText('In Google Maps, find the clinic → Share → Copy link.')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),
                Section::make('Contact')
                    ->columns(2)
                    ->schema([
                        TextInput::make('phone')->tel()->maxLength(30),
                        TextInput::make('whatsapp')
                            ->label('WhatsApp number')
                            ->helperText('Country code + number, no spaces or +. Example: 60123456789')
                            ->regex('/^\d{9,15}$/'),
                        TextInput::make('email')->email()->maxLength(255),
                        TextInput::make('facebook_url')->label('Facebook page link')->url()->maxLength(255),
                        TextInput::make('instagram_url')->label('Instagram link')->url()->maxLength(255),
                    ]),
                Section::make('Opening hours')
                    ->schema([
                        Repeater::make('opening_hours')
                            ->hiddenLabel()
                            ->schema([
                                TextInput::make('days')->required()->placeholder('Monday – Friday')->maxLength(60),
                                TextInput::make('hours')->required()->placeholder('9:00 am – 6:00 pm')->maxLength(60),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->addActionLabel('Add a row'),
                    ]),
                Section::make('Online appointment requests')
                    ->columns(2)
                    ->schema([
                        Toggle::make('bookings_enabled')
                            ->label('Accept requests on the website')
                            ->helperText('Switch off during holidays. Visitors will be asked to call instead.')
                            ->columnSpanFull(),
                        TextInput::make('booking_notification_email')
                            ->label('Send new-request alerts to')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('booking_max_days_ahead')
                            ->label('Furthest date patients can choose (days ahead)')
                            ->numeric()->minValue(7)->maxValue(365)->required(),
                        TextInput::make('booking_retention_months')
                            ->label('Delete old requests after (months)')
                            ->helperText('Privacy law (PDPA): don\'t keep personal data longer than needed. Counted from the appointment date.')
                            ->numeric()->minValue(1)->maxValue(24)->required(),
                    ]),
                Section::make('Privacy (PDPA)')
                    ->description('The person patients contact about their personal data. Shown in the privacy policy.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('dpo_name')->label('Data Protection Officer')->maxLength(100),
                        TextInput::make('dpo_email')->label('DPO email')->email()->maxLength(255),
                        TextInput::make('dpo_phone')->label('DPO phone')->tel()->maxLength(30),
                    ]),
                Section::make('Visitor statistics')
                    ->schema([
                        TextInput::make('analytics_id')
                            ->label('Google Analytics 4 measurement ID')
                            ->placeholder('G-XXXXXXXXXX')
                            ->helperText('Leave empty to turn analytics off. When set, visitors are asked for permission first (cookie banner), and analytics only load if they accept.')
                            ->regex('/^G-[A-Z0-9]{4,20}$/')
                            ->maxLength(30),
                    ]),
                Section::make('Legal & demo')
                    ->schema([
                        Textarea::make('registration_note')
                            ->label('Clinic registration line (footer)')
                            ->rows(2)
                            ->maxLength(300),
                        Toggle::make('show_demo_notice')
                            ->label('Show "Demo website" notice')
                            ->helperText('Keep this on unless this is a real clinic.'),
                    ]),
            ]);
    }
}
