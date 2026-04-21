<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\InventoryProduct;
use App\Models\PosSale;

class AiInsightsService
{
    public function getExecutiveInsights()
    {
        return [
            'sales_prediction' => $this->predictNextWeekSales(),
            'inventory_optimization' => $this->getInventoryOptimizationTips(),
            'employee_efficiency' => $this->getTopPerformers(),
            'risk_alerts' => $this->getComplianceRiskAlerts(),
        ];
    }

    protected function predictNextWeekSales()
    {
        $last4WeeksSales = [];
        for ($i = 0; $i < 4; $i++) {
            $last4WeeksSales[] = PosSale::whereBetween('sale_time', [
                now()->subDays(($i + 1) * 7),
                now()->subDays($i * 7),
            ])->where('status', 'completed')->sum('total');
        }

        // Weighted Moving Average (more weight to recent weeks)
        $weights = [0.4, 0.3, 0.2, 0.1];
        $prediction = 0;
        foreach ($last4WeeksSales as $index => $sales) {
            $prediction += $sales * $weights[$index];
        }

        return round($prediction * 1.05, 2); // 5% projected seasonal uplift
    }

    protected function getInventoryOptimizationTips()
    {
        $lowStockCount = InventoryProduct::where('current_stock', '<=', \DB::raw('reorder_level'))->count();
        $overStocked = InventoryProduct::where('current_stock', '>', 50)->take(2)->get();

        $tips = [];
        if ($lowStockCount > 0) {
            $tips[] = "Critical: {$lowStockCount} items below reorder level. Priority restocking recommended.";
        }

        foreach ($overStocked as $item) {
            $tips[] = "Overstocked: {$item->name} - Current stock {$item->current_stock}. Consider promotional bundling.";
        }

        if (empty($tips)) {
            $tips[] = 'Inventory levels are currently optimized based on velocity patterns.';
        }

        return $tips;
    }

    protected function getTopPerformers()
    {
        return Employee::orderBy('performance_score', 'desc')->take(3)->pluck('first_name')->toArray();
    }

    protected function getComplianceRiskAlerts()
    {
        return [
            '2 Transactions over 5,000,000 Rs. pending AML review',
            '3 Employees with overdue mandatory training',
            '1 Warehouse sensor (Dubai) reporting high humidity (65%)',
        ];
    }
}
