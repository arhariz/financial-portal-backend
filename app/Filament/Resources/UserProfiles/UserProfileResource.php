<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserProfiles;

use App\Filament\Resources\UserProfiles\Pages\CreateUserProfile;
use App\Filament\Resources\UserProfiles\Pages\EditUserProfile;
use App\Filament\Resources\UserProfiles\Pages\ListUserProfiles;
use App\Filament\Resources\UserProfiles\Schemas\UserProfileForm;
use App\Filament\Resources\UserProfiles\Tables\UserProfilesTable;
use App\Models\UserProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserProfileResource extends Resource
{
    protected static ?string $model = UserProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $navigationLabel = 'User Profiles';

    protected static ?string $modelLabel = 'User Profile';

    protected static ?string $pluralModelLabel = 'User Profiles';

    protected static ?string $recordTitleAttribute = 'full_name';

    protected static ?int $navigationSort = 4;

    protected static string | \UnitEnum | null $navigationGroup = 'Access Control';

    public static function form(Schema $schema): Schema
    {
        return UserProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserProfilesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserProfiles::route('/'),
            'create' => CreateUserProfile::route('/create'),
            'edit' => EditUserProfile::route('/{record}/edit'),
        ];
    }
}
