<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Carbon;

/**
 * T+2 eligibility in Asia/Dhaka (TME-02/D-009): the second business day
 * after investment, with Fri/Sat/Sun falling through to the next Tuesday.
 */
final class EligibilityDateService
{
    private const BUSINESS_TIMEZONE = 'Asia/Dhaka';

    public function investmentDate(): Carbon
    {
        return now()->setTimezone(self::BUSINESS_TIMEZONE)->startOfDay();
    }

    public function forDate(string $date): Carbon
    {
        $investmentDate = Carbon::parse($date, self::BUSINESS_TIMEZONE)->startOfDay();

        if ($this->isWeekend($investmentDate)) {
            return $this->nextTuesday($investmentDate);
        }

        $candidate = $investmentDate->copy()->addDays(2);

        if ($this->isWeekend($candidate)) {
            return $this->nextTuesday($candidate);
        }

        return $candidate;
    }

    public function forNow(): Carbon
    {
        return $this->forDate(now()->setTimezone(self::BUSINESS_TIMEZONE)->toDateString());
    }

    private function isWeekend(Carbon $date): bool
    {
        return in_array($date->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY, Carbon::SUNDAY], true);
    }

    private function nextTuesday(Carbon $date): Carbon
    {
        $result = $date->copy();

        while ($result->dayOfWeek !== Carbon::TUESDAY) {
            $result->addDay();
        }

        return $result;
    }
}
