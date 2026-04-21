<?php

namespace App\Services\POS;

use App\Models\Customer;

class LoyaltyService
{
    /**
     * Add loyalty points to customer
     */
    public function addPoints(Customer $customer, int $points, string $reason = 'purchase'): void
    {
        $customer->increment('loyalty_points', $points);
    }

    /**
     * Redeem loyalty points
     */
    public function redeemPoints(Customer $customer, int $points): bool
    {
        if ($customer->loyalty_points < $points) {
            return false;
        }

        $customer->decrement('loyalty_points', $points);

        return true;
    }

    /**
     * Calculate discount from points
     */
    public function getDiscountFromPoints(int $points, float $pointValue = 0.01): float
    {
        return round($points * $pointValue, 2);
    }

    /**
     * Get customer tier based on points
     */
    public function getCustomerTier(Customer $customer): string
    {
        $points = $customer->loyalty_points;

        return match (true) {
            $points >= 10000 => 'platinum',
            $points >= 5000 => 'gold',
            $points >= 1000 => 'silver',
            default => 'bronze',
        };
    }

    /**
     * Upgrade customer to VIP if conditions met
     */
    public function checkAndUpgradeVIP(Customer $customer): void
    {
        if ($customer->customer_type === 'vip') {
            return;
        }

        // Criteria: Rs. 500,000 lifetime purchase + 100+ points
        $totalSpend = $customer->sales()->sum('total');
        if ($totalSpend >= 500000 && $customer->loyalty_points >= 100) {
            $customer->update(['customer_type' => 'vip']);
        }
    }

    /**
     * Get VIP customer benefits
     */
    public function getVIPBenefits(Customer $customer): array
    {
        if ($customer->membership_level !== 'platinum' && $customer->customer_type !== 'vip') {
            return [];
        }

        return [
            'discount_percent' => 15,
            'points_multiplier' => 1.5,
            'free_shipping' => true,
            'priority_support' => true,
        ];
    }
}
