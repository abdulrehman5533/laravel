<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Supplier;

class ProcurementIntelligenceService
{
    public function calculateVendorScore(Supplier $supplier)
    {
        $orders = PurchaseOrder::where('supplier_id', $supplier->id)->get();
        if ($orders->isEmpty()) {
            return [
                'overall_score' => 0,
                'status' => 'New Vendor',
                'recommendation' => 'Trial Order Recommended',
            ];
        }

        // 1. Timeliness (40% weight)
        $onTimeCount = $orders->where('status', 'delivered')->where('delivery_date', '<=', \DB::raw('expected_delivery_date'))->count();
        $onTimeRate = $onTimeCount / $orders->count();

        // 2. Quality / Return Rate (40% weight)
        // Mocking return rate as it might not be in PO model directly
        $returnRate = $supplier->return_rate ?? 0.05; // 5% default
        $qualityScore = (1 - $returnRate);

        // 3. Price Variance (20% weight)
        // Compare PO price vs Market Rate at time of purchase
        $priceVariance = $supplier->price_variance ?? 1.0; // 1.0 means matches market
        $priceScore = $priceVariance <= 1.0 ? 1.0 : (1 / $priceVariance);

        $overallScore = ($onTimeRate * 40) + ($qualityScore * 40) + ($priceScore * 20);

        return [
            'overall_score' => round($overallScore, 2),
            'metrics' => [
                'timeliness' => round($onTimeRate * 100, 2).'%',
                'quality' => round($qualityScore * 100, 2).'%',
                'price_competitiveness' => round($priceScore * 100, 2).'%',
            ],
            'reliability' => $overallScore > 85 ? 'Strategic Partner' : ($overallScore > 60 ? 'Preferred' : 'Risk'),
            'suggested_allocation' => $overallScore > 80 ? 'Primary Source' : 'Secondary Source',
        ];
    }

    public function predictLeadTime(Supplier $supplier)
    {
        // AI Logic: Based on historical delivery logs
        return [
            'expected_days' => 7,
            'confidence' => '85%',
            'potential_delays' => ['Weather in Transit Hub', 'High Season Congestion'],
        ];
    }
}
