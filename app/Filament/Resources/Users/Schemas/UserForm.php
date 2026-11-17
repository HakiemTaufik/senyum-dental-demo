<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Role;
use App\Models\User;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->label(fn (string $operation): string => $operation === 'create' ? 'Temporary password' : 'New password')
                            ->helperText(fn (string $operation): string => $operation === 'create'
                                ? 'Give this to the staff member privately. They will also set up two-factor login the first time they sign in.'
                                : 'Leave empty to keep their current password.')
                            ->password()
                            ->revealable()
                            ->rule(Password::min(12)->mixedCase()->numbers())
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->maxLength(255),
                    ])
                    ->columns(1),
                Section::make('Access')
                    ->schema([
                        Radio::make('role')
                            ->options(Role::class)
                            ->required()
                            // The owner can't remove their own Owner role (prevents lock-out).
                            ->disabled(fn (?User $record): bool => $record?->is(auth()->user()) ?? false),
                        Toggle::make('is_active')
                            ->label('Account active')
                            ->helperText('Switch off to block sign-in without deleting the account (e.g. when someone leaves).')
                            ->default(true)
                            ->disabled(fn (?User $record): bool => $record?->is(auth()->user()) ?? false),
                    ]),
            ]);
    }
}
