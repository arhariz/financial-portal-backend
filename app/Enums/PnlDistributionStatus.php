<?php

declare(strict_types=1);

namespace App\Enums;

enum PnlDistributionStatus: string
{
    case Draft = 'draft';
    case Distributed = 'distributed';
    case Reversed = 'reversed';
}
