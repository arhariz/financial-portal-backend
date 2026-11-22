<?php

declare(strict_types=1);

namespace App\Enums;

enum WalletTransactionType: string
{
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';
    case TransferIn = 'transfer_in';
    case TransferOut = 'transfer_out';
    case Invest = 'invest';
    case ProfitDistribution = 'profit_distribution';
    case LossAdjustment = 'loss_adjustment';
    case Commission = 'commission';
    case RoleBonus = 'role_bonus';
    case CapitalRelease = 'capital_release';
    case Reversal = 'reversal';
    case Adjustment = 'adjustment';
}
