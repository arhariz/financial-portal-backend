<?php

declare(strict_types=1);

namespace App\Filament\Resources\AgentCreditPurchases\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AgentCreditPurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('amount_bdt')->label('BDT')->disabled(),
                TextInput::make('amount_usd')->label('USD face value')->disabled(),
                TextInput::make('bonus_amount_usd')->label('Role bonus (USD)')->disabled(),
                TextInput::make('created_by')->label('Recorded by')->disabled(),
            ]);
    }
}
