<?php

declare(strict_types=1);

namespace App\Enums;

enum PositionSource: string
{
    case Cash = 'cash';
    case Profit = 'profit';
}
