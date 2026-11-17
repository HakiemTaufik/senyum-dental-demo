<?php

namespace App\Models;

use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A website page built from content blocks (hero, text, services list, ...).
 * "System" pages (home, contact, privacy, ...) can be edited but not deleted.
 */
#[Fillable(['title', 'slug', 'content', 'is_published', 'is_system', 'show_in_menu', 'show_in_footer', 'menu_order', 'seo_title', 'seo_description'])]
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    public const HOME = 'home';

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'is_published' => 'boolean',
            'is_system' => 'boolean',
            'show_in_menu' => 'boolean',
            'show_in_footer' => 'boolean',
            'menu_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function isHome(): bool
    {
        return $this->slug === self::HOME;
    }

    public function url(): string
    {
        return $this->isHome() ? route('home') : route('page', $this);
    }
}
