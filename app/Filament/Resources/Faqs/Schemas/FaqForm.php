<?php

namespace App\Filament\Resources\Faqs\Schemas;

use App\Filament\Support\ContentFields;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('question')->required()->maxLength(255),
                        RichEditor::make('answer')
                            ->required()
                            ->toolbarButtons(ContentFields::RICH_TOOLBAR),
                        TextInput::make('category')
                            ->placeholder('e.g. Appointments')
                            ->datalist(['Appointments', 'Fees & payment', 'Treatments', 'Children'])
                            ->maxLength(100),
                        Toggle::make('is_published')->label('Show on website')->default(true),
                    ]),
            ]);
    }
}
