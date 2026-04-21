<?php

namespace App\Services\HR;

use App\Models\HR\Holiday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceService
{
    /**
     * Calculate actual working days excluding holidays and weekends
     */
    public function calculateWorkingDays($employee, $startDate, $endDate)
    {
        $period = CarbonPeriod::create($startDate, $endDate);
        $workingDays = 0;

        // Get holidays for this range and branch
        $holidays = Holiday::whereBetween('date', [$startDate, $endDate])
            ->where(function ($q) use ($employee) {
                $q->whereNull('branch_id')
                  ->orWhere('branch_id', $employee->branch_id);
            })
            ->pluck('date')
            ->map(function ($date) {
                return $date->toDateString();
            })
            ->toArray();

        foreach ($period as $date) {
            // Check for week off (assuming Sunday for now, can be expanded to dynamic rules)
            if ($date->isSunday()) {
                continue;
            }

            // Check for holiday
            if (in_array($date->toDateString(), $holidays)) {
                continue;
            }

            $workingDays++;
        }

        return $workingDays;
    }
}
