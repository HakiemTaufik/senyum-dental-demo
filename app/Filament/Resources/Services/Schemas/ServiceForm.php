<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Filament\Support\ContentFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Service')
                            ->columnSpan(2)
                            ->schema([
                                ...ContentFields::titleAndSlug('name', 'Service name'),
                                Textarea::make('summary')
                                    ->helperText('One or two sentences, shown on service cards.')
                                    ->required()
                                    ->rows(2)
                                    ->maxLength(300),
                                RichEditor::make('description')
                                    ->helperText('Full description on the service page. Avoid words like "best", "painless" or "guaranteed" (dental advertising rules).')
                                    ->toolbarButtons(ContentFields::RICH_TOOLBAR),
                            ]),
                        Section::make('Details')
                            ->columnSpan(1)
                            ->schema([
                                Toggle::make('is_published')->label('Show on website')->default(true),
                                Toggle::make('is_bookable')->label('Can be booked online')->default(true),
                                Select::make('icon')
                                    ->options(ContentFields::ICONS)
                                    ->default('tooth'),
                                TextInput::make('duration_minutes')
                                    ->label('Typical duration (minutes)')
                                    ->numeric()
                                    ->minValue(5)
                                    ->maxValue(480),
                                TextInput::make('price_note')
                                    ->label('Fee note')
                                    ->placeholder('e.g. From RM 80')
                                    ->helperText('Optional. Fees must follow the clinic\'s fee schedule.')
                                    ->maxLength(100),
                                FileUpload::make('image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('services')
                                    ->imageEditor()
                                    ->maxSize(4096)
                                    ->automaticallyResizeImagesToWidth('1600'),
                                TextInput::make('image_alt')
                                    ->label('Image description (for blind visitors)')
                                    ->maxLength(255),
                            ]),
                    ]),
                ContentFields::seoSection(),
            ]);
    }
}
