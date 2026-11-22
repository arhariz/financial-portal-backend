<?php

declare(strict_types=1);

namespace App\Enums;

enum WalletType: string
{
    case Cash = 'cash';
    case Investment = 'investment';
    case Profit = 'profit';
    case Commission = 'commission';
}
