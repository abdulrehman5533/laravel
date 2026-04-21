<?php

namespace App\Services\Security;

use App\Models\Girvi;
use App\Models\PurityOverrideLog;
use Illuminate\Support\Facades\DB;

class FraudDetectionService
{
    /**
     * Detect frequent purity overrides (Red Flag for Karigar/Staff theft)
     */
    public function detectPurityOverrideAbuse(int $userId, int $days = 30): array
    {
        $overrides = PurityOverrideLog::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        $suspicious = $overrides->filter(function ($log) {
            return abs($log->original_purity - $log->new_purity) > 2.0; // More than 2% deviation
        });

        return [
            'total_overrides' => $overrides->count(),
            'suspicious_overrides' => $suspicious->count(),
            'risk_level' => $suspicious->count() > 3 ? 'High' : 'Low',
        ];
    }

    /**
     * Detect Girvi manipulation (e.g., undervalued items to issue higher loans)
     */
    public function detectGirviValuationFraud(Girvi $girvi): bool
    {
        foreach ($girvi->items as $item) {
            $expectedFine = $item->fine_weight;
            $valuation = $item->estimated_value;

            // Check if valuation per gram is significantly higher than today's market rate
            $marketRate = \App\Models\GoldRate::getTodayRate()?->rate_24k ?? 0;
            if ($marketRate > 0) {
                $valuePerFineGram = $valuation / ($expectedFine ?: 1);
                if ($valuePerFineGram > ($marketRate * 1.1)) { // 10% higher than market
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Detect "Silent Theft" - Small stock adjustments over time
     */
    public function detectInventoryShrinkage(int $productId): array
    {
        $adjustments = DB::table('stock_movements')
            ->where('product_id', $productId)
            ->whereIn('type', ['subtract', 'wastage', 'damage'])
            ->where('created_at', '>=', now()->subMonths(6))
            ->sum('weight');

        $totalSales = DB::table('pos_sale_items')
            ->where('product_id', $productId)
            ->sum('weight');

        $shrinkageRatio = ($totalSales > 0) ? ($adjustments / $totalSales) : 0;

        return [
            'total_adjustments_weight' => $adjustments,
            'shrinkage_ratio' => round($shrinkageRatio, 4),
            'is_suspicious' => $shrinkageRatio > 0.05, // More than 5% loss outside sales
        ];
    }
}
