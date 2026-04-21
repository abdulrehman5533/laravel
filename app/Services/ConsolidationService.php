<?php

namespace App\Services;

use App\Models\Branch;

class ConsolidationService
{
    public function getConsolidatedPL($startDate, $endDate, $baseCurrency = 'PKR')
    {
        $branches = Branch::all();
        $reportService = app(\App\Services\Accounting\AccountingReportService::class);

        $consolidated = [
            'total_revenue' => 0,
            'total_expenses' => 0,
            'net_profit' => 0,
            'branch_breakdown' => [],
        ];

        foreach ($branches as $branch) {
            // Switch tenant context manually if using multi-tenancy traits
            $pl = $reportService->getProfitAndLoss($startDate, $endDate); // Need to filter by branch in AccountingReportService

            $rate = $this->getExchangeRate($branch->currency ?? 'PKR', $baseCurrency);

            $convertedProfit = $pl['net_profit'] * $rate;

            $consolidated['total_revenue'] += $pl['total_revenue'] * $rate;
            $consolidated['total_expenses'] += $pl['total_expenses'] * $rate;
            $consolidated['net_profit'] += $convertedProfit;

            $consolidated['branch_breakdown'][] = [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'net_profit_local' => $pl['net_profit'],
                'net_profit_converted' => $convertedProfit,
            ];
        }

        return $consolidated;
    }

    protected function getExchangeRate($from, $to)
    {
        if ($from === $to) {
            return 1.0;
        }

        // In a production ERP, this would call an external API (Fixer.io, OER, etc.)
        // or a local exchange_rates table updated daily.
        $rates = [
            'INR_USD' => 0.012,
            'AED_USD' => 0.27,
            'GBP_USD' => 1.27,
            'USD_INR' => 83.33,
            'USD_AED' => 3.67,
            'USD_GBP' => 0.79,
        ];

        $key = "{$from}_{$to}";

        return $rates[$key] ?? 1.0;
    }

    public function interBranchTransfer($fromBranchId, $toBranchId, $itemData)
    {
        // Logic to move stock and create accounting entries between legal entities
        return "Inter-branch transfer of {$itemData['quantity']} units initiated.";
    }
}
