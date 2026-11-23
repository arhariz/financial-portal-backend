<?php

declare(strict_types=1);

namespace App\Enums;

enum CommissionStatus: string
{
    case Pending = 'pending';
    case Vested = 'vested';
    case Cancelled = 'cancelled';
    case Reversed = 'reversed';
}
