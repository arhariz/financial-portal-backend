<?php

declare(strict_types=1);

namespace App\Filament\Resources\CapitalReleaseRequests\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CapitalReleaseRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('amount_usd')->label('USD')->disabled(),
                TextInput::make('status')->disabled(),
                TextInput::make('release_at')->label('Release at (24h gate)')->disabled(),
                TextInput::make('released_at')->disabled(),
            ]);
    }
}
