<?php

namespace App\Services\Girvi;

use App\Models\Girvi;
use Carbon\Carbon;

class GirviInterestService
{
    /**
     * Calculate accrued interest for a specific Girvi loan.
     */
    public function calculateAccruedInterest(Girvi $girvi, ?Carbon $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? now();
        $startDate = $girvi->girvi_date;
        $principal = $girvi->loan_amount - $girvi->principal_paid;

        $days = $startDate->diffInDays($asOfDate);
        if ($days <= 0) {
            return ['interest' => 0, 'penalty' => 0];
        }

        $interest = 0;
        $penalty = 0;

        // Interest Logic based on type
        switch ($girvi->interest_type) {
            case 'simple':
                $interest = ($principal * ($girvi->interest_rate / 100) * ($days / 30));
                break;
            case 'compound':
                // Compound monthly: A = P(1 + r/n)^(nt)
                $months = $days / 30;
                $interest = $principal * pow((1 + ($girvi->interest_rate / 100)), $months) - $principal;
                break;
        }

        // Penal Interest if overdue
        if ($girvi->maturity_date && $asOfDate->gt($girvi->maturity_date)) {
            $overdueDays = $girvi->maturity_date->diffInDays($asOfDate);
            $penalRate = $girvi->penal_interest_rate ?? ($girvi->interest_rate * 1.5);
            $penalty = ($principal * ($penalRate / 100) * ($overdueDays / 30));
        }

        return [
            'principal' => $principal,
            'interest' => round($interest, 2),
            'penalty' => round($penalty, 2),
            'total_accrued' => round($interest + $penalty, 2),
            'days' => $days,
        ];
    }

    /**
     * Enterprise: Apply rounding rules
     */
    protected function applyRounding(float $amount, string $rule): float
    {
        return match ($rule) {
            'up' => ceil($amount),
            'down' => floor($amount),
            'nearest_10' => round($amount / 10) * 10,
            default => round($amount, 2),
        };
    }
}
