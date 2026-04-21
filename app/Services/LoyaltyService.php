<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\LoyaltyTier;
use App\Models\LoyaltyPointsLedger;
use Illuminate\Support\Facades\DB;

class LoyaltyService
{
    /**
     * Award points to a customer based on sale amount
     */
    public function awardPoints(Customer $customer, float $amount, string $sourceType = 'Sale', ?int $sourceId = null): void
    {
        $tier = $customer->loyaltyTier;
        $multiplier = $tier ? $tier->multiplier : 1.0;
        
        // Example: 1 point for every 100 currency units
        $basePoints = floor($amount / 100);
        $pointsToAward = floor($basePoints * $multiplier);

        if ($pointsToAward <= 0) {
            return;
        }

        DB::transaction(function () use ($customer, $pointsToAward, $sourceType, $sourceId) {
            LoyaltyPointsLedger::create([
                'customer_id' => $customer->id,
                'points' => $pointsToAward,
                'transaction_type' => 'earned',
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'description' => "Points earned from $sourceType",
            ]);

            $customer->increment('loyalty_points', $pointsToAward);
            $customer->increment('total_loyalty_points', $pointsToAward);

            $this->checkTierUpgrade($customer);
        });
    }

    /**
     * Redeem points for a customer
     */
    public function redeemPoints(Customer $customer, int $points, string $sourceType = 'Sale', ?int $sourceId = null): bool
    {
        if ($customer->loyalty_points < $points) {
            return false;
        }

        DB::transaction(function () use ($customer, $points, $sourceType, $sourceId) {
            LoyaltyPointsLedger::create([
                'customer_id' => $customer->id,
                'points' => -$points,
                'transaction_type' => 'redeemed',
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'description' => "Points redeemed for $sourceType",
            ]);

            $customer->decrement('loyalty_points', $points);
        });

        return true;
    }

    /**
     * Check and upgrade customer tier if eligible
     */
    public function checkTierUpgrade(Customer $customer): void
    {
        $newTier = LoyaltyTier::where('min_points', '<=', $customer->total_loyalty_points)
            ->orderBy('min_points', 'desc')
            ->first();

        if ($newTier && $customer->loyalty_tier_id != $newTier->id) {
            $customer->update(['loyalty_tier_id' => $newTier->id]);
        }
    }
}
