<?php

declare(strict_types=1);

namespace App\Enums;

enum KycUserType: string
{
    case Investor = 'investor';
    case Agent = 'agent';
}
