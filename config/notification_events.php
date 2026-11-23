<?php

declare(strict_types=1);

/**
 * The five notification event groups locked by plan §7 / D-016.
 * Machine keys are transcribed from the locked group names; security alerts
 * are mandatory and cannot be disabled by user preference (plan §7).
 */
return [
    'events' => [
        'kyc' => ['label' => 'KYC', 'mandatory' => false],
        'deposits_withdrawals' => ['label' => 'Deposits / withdrawals', 'mandatory' => false],
        'pnl_distribution' => ['label' => 'P&L distributions', 'mandatory' => false],
        'commissions_agent' => ['label' => 'Commissions / agent updates', 'mandatory' => false],
        'security_alert' => ['label' => 'Security alerts', 'mandatory' => true],
    ],
];
