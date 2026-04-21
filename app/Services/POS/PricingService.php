<?php

namespace App\Services\POS;

use App\Models\Customer;
use App\Models\GoldRate;
use App\Models\PosSale;

class PricingService
{
    /**
     * Enterprise Logic: Get applicable metal rate for a customer/sale
     */
    public function getApplicableRate(
        ?Customer $customer = null,
        string $metalType = 'gold',
        ?string $purity = null,
        ?PosSale $sale = null
    ): float {
        // 1. Check for locked rate in the sale
        if ($sale && $sale->rate_locked && $sale->locked_rate > 0) {
            return (float) $sale->locked_rate;
        }

        // 2. Fetch today's base rate
        $todayRate = GoldRate::getTodayRate();
        if (! $todayRate) {
            throw new \Exception("Today's metal rate not found or not approved.");
        }

        $baseRate = $todayRate->getRateByPurity($purity);

        // 3. Check for customer-specific contract
        if ($customer) {
            $contract = $customer->getActiveRateContract($metalType);
            if ($contract) {
                return $contract->applyToRate($baseRate);
            }
        }

        return $baseRate;
    }

    /**
     * Calculate making charges based on weight and rate
     */
    public function calculateMakingCharges(float $weight, float $ratePerGram, string $materialType = 'gold'): float
    {
        return round($weight * $ratePerGram, 2);
    }

    /**
     * Calculate wastage amount (percentage-based)
     */
    public function calculateWastage(float $amount, float $wastagePercent): float
    {
        return round(($amount * $wastagePercent) / 100, 2);
    }

    /**
     * Calculate GST based on material type or custom rate
     */
    public function calculateGST(float $baseAmount, string $materialType = 'gold'): float
    {
        $gstRates = [
            'gold' => 0.05,        // 5%
            'silver' => 0.05,      // 5%
            'diamond' => 0.00,     // 0%
            'gemstone' => 0.05,    // 5%
            'accessory' => 0.18,   // 18%
            'mixed' => 0.18,       // 18%
        ];

        $rate = $gstRates[strtolower($materialType)] ?? 0.18;

        return round($baseAmount * $rate, 2);
    }

    /**
     * Apply wholesale discount tier
     */
    public function applyWholesaleDiscount(float $totalAmount, string $customerType): float
    {
        $discounts = [
            'vip' => 0.15,      // 15%
            'wholesale' => 0.10, // 10%
            'regular' => 0.00,  // No discount
        ];

        $discount = $discounts[$customerType] ?? 0;

        return round($totalAmount * (1 - $discount), 2);
    }

    /**
     * Apply loyalty points to reduce sale amount
     */
    public function applyLoyaltyPoints(float $totalAmount, int $pointsToRedeem, float $pointValue = 0.01): float
    {
        $reduction = $pointsToRedeem * $pointValue;

        return max(0, $totalAmount - $reduction);
    }

    /**
     * Calculate loyalty points earned from sale
     */
    public function calculateLoyaltyPoints(float $totalAmount, float $pointsPerCurrency = 0.01): int
    {
        return (int) floor($totalAmount * $pointsPerCurrency);
    }

    /**
     * Get multi-currency exchange rates
     */
    public function getExchangeRate(string $fromCurrency, string $toCurrency): float
    {
        // TODO: Integrate with live currency API
        $rates = [
            'USD' => 280.0,
            'EUR' => 300.0,
            'GBP' => 350.0,
            'PKR' => 1.0,
            'AED' => 76.0,
        ];

        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        $baseAmount = $rates[$fromCurrency] ?? 1;
        $targetAmount = $rates[$toCurrency] ?? 1;

        return round($targetAmount / $baseAmount, 6);
    }

    /**
     * Calculate line-item total with all charges
     */
    public function calculateLineTotal(
        float $qty,
        float $unitPrice,
        float $makingCharge = 0,
        float $wastagePercent = 0,
        float $taxPercent = 0
    ): float {
        $subtotal = $qty * $unitPrice;
        $wastage = ($wastagePercent / 100) * $subtotal;
        $beforeTax = $subtotal + $makingCharge + $wastage;
        $tax = ($taxPercent / 100) * $beforeTax;

        return round($beforeTax + $tax, 2);
    }
}
