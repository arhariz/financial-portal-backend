<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The five event groups locked by plan §7 / D-016.
 */
enum NotificationEvent: string
{
    case Kyc = 'kyc';
    case DepositsWithdrawals = 'deposits_withdrawals';
    case PnlDistribution = 'pnl_distribution';
    case CommissionsAgent = 'commissions_agent';
    case SecurityAlert = 'security_alert';

    public function isMandatory(): bool
    {
        return config("notification_events.events.{$this->value}.mandatory") === true;
    }
}
