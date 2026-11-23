<?php

declare(strict_types=1);

namespace App\Enums;

enum CommissionEvent: string
{
    case Investment = 'investment';
    case ProfitDistribution = 'profit_distribution';
    case CreditPurchase = 'credit_purchase';
}
