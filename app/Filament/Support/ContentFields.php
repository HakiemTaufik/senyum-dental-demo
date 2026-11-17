<?php

namespace App\Filament\Support;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

/** Form fields reused by several admin screens. */
class ContentFields
{
    public const RICH_TOOLBAR = [
        ['bold', 'italic', 'link'],
        ['h2', 'h3'],
        ['bulletList', 'orderedList', 'blockquote'],
        ['undo', 'redo'],
    ];

    /** Simple icons the owner can pick (drawn in resources/views/components/site-icon.blade.php). */
    public const ICONS = [
        'tooth' => 'Tooth',
        'sparkle' => 'Sparkle (cleaning / whitening)',
        'shield' => 'Shield (prevention)',
        'smile' => 'Smile',
        'child' => 'Child (family / kids)',
        'braces' => 'Braces / aligners',
        'crown' => 'Crown / bridge',
        'emergency' => 'First aid (urgent care)',
        'calendar' => 'Calendar',
        'clock' => 'Clock',
        'heart' => 'Heart (care)',
        'chat' => 'Chat (consultation)',
        'location' => 'Location pin',
        'accessible' => 'Accessibility',
    ];

    /** Title field that fills in the web address (slug) automatically while creating. */
    public static function titleAndSlug(string $field = 'title', ?string $label = null): array
    {
        return [
            TextInput::make($field)
                ->label($label)
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation): void {
                    if ($operation === 'create' || blank($get('slug'))) {
                        $set('slug', Str::slug((string) $state));
                    }
                }),
            TextInput::make('slug')
                ->label('Web address')
                ->helperText('Lower-case words joined by hyphens, e.g. teeth-whitening. Avoid changing it after launch: old links would break.')
                ->required()
                ->maxLength(255)
                ->alphaDash()
                ->unique(ignoreRecord: true),
        ];
    }

    public static function seoSection(): Section
    {
        return Section::make('Search engines (SEO)')
            ->description('How this appears in Google results. Leave empty to use the title and summary.')
            ->collapsed()
            ->columnSpanFull()
            ->schema([
                TextInput::make('seo_title')
                    ->label('Title in Google')
                    ->maxLength(60)
                    ->helperText('Up to 60 characters.'),
                Textarea::make('seo_description')
                    ->label('Description in Google')
                    ->rows(2)
                    ->maxLength(160)
                    ->helperText('Up to 160 characters.'),
            ]);
    }
}
