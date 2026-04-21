<?php

namespace App\Services;

class GSTCalculationService
{
    // Standard GST rates in Pakistan
    private array $gstRates = [
        'Precious_Metals' => 18,
        'Gemstones' => 18,
        'Diamonds' => 18,
        'Accessories' => 18,
        'Mixed' => 18,
    ];

    /**
     * Calculate GST amount
     */
    public function calculateGST($amount, $gstPercentage = null, $materialType = null): float
    {
        if ($gstPercentage) {
            return round($amount * $gstPercentage / 100, 2);
        }

        if ($materialType && isset($this->gstRates[$materialType])) {
            $gstPercentage = $this->gstRates[$materialType];

            return round($amount * $gstPercentage / 100, 2);
        }

        return 0;
    }

    /**
     * Calculate total amount with GST
     */
    public function calculateTotalWithGST($amount, $gstPercentage = null, $materialType = null): float
    {
        $gstAmount = $this->calculateGST($amount, $gstPercentage, $materialType);

        return round($amount + $gstAmount, 2);
    }

    /**
     * Calculate net amount (amount before GST)
     */
    public function calculateNetAmount($totalAmount, $gstPercentage = null, $materialType = null): float
    {
        if (! $gstPercentage && ! $materialType) {
            return $totalAmount;
        }

        if (! $gstPercentage && $materialType && isset($this->gstRates[$materialType])) {
            $gstPercentage = $this->gstRates[$materialType];
        }

        if ($gstPercentage) {
            return round($totalAmount / (1 + $gstPercentage / 100), 2);
        }

        return $totalAmount;
    }

    /**
     * Get applicable GST rate for material type
     */
    public function getGSTRate($materialType): int
    {
        return $this->gstRates[$materialType] ?? 18;
    }

    /**
     * Calculate GST breakdown
     */
    public function getGSTBreakdown($amount, $gstPercentage = null, $materialType = null): array
    {
        $netAmount = $amount;
        $gstAmount = $this->calculateGST($amount, $gstPercentage, $materialType);
        $totalAmount = $netAmount + $gstAmount;
        $rate = $gstPercentage ?? ($materialType ? $this->gstRates[$materialType] : 0);

        return [
            'net_amount' => $netAmount,
            'gst_percentage' => $rate,
            'gst_amount' => $gstAmount,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Get all GST rates
     */
    public function getAllGSTRates(): array
    {
        return $this->gstRates;
    }

    /**
     * Set custom GST rate for material type
     */
    public function setCustomGSTRate($materialType, $rate): void
    {
        $this->gstRates[$materialType] = $rate;
    }

    /**
     * Check if material is GST exempted
     */
    public function isGSTExempted($materialType): bool
    {
        return isset($this->gstRates[$materialType]) && $this->gstRates[$materialType] === 0;
    }

    /**
     * Calculate reverse GST (for returns)
     */
    public function calculateReverseGST($totalAmount, $gstPercentage = null, $materialType = null): array
    {
        $netAmount = $this->calculateNetAmount($totalAmount, $gstPercentage, $materialType);
        $gstAmount = $totalAmount - $netAmount;

        return [
            'net_amount' => $netAmount,
            'gst_amount' => $gstAmount,
            'gst_percentage' => $gstPercentage ?? ($materialType ? $this->gstRates[$materialType] : 0),
        ];
    }

    /**
     * Calculate composite GST (CGST + SGST)
     */
    public function getCompositeGST($gstPercentage): array
    {
        $cgst = $gstPercentage / 2;
        $sgst = $gstPercentage / 2;

        return [
            'cgst' => $cgst,
            'sgst' => $sgst,
            'total' => $gstPercentage,
        ];
    }
}
