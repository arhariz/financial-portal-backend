<?php

declare(strict_types=1);

namespace App\Filament\Resources\CapitalReleaseRequests;

use App\Filament\Resources\CapitalReleaseRequests\Pages\ListCapitalReleaseRequests;
use App\Filament\Resources\CapitalReleaseRequests\Schemas\CapitalReleaseRequestForm;
use App\Filament\Resources\CapitalReleaseRequests\Tables\CapitalReleaseRequestsTable;
use App\Models\CapitalReleaseRequest;
use BackedEnum;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CapitalReleaseRequestResource extends Resource
{
    protected static ?string $model = CapitalReleaseRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $navigationLabel = 'Capital Releases';

    protected static ?string $modelLabel = 'Capital Release';

    protected static ?string $pluralModelLabel = 'Capital Releases';

    protected static ?int $navigationSort = 6;

    protected static string|UnitEnum|null $navigationGroup = 'Money Flow';

    public static function getSlug(?Panel $panel = null): string
    {
        return 'capital-releases';
    }

    public static function form(Schema $schema): Schema
    {
        return CapitalReleaseRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CapitalReleaseRequestsTable::configure($table);
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListCapitalReleaseRequests::route('/'),
        ];
    }
}
