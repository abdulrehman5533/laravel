<?php

namespace App\Services\Inventory;

use App\Models\InventoryProduct;
use App\Models\InventoryProductIntelligence;

class StockAIService
{
    /**
     * Run intelligence analysis for a product
     */
    public function analyzeProduct(InventoryProduct $product)
    {
        $movements = $product->stockMovements()
            ->where('type', 'subtract')
            ->where('reference', 'like', 'sale:%')
            ->orderBy('created_at', 'desc')
            ->get();

        $lastSoldAt = $movements->first()?->created_at;
        $ageInDays = $product->getAgeInDays();

        // Calculate Movement Speed
        $monthlySales = $this->calculateMonthlySales($movements);
        $speed = $this->determineMovementSpeed($monthlySales, $ageInDays, $product->current_stock);

        // Calculate Risk Score (0-100)
        $riskScore = $this->calculateRiskScore($ageInDays, $speed, $product->current_stock);

        // Generate AI Suggestions
        $suggestions = $this->generateSuggestions($speed, $riskScore, $product);

        return InventoryProductIntelligence::updateOrCreate(
            ['product_id' => $product->id],
            [
                'risk_score' => $riskScore,
                'movement_speed' => $speed,
                'last_sold_at' => $lastSoldAt,
                'ai_suggestions' => $suggestions,
                'last_analyzed_at' => now(),
            ]
        );
    }

    protected function calculateMonthlySales($movements)
    {
        $threeMonthsAgo = now()->subMonths(3);
        $recentMovements = $movements->where('created_at', '>=', $threeMonthsAgo);

        return $recentMovements->sum('quantity') / 3;
    }

    protected function determineMovementSpeed($monthlySales, $ageInDays, $currentStock)
    {
        if ($monthlySales > ($currentStock * 0.5)) {
            return 'fast';
        }
        if ($monthlySales > 0) {
            return 'medium';
        }
        if ($ageInDays > 180 && $monthlySales == 0) {
            return 'dead';
        }

        return 'slow';
    }

    protected function calculateRiskScore($ageInDays, $speed, $currentStock)
    {
        $score = 0;

        // Aging factor (max 40 points)
        $score += min(40, ($ageInDays / 365) * 40);

        // Speed factor (max 40 points)
        if ($speed === 'dead') {
            $score += 40;
        }
        if ($speed === 'slow') {
            $score += 25;
        }
        if ($speed === 'medium') {
            $score += 10;
        }

        // Stock volume factor (max 20 points)
        if ($currentStock > 10) {
            $score += 20;
        } elseif ($currentStock > 5) {
            $score += 10;
        }

        return min(100, $score);
    }

    protected function generateSuggestions($speed, $riskScore, $product)
    {
        $suggestions = [];

        if ($riskScore > 70) {
            $suggestions[] = [
                'type' => 'critical',
                'action' => 'Convert Metal',
                'reason' => 'Item is dead stock and represents high capital lockup.',
            ];
            $suggestions[] = [
                'type' => 'warning',
                'action' => 'Clearance Discount (20%+)',
                'reason' => 'High risk of capital stagnation.',
            ];
        } elseif ($riskScore > 40) {
            $suggestions[] = [
                'type' => 'info',
                'action' => 'Inter-branch Transfer',
                'reason' => 'Consider moving to a branch with higher demand for this category.',
            ];
            $suggestions[] = [
                'type' => 'info',
                'action' => 'Marketing Push',
                'reason' => 'Slow moving item, needs better visibility.',
            ];
        }

        if ($speed === 'fast' && $product->current_stock <= $product->reorder_level * 2) {
            $suggestions[] = [
                'type' => 'success',
                'action' => 'Restock Priority',
                'reason' => 'Fast moving item with decreasing stock levels.',
            ];
        }

        return $suggestions;
    }

    /**
     * Batch analyze all products
     */
    public function analyzeAll()
    {
        $products = InventoryProduct::active()->get();
        foreach ($products as $product) {
            $this->analyzeProduct($product);
        }

        return $products->count();
    }
}
