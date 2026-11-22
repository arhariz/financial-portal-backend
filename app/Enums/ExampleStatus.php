<?php

declare(strict_types=1);

namespace App\Enums;

enum ExampleStatus: string
{
    case Active = 'active';
    case Closed = 'closed';
}
