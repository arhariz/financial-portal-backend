<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Deduction types locked by D-030 / COM-07. New cases require a Decision (GOV-01).
 */
enum DeductionType: string
{
    case LossAdjustment = 'loss_adjustment';
    case Reversal = 'reversal';
    case AdminAdjustment = 'admin_adjustment';
}
