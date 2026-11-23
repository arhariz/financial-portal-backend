<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManualDeposits\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ManualDepositForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('amount_bdt')->label('BDT')->disabled(),
                TextInput::make('amount_usd')->label('USD')->disabled(),
                TextInput::make('payment_method')->disabled(),
                TextInput::make('proof_path')->label('Proof key')->disabled(),
                TextInput::make('status')->disabled(),
            ]);
    }
}
