<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Support\ContentFields;
use App\Models\Page;
use App\Support\Html;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Page')
                            ->columnSpan(2)
                            ->schema([
                                ...ContentFields::titleAndSlug('title', 'Page title'),
                            ]),
                        Section::make('Visibility')
                            ->columnSpan(1)
                            ->schema([
                                Toggle::make('is_published')->label('Published')->default(true)
                                    ->disabled(fn (?Page $record): bool => $record?->isHome() ?? false),
                                Toggle::make('show_in_menu')->label('Show in top menu'),
                                Toggle::make('show_in_footer')->label('Show in footer links'),
                                TextInput::make('menu_order')->label('Menu position')->numeric()->default(0)
                                    ->helperText('Lower numbers appear first.'),
                            ]),
                    ]),
                Section::make('Content')
                    ->description('Build the page from blocks. Use the arrows to reorder them.')
                    ->columnSpanFull()
                    ->schema([
                        Builder::make('content')
                            ->hiddenLabel()
                            ->blocks(self::blocks())
                            ->collapsible()
                            ->blockNumbers(false)
                            ->addActionLabel('Add a block'),
                    ]),
                ContentFields::seoSection(),
            ]);
    }

    /** @return array<Block> */
    public static function blocks(): array
    {
        return [
            Block::make('hero')
                ->label('Hero banner (top of page)')
                ->icon(Heroicon::OutlinedSparkles)
                ->schema([
                    TextInput::make('heading')->required()->maxLength(120),
                    Textarea::make('text')->rows(2)->maxLength(300),
                    Grid::make(2)->schema([
                        TextInput::make('button_label')->default('Request an appointment')->maxLength(40),
                        self::linkField('button_url')->default('/book'),
                    ]),
                    Toggle::make('show_whatsapp')->label('Also show a WhatsApp button')->default(true),
                    self::imageField('hero'),
                    TextInput::make('image_alt')->label('Image description (for blind visitors)')->maxLength(255),
                ]),

            Block::make('rich_text')
                ->label('Text')
                ->icon(Heroicon::OutlinedBars3BottomLeft)
                ->schema([
                    TextInput::make('heading')->maxLength(120),
                    RichEditor::make('body')->required()->toolbarButtons(ContentFields::RICH_TOOLBAR)
                        ->helperText('Tip: type '.implode(', ', array_keys(Html::TOKENS)).' and the current value from Settings → Clinic details is shown instead.'),
                ]),

            Block::make('image_text')
                ->label('Image + text')
                ->icon(Heroicon::OutlinedPhoto)
                ->schema([
                    TextInput::make('heading')->maxLength(120),
                    RichEditor::make('body')->toolbarButtons(ContentFields::RICH_TOOLBAR),
                    self::imageField('content'),
                    TextInput::make('image_alt')->label('Image description (for blind visitors)')->maxLength(255),
                    Radio::make('image_position')->options(['left' => 'Image on the left', 'right' => 'Image on the right'])->default('right')->inline(),
                    Grid::make(2)->schema([
                        TextInput::make('button_label')->maxLength(40),
                        self::linkField('button_url'),
                    ]),
                ]),

            Block::make('features')
                ->label('Highlights (icons with short text)')
                ->icon(Heroicon::OutlinedSquares2x2)
                ->schema([
                    TextInput::make('heading')->maxLength(120),
                    Textarea::make('intro')->rows(2)->maxLength(300),
                    Repeater::make('items')
                        ->schema([
                            Select::make('icon')->options(ContentFields::ICONS)->default('heart'),
                            TextInput::make('title')->required()->maxLength(80),
                            Textarea::make('text')->rows(2)->maxLength(240),
                        ])
                        ->columns(3)
                        ->minItems(1)
                        ->maxItems(6)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                ]),

            Block::make('services')
                ->label('List of services')
                ->icon(Heroicon::OutlinedListBullet)
                ->schema([
                    TextInput::make('heading')->default('Our services')->maxLength(120),
                    Textarea::make('intro')->rows(2)->maxLength(300),
                    TextInput::make('limit')->numeric()->minValue(1)->helperText('Leave empty to show all published services.'),
                    Toggle::make('show_all_link')->label('Show "All services" link')->default(false),
                ]),

            Block::make('dentists')
                ->label('List of dentists')
                ->icon(Heroicon::OutlinedUserGroup)
                ->schema([
                    TextInput::make('heading')->default('Meet our dentists')->maxLength(120),
                    Textarea::make('intro')->rows(2)->maxLength(300),
                ]),

            Block::make('faqs')
                ->label('Frequently asked questions')
                ->icon(Heroicon::OutlinedQuestionMarkCircle)
                ->schema([
                    TextInput::make('heading')->default('Frequently asked questions')->maxLength(120),
                    TextInput::make('category')->helperText('Optional: only show FAQs from this category.')->maxLength(100),
                    TextInput::make('limit')->numeric()->minValue(1)->helperText('Leave empty to show all.'),
                    Toggle::make('show_all_link')->label('Show "All FAQs" link')->default(false),
                ]),

            Block::make('cta')
                ->label('Call to action banner')
                ->icon(Heroicon::OutlinedMegaphone)
                ->schema([
                    TextInput::make('heading')->required()->maxLength(120),
                    Textarea::make('text')->rows(2)->maxLength(300),
                    Grid::make(2)->schema([
                        TextInput::make('button_label')->default('Request an appointment')->maxLength(40),
                        self::linkField('button_url')->default('/book'),
                    ]),
                    Toggle::make('show_whatsapp')->label('Also show a WhatsApp button')->default(true),
                ]),

            Block::make('contact')
                ->label('Contact details, hours & map link')
                ->icon(Heroicon::OutlinedMapPin)
                ->schema([
                    TextInput::make('heading')->default('Visit or contact us')->maxLength(120),
                    Textarea::make('intro')->rows(2)->maxLength(300)
                        ->helperText('Address, phone and hours come from Settings → Clinic details.'),
                ]),
        ];
    }

    /** A link field that accepts site links (/about), full https links, phone (tel:) and email (mailto:). */
    public static function linkField(string $name): TextInput
    {
        return TextInput::make($name)
            ->label('Button link')
            ->placeholder('/contact')
            ->helperText('A page on this site like /contact, or a full https:// link.')
            ->maxLength(255)
            ->regex('/^(\/[^\s]*|#[\w-]*|https:\/\/[^\s]+|tel:\+?[\d\s-]+|mailto:[^\s@]+@[^\s@]+)$/');
    }

    public static function imageField(string $directory): FileUpload
    {
        return FileUpload::make('image')
            ->image()
            ->disk('public')
            ->directory('pages/'.$directory)
            ->imageEditor()
            ->maxSize(4096)
            ->automaticallyResizeImagesToWidth('1600')
            ->helperText('Optional. Use photos you have the rights to.');
    }
}
