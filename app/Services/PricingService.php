<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\InventoryProduct;

class PricingService
{
    public function calculateDynamicPrice(InventoryProduct $product, ?Customer $customer = null)
    {
        $basePrice = $product->sale_price;
        $dynamicAdjustment = 0;

        // AI Logic: Demand-based pricing
        if ($product->stock_quantity < 5) {
            $dynamicAdjustment += ($basePrice * 0.05); // Price up by 5% if low stock
        }

        // AI Logic: Time-based pricing (Happy Hour)
        $hour = now()->hour;
        if ($hour >= 10 && $hour <= 12) {
            $dynamicAdjustment -= ($basePrice * 0.03); // 3% discount in morning
        }

        // Customer Tier Pricing
        if ($customer && $customer->loyalty_points > 1000) {
            $dynamicAdjustment -= ($basePrice * 0.05); // Extra 5% for VIP
        }

        return $basePrice + $dynamicAdjustment;
    }

    public function generateAiPromotions()
    {
        // Mockup: Identify slow-moving items and suggest promotions
        return [
            [
                'item' => 'Diamond Ring X1',
                'suggested_discount' => '15%',
                'reason' => 'No sales in 30 days',
            ],
            [
                'item' => 'Gold Chain 22K',
                'suggested_discount' => 'BOGO',
                'reason' => 'High inventory (50+ units)',
            ],
        ];
    }
}
