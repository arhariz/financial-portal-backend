<?php

declare(strict_types=1);

namespace App\Enums;

enum SymbolStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
}
