<?php

namespace App\Filament\Resources\Dentists\Schemas;

use App\Enums\Role;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class DentistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Profile')
                            ->columnSpan(2)
                            ->schema([
                                TextInput::make('name')
                                    ->placeholder('Dr. Nur Aina binti Ahmad')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('position')
                                    ->placeholder('Dental Surgeon')
                                    ->maxLength(255),
                                TextInput::make('qualifications')
                                    ->placeholder('BDS (Malaya)')
                                    ->helperText('Only recognised qualifications (dental advertising rules).')
                                    ->maxLength(255),
                                TextInput::make('mdc_registration')
                                    ->label('Malaysian Dental Council registration no.')
                                    ->maxLength(100),
                                TextInput::make('languages')
                                    ->placeholder('English, Bahasa Melayu')
                                    ->maxLength(255),
                                Textarea::make('bio')
                                    ->rows(5)
                                    ->maxLength(2000)
                                    ->helperText('Factual and modest. No patient testimonials or claims of being better than others.'),
                            ]),
                        Section::make('Settings')
                            ->columnSpan(1)
                            ->schema([
                                Toggle::make('is_published')->label('Show on website')->default(true),
                                FileUpload::make('photo')
                                    ->image()
                                    ->avatar()
                                    ->disk('public')
                                    ->directory('dentists')
                                    ->imageEditor()
                                    ->maxSize(4096)
                                    ->automaticallyResizeImagesToWidth('800')
                                    ->helperText('A square photo works best. Get the dentist\'s written permission first.'),
                                Select::make('user_id')
                                    ->label('Linked staff login')
                                    ->helperText('Optional. Lets this dentist see the bookings assigned to them.')
                                    ->relationship('user', 'name', fn (Builder $query) => $query->where('role', Role::Dentist))
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ]),
            ]);
    }
}
