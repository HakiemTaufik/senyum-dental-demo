<?php

namespace App\Filament\Resources\Faqs\Tables;

use App\Models\Faq;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FaqsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('question')->searchable()->wrap()->weight('medium'),
                TextColumn::make('category')->badge()->placeholder('—'),
                ToggleColumn::make('is_published')->label('On website'),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options(fn (): array => Faq::query()->whereNotNull('category')->distinct()->pluck('category', 'category')->all()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
