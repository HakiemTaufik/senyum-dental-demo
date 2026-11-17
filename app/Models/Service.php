<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'summary', 'description', 'icon', 'image', 'image_alt', 'duration_minutes', 'price_note', 'is_bookable', 'is_published', 'sort_order', 'seo_title', 'seo_description'])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory, Publishable;

    protected function casts(): array
    {
        return [
            'is_bookable' => 'boolean',
            'is_published' => 'boolean',
            'duration_minutes' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
