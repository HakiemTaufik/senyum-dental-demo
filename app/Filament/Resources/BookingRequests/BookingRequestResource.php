<?php

namespace App\Filament\Resources\BookingRequests;

use App\Enums\BookingStatus;
use App\Filament\Resources\BookingRequests\Pages\ListBookingRequests;
use App\Filament\Resources\BookingRequests\Pages\ViewBookingRequest;
use App\Filament\Resources\BookingRequests\Schemas\BookingRequestInfolist;
use App\Filament\Resources\BookingRequests\Tables\BookingRequestsTable;
use App\Models\BookingRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class BookingRequestResource extends Resource
{
    protected static ?string $model = BookingRequest::class;

    protected static ?string $modelLabel = 'appointment request';

    protected static ?string $navigationLabel = 'Appointment requests';

    protected static string|UnitEnum|null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = -10;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $recordTitleAttribute = 'reference';

    /** Every list/search only includes requests this staff member may see (dentists: their own). */
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        return parent::getEloquentQuery()
            ->with(['service', 'dentist'])
            ->when($user, fn (Builder $query) => $query->visibleTo($user));
    }

    /** The orange number in the menu: requests still waiting for a reply. */
    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->where('status', BookingStatus::Pending)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'New requests waiting for a reply';
    }

    public static function infolist(Schema $schema): Schema
    {
        return BookingRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookingRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookingRequests::route('/'),
            'view' => ViewBookingRequest::route('/{record}'),
        ];
    }
}
