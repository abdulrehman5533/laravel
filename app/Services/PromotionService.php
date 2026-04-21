<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\Coupon;
use App\Models\InventoryProduct;
use Carbon\Carbon;

class PromotionService
{
    /**
     * Validate if a promotion is currently applicable
     */
    public function isValid(Promotion $promotion, float $totalAmount = 0): bool
    {
        $now = Carbon::now();

        if (!$promotion->is_active) {
            return false;
        }

        if ($promotion->start_date && $now->lt($promotion->start_date)) {
            return false;
        }

        if ($promotion->end_date && $now->gt($promotion->end_date)) {
            return false;
        }

        $rules = $promotion->rules ?? [];

        if (isset($rules['min_amount']) && $totalAmount < $rules['min_amount']) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount for a given promotion and amount
     */
    public function calculateDiscount(Promotion $promotion, float $amount, array $items = []): float
    {
        if (!$this->isValid($promotion, $amount)) {
            return 0;
        }

        $discount = 0;

        if ($promotion->type === 'percentage') {
            $discount = ($amount * $promotion->value) / 100;
        } elseif ($promotion->type === 'fixed') {
            $discount = $promotion->value;
        }

        // Apply max discount limit if set in rules
        $rules = $promotion->rules ?? [];
        if (isset($rules['max_discount']) && $discount > $rules['max_discount']) {
            $discount = $rules['max_discount'];
        }

        return min($discount, $amount);
    }

    /**
     * Validate and apply a coupon code
     */
    public function validateCoupon(string $code, float $totalAmount = 0): array
    {
        $coupon = Coupon::where('code', $code)->with('promotion')->first();

        if (!$coupon) {
            return ['valid' => false, 'message' => 'Invalid coupon code.'];
        }

        if (!$this->isValid($coupon->promotion, $totalAmount)) {
            return ['valid' => false, 'message' => 'Coupon is not applicable at this time.'];
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return ['valid' => false, 'message' => 'Coupon usage limit reached.'];
        }

        if ($coupon->min_purchase_amount && $totalAmount < $coupon->min_purchase_amount) {
            return ['valid' => false, 'message' => 'Minimum purchase amount of ' . $coupon->min_purchase_amount . ' not met.'];
        }

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount_amount' => $this->calculateDiscount($coupon->promotion, $totalAmount)
        ];
    }

    /**
     * Increment coupon usage
     */
    public function incrementUsage(Coupon $coupon): void
    {
        $coupon->increment('used_count');
    }
}
