<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserProfiles\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('full_name')
                    ->maxLength(255),
                DatePicker::make('date_of_birth'),
                Textarea::make('address')
                    ->rows(3),
                TextInput::make('city')
                    ->maxLength(255),
                TextInput::make('country')
                    ->maxLength(255),
                TextInput::make('id_number')
                    ->maxLength(255)
                    ->helperText('KYC-sensitive. Admin-editable only; masked in listings and audit-logged on change.'),
            ]);
    }
}
