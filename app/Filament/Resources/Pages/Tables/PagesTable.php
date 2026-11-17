<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Models\Page;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('menu_order')
            ->columns([
                TextColumn::make('title')->searchable()->weight('medium')
                    ->description(fn (Page $record): string => $record->isHome() ? '/' : '/'.$record->slug),
                IconColumn::make('is_published')->label('Published')->boolean(),
                IconColumn::make('show_in_menu')->label('In menu')->boolean(),
                IconColumn::make('show_in_footer')->label('In footer')->boolean(),
                TextColumn::make('updated_at')->label('Last changed')->since()->sortable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->color('gray')
                    ->url(fn (Page $record): string => $record->url(), shouldOpenInNewTab: true),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
