<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\Role;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('role')->badge()->sortable(),
                IconColumn::make('is_active')->label('Active')->boolean(),
                IconColumn::make('two_factor')
                    ->label('2FA set up')
                    ->boolean()
                    ->state(fn (User $record): bool => $record->hasTwoFactorEnabled()),
                TextColumn::make('last_login_at')
                    ->label('Last sign-in')
                    ->since()
                    ->placeholder('Never')
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('role')->options(Role::class),
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('resetTwoFactor')
                    ->label('Reset 2FA')
                    ->icon(Heroicon::OutlinedKey)
                    ->color('warning')
                    ->visible(fn (User $record): bool => $record->hasTwoFactorEnabled())
                    ->requiresConfirmation()
                    ->modalDescription('Use this if the staff member lost their phone. They will set up two-factor login again at their next sign-in.')
                    ->action(function (User $record): void {
                        $record->saveAppAuthenticationSecret(null);
                        $record->saveAppAuthenticationRecoveryCodes(null);

                        Notification::make()->title('Two-factor login reset')->success()->send();
                    }),
            ]);
    }
}
