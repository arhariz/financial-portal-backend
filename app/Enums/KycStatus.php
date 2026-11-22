<?php

declare(strict_types=1);

namespace App\Enums;

enum KycStatus: string
{
    case None = 'none';
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
