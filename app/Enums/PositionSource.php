<?php

declare(strict_types=1);

namespace App\Enums;

enum PositionSource: string
{
    case Cash = 'cash';
    case Profit = 'profit';
    // WAL-05 permits commission → investment; the position must record its
    // true origin (GOV-02: enum aligned with the locked transfer rule).
    case Commission = 'commission';
}
