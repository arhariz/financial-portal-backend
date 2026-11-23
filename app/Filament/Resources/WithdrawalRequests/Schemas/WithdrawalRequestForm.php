<?php

declare(strict_types=1);

namespace App\Filament\Resources\WithdrawalRequests\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WithdrawalRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('amount_usd')->label('USD')->disabled(),
                TextInput::make('source_wallet_type')->label('Source wallet')->disabled(),
                TextInput::make('payout_method')->disabled(),
                TextInput::make('status')->disabled(),
            ]);
    }
}
