<?php

declare(strict_types=1);

namespace App\Filament\Resources\AgentCreditTransfers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AgentCreditTransferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('agent_id')->label('Agent ID')->disabled(),
                TextInput::make('investor_id')->label('Investor ID')->disabled(),
                TextInput::make('amount_usd')->label('USD')->disabled(),
            ]);
    }
}
