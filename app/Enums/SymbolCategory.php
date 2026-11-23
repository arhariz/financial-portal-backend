<?php

declare(strict_types=1);

namespace App\Enums;

enum SymbolCategory: string
{
    case Metal = 'metal';
    case Crypto = 'crypto';
    case Energy = 'energy';
    case Forex = 'forex';
}
