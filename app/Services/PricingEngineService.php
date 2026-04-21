<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\GoldRate;
use App\Models\InventoryProduct;
use App\Models\PosPricingTier;

class PricingEngineService
{
    /**
     * Calculate the final price for a product based on customer context
     */
    public function calculatePrice(InventoryProduct $product, ?Customer $customer = null, int $quantity = 1): array
    {
        $basePrice = $product->selling_price;
        $goldRate = GoldRate::getTodayRate();
        $metalPrice = 0;

        // 1. Calculate Metal Component if applicable
        if ($product->purity && $goldRate) {
            $purityName = $product->purity->name;
            $ratePerGram = $goldRate->getRateByPurity($purityName);
            $metalPrice = $ratePerGram * $product->net_weight;
        }

        // 2. Determine Base Pricing (Retail vs Wholesale)
        if ($customer && $customer->customer_type === 'wholesale') {
            $price = $this->applyWholesaleTiers($product, $quantity);
        } else {
            $price = $this->applyRetailPricing($product);
        }

        // 3. Apply Customer-Specific Price Lists
        if ($customer && $customer->price_list_id) {
            $price = $this->applyCustomPriceList($price, $customer->price_list_id, $product);
        }

        // 4. Calculate Making Charges & Wastage
        $makingCharges = $this->calculateMakingCharges($product, $quantity);
        $wastageCharges = $this->calculateWastageCharges($product, $metalPrice);

        $totalBeforeTax = $metalPrice + $makingCharges + $wastageCharges + ($product->stone_price ?? 0);

        return [
            'metal_price' => $metalPrice,
            'making_charges' => $makingCharges,
            'wastage_charges' => $wastageCharges,
            'stone_price' => $product->stone_price ?? 0,
            'base_price' => $price,
            'total_before_tax' => $totalBeforeTax,
            'currency' => 'PKR', // Default from config
        ];
    }

    /**
     * Apply Wholesale Tier Logic
     */
    private function applyWholesaleTiers(InventoryProduct $product, int $quantity): float
    {
        // Logic to fetch tier based on quantity
        $tier = PosPricingTier::where('min_quantity', '<=', $quantity)
            ->where('max_quantity', '>=', $quantity)
            ->first();

        if ($tier) {
            return $product->selling_price * (1 - ($tier->discount_percent / 100));
        }

        return $product->wholesale_price ?? $product->selling_price;
    }

    private function applyRetailPricing(InventoryProduct $product): float
    {
        return $product->selling_price;
    }

    private function applyCustomPriceList(float $currentPrice, int $priceListId, InventoryProduct $product): float
    {
        // Placeholder for Custom Price List Logic
        return $currentPrice;
    }

    private function calculateMakingCharges(InventoryProduct $product, int $quantity): float
    {
        if ($product->making_charge_type === 'per_gram') {
            return $product->making_charge_value * $product->net_weight * $quantity;
        }

        return $product->making_charge_value * $quantity;
    }

    private function calculateWastageCharges(InventoryProduct $product, float $metalPrice): float
    {
        return ($metalPrice * ($product->wastage_percentage ?? 0)) / 100;
    }
}
