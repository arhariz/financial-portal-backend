<?php

declare(strict_types=1);

namespace App\Enums;

enum WithdrawalSourceWalletType: string
{
    case Cash = 'cash';
    case Profit = 'profit';
}
