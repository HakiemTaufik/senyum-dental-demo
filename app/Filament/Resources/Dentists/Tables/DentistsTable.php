<?php

namespace App\Filament\Resources\Dentists\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class DentistsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('photo')->disk('public')->circular(),
                TextColumn::make('name')->searchable()->weight('medium'),
                TextColumn::make('position'),
                TextColumn::make('user.name')->label('Staff login')->placeholder('—'),
                ToggleColumn::make('is_published')->label('On website'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
