<?php

namespace App\Filament\Resources\Services\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->description('Drag rows (after clicking the reorder button) to change the order on the website.')
            ->columns([
                TextColumn::make('name')->searchable()->weight('medium'),
                TextColumn::make('price_note')->label('Fee note')->placeholder('—'),
                IconColumn::make('is_bookable')->label('Bookable')->boolean(),
                ToggleColumn::make('is_published')->label('On website'),
                TextColumn::make('updated_at')->label('Last changed')->since()->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
