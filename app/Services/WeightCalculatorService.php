<?php

namespace App\Services;

class WeightCalculatorService
{
    // Weight constants (in grams)
    private const TOLA_TO_GRAMS = 11.6638038;

    private const SUNARI_RATTI_TO_GRAMS = 0.121497956;

    private const CARAT_TO_GRAMS = 0.2;

    private const TROY_OUNCE_TO_GRAMS = 31.1034768;

    private const STANDARD_OUNCE_TO_GRAMS = 28.3495231;

    // Karat to purity percentage
    private const KARAT_PURITY = [
        24 => 99.9,
        22 => 91.7,
        21 => 87.5,
        20 => 83.3,
        18 => 75,
        14 => 58.3,
        10 => 41.7,
    ];

    /**
     * Convert any weight to grams
     */
    public function toGrams($value, $unit, $rattiType = 'sunari'): float
    {
        $unit = strtolower(trim($unit));

        return match ($unit) {
            'g', 'gram', 'grams' => (float) $value,
            'mg', 'milligram', 'milligrams' => (float) $value / 1000,
            'tola', 'tolas' => (float) $value * self::TOLA_TO_GRAMS,
            'ratti', 'rattis' => $this->rattiToGrams((float) $value, $rattiType),
            'carat', 'carats', 'ct' => (float) $value * self::CARAT_TO_GRAMS,
            'troy_oz', 'troy_ounce', 'troy ounce' => (float) $value * self::TROY_OUNCE_TO_GRAMS,
            'oz', 'ounce', 'ounces' => (float) $value * self::STANDARD_OUNCE_TO_GRAMS,
            default => throw new \InvalidArgumentException("Unknown weight unit: {$unit}"),
        };
    }

    /**
     * Convert ratti to grams based on type
     */
    private function rattiToGrams(float $value, string $rattiType): float
    {
        $sunariGrams = $value * self::SUNARI_RATTI_TO_GRAMS;

        return match ($rattiType) {
            'sunari' => $sunariGrams,
            'pakki' => $sunariGrams * 1.5,
            default => $sunariGrams,
        };
    }

    /**
     * Convert grams to any unit
     */
    public function fromGrams($grams, $unit, $rattiType = 'sunari'): float
    {
        $grams = (float) $grams;
        $unit = strtolower(trim($unit));

        return match ($unit) {
            'g', 'gram', 'grams' => $grams,
            'mg', 'milligram', 'milligrams' => $grams * 1000,
            'tola', 'tolas' => $grams / self::TOLA_TO_GRAMS,
            'ratti', 'rattis' => $this->gramsToRatti($grams, $rattiType),
            'carat', 'carats', 'ct' => $grams / self::CARAT_TO_GRAMS,
            'troy_oz', 'troy_ounce' => $grams / self::TROY_OUNCE_TO_GRAMS,
            'oz', 'ounce', 'ounces' => $grams / self::STANDARD_OUNCE_TO_GRAMS,
            default => throw new \InvalidArgumentException("Unknown weight unit: {$unit}"),
        };
    }

    /**
     * Convert grams to ratti
     */
    private function gramsToRatti(float $grams, string $rattiType): float
    {
        $sunariRatti = $grams / self::SUNARI_RATTI_TO_GRAMS;

        return match ($rattiType) {
            'sunari' => $sunariRatti,
            'pakki' => $sunariRatti / 1.5,
            default => $sunariRatti,
        };
    }

    /**
     * Auto-detect unit from input string
     */
    public function detectUnit($input): ?string
    {
        $input = strtolower(trim($input));

        $patterns = [
            '/tola/' => 'tola',
            '/ratti/' => 'ratti',
            '/\bct\b/' => 'carat',
            '/carat/' => 'carat',
            '/mg/' => 'mg',
            '/milligram/' => 'mg',
            '/troy\s*oz|troy\s*ounce/' => 'troy_oz',
            '/\boz\b/' => 'oz',
            '/ounce/' => 'oz',
            '/\bg\b/' => 'g',
            '/gram/' => 'g',
        ];

        foreach ($patterns as $pattern => $unit) {
            if (preg_match($pattern, $input)) {
                return $unit;
            }
        }

        return null;
    }

    /**
     * Extract numeric value from input with unit
     */
    public function extractValue($input): ?float
    {
        if (preg_match('/^([\d.]+)/', $input, $matches)) {
            return (float) $matches[1];
        }

        return null;
    }

    /**
     * Get karat purity percentage
     */
    public function getKaratPurity($karat): float
    {
        return self::KARAT_PURITY[$karat] ?? 0;
    }

    /**
     * Calculate pure metal weight from gross weight and karat
     */
    public function calculatePureMetalWeight($grossWeight, $karat): float
    {
        $purity = $this->getKaratPurity($karat) / 100;

        return $grossWeight * $purity;
    }

    /**
     * Calculate metal value
     */
    public function calculateMetalValue($grossWeight, $karat, $ratePerGram): array
    {
        $pureWeight = $this->calculatePureMetalWeight($grossWeight, $karat);
        $pureValue = $pureWeight * $ratePerGram;

        return [
            'pure_weight' => round($pureWeight, 4),
            'pure_value' => round($pureValue, 2),
        ];
    }

    /**
     * Calculate wastage
     */
    public function calculateWastage($grossWeight, $karat, $wastageType, $wastageValue, $ratePerGram): array
    {
        $pureWeight = $this->calculatePureMetalWeight($grossWeight, $karat);

        if ($wastageType === 'percentage') {
            $wastageWeight = ($pureWeight * $wastageValue) / 100;
        } else {
            $wastageWeight = $wastageValue; // fixed grams
        }

        $wastageAmount = $wastageWeight * $ratePerGram;

        return [
            'wastage_weight' => round($wastageWeight, 4),
            'wastage_value' => round($wastageAmount, 2),
        ];
    }

    /**
     * Calculate making charges
     */
    public function calculateMakingCharges($grossWeight, $karat, $ratePerGram, $chargeType, $chargeValue): array
    {
        $pureWeight = $this->calculatePureMetalWeight($grossWeight, $karat);
        $pureValue = $pureWeight * $ratePerGram;

        if ($chargeType === 'per_gram') {
            $amount = $grossWeight * $chargeValue;
            $description = 'Per gram: Rs. '.number_format($chargeValue, 2).'/g × '.number_format($grossWeight, 4).'g';
        } elseif ($chargeType === 'per_piece') {
            $amount = $chargeValue;
            $description = 'Per piece: Rs. '.number_format($chargeValue, 2);
        } else { // percentage
            $amount = ($pureValue * $chargeValue) / 100;
            $description = 'Percentage: '.number_format($chargeValue, 2).'% of metal value';
        }

        return [
            'making_charges' => round($amount, 2),
            'description' => $description,
        ];
    }

    /**
     * Calculate stone cost
     */
    public function calculateStonesCost($stoneWeight, $stoneUnit, $pricePerCarat, $rattiType = 'sunari'): array
    {
        if (! $stoneWeight || ! $pricePerCarat) {
            return [
                'stone_weight_carats' => 0,
                'stone_cost' => 0,
            ];
        }

        $stoneWeightInGrams = $this->toGrams($stoneWeight, $stoneUnit, $rattiType);
        $stoneWeightInCarats = $this->fromGrams($stoneWeightInGrams, 'carat');
        $stoneCost = $stoneWeightInCarats * $pricePerCarat;

        return [
            'stone_weight_carats' => round($stoneWeightInCarats, 4),
            'stone_cost' => round($stoneCost, 2),
        ];
    }

    /**
     * Complete price breakdown calculation
     */
    public function calculatePriceBreakdown(
        $grossWeight,
        $unit,
        $karat,
        $ratePerGram,
        $wastageType,
        $wastageValue,
        $makingChargeType,
        $makingChargeValue,
        $stoneWeight,
        $stoneUnit,
        $stonePrice,
        $taxPercentage,
        $discountPercentage,
        $rattiType,
        $customCharges
    ): array {
        try {
            // Convert gross weight to grams
            $grossWeightGrams = $this->toGrams($grossWeight, $unit, $rattiType);

            // Metal calculations
            $metalCalc = $this->calculateMetalValue($grossWeightGrams, $karat, $ratePerGram);

            // Wastage
            $wastageCalc = $this->calculateWastage($grossWeightGrams, $karat, $wastageType, $wastageValue, $ratePerGram);

            // Making charges
            $makingCalc = $this->calculateMakingCharges($grossWeightGrams, $karat, $ratePerGram, $makingChargeType, $makingChargeValue);

            // Stones
            $stoneCalc = $this->calculateStonesCost($stoneWeight, $stoneUnit, $stonePrice, $rattiType);

            // Subtotal
            $subtotal = $metalCalc['pure_value'] + $wastageCalc['wastage_value'] + $makingCalc['making_charges'] + $stoneCalc['stone_cost'] + ($customCharges ?? 0);

            // Tax
            $taxAmount = ($subtotal * $taxPercentage) / 100;

            // Discount
            $discountAmount = ($subtotal * $discountPercentage) / 100;

            // Final total
            $finalTotal = $subtotal + $taxAmount - $discountAmount;

            return [
                'success' => true,
                'gross_weight_input' => $grossWeight,
                'input_unit' => $unit,
                'gross_weight_grams' => round($grossWeightGrams, 4),
                'karat' => $karat,
                'purity_percentage' => $this->getKaratPurity($karat),
                'pure_weight' => $metalCalc['pure_weight'],
                'rate_per_gram' => $ratePerGram,
                'metal_value' => $metalCalc['pure_value'],
                'wastage_type' => $wastageType,
                'wastage_input' => $wastageValue,
                'wastage_weight' => $wastageCalc['wastage_weight'],
                'wastage_value' => $wastageCalc['wastage_value'],
                'making_charges' => $makingCalc['making_charges'],
                'making_charge_description' => $makingCalc['description'],
                'stone_weight' => $stoneWeight ?? 0,
                'stone_unit' => $stoneUnit,
                'stone_weight_carats' => $stoneCalc['stone_weight_carats'],
                'stone_price_per_carat' => $stonePrice ?? 0,
                'stone_total_cost' => $stoneCalc['stone_cost'],
                'custom_charges' => $customCharges ?? 0,
                'subtotal' => round($subtotal, 2),
                'tax_percentage' => $taxPercentage,
                'tax_amount' => round($taxAmount, 2),
                'discount_percentage' => $discountPercentage,
                'discount_amount' => round($discountAmount, 2),
                'final_total' => round($finalTotal, 2),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate old gold exchange deduction
     */
    public function calculateOldGoldExchange($oldGoldWeight, $oldGoldKarat, $ratePerGram): array
    {
        $pureWeight = $this->calculatePureMetalWeight($oldGoldWeight, $oldGoldKarat);
        $exchangeValue = $pureWeight * $ratePerGram;
        return [
            'old_gold_weight'    => round($oldGoldWeight, 4),
            'old_gold_karat'     => $oldGoldKarat,
            'old_gold_pure_wt'   => round($pureWeight, 4),
            'exchange_value'     => round($exchangeValue, 2),
        ];
    }

    /**
     * Calculate multiple stones total cost
     */
    public function calculateMultipleStones(array $stones, $rattiType = 'sunari'): array
    {
        $totalCost   = 0;
        $totalCarats = 0;
        $details     = [];
        foreach ($stones as $stone) {
            if (empty($stone['weight']) || empty($stone['price_per_carat'])) continue;
            $calc = $this->calculateStonesCost($stone['weight'], $stone['unit'] ?? 'carat', $stone['price_per_carat'], $rattiType);
            $totalCost   += $calc['stone_cost'];
            $totalCarats += $calc['stone_weight_carats'];
            $details[]    = array_merge($stone, $calc);
        }
        return ['total_cost' => round($totalCost, 2), 'total_carats' => round($totalCarats, 4), 'details' => $details];
    }

    /**
     * Complete price breakdown with all new features
     */
    public function calculateFullBreakdown(array $input): array
    {
        $grossWeightGrams = $this->toGrams($input['gross_weight'], $input['unit'], $input['ratti_type'] ?? 'sunari');
        $stoneWeightGrams = isset($input['stone_weight']) && $input['stone_weight'] > 0
            ? $this->toGrams($input['stone_weight'], $input['stone_unit'] ?? 'carat', $input['ratti_type'] ?? 'sunari')
            : 0;
        $netMetalWeight = $grossWeightGrams - $stoneWeightGrams;

        // Metal value
        $metalCalc   = $this->calculateMetalValue($netMetalWeight, $input['karat'], $input['rate_per_gram']);
        $wastageCalc = $this->calculateWastage($netMetalWeight, $input['karat'], $input['wastage_type'], $input['wastage_value'], $input['rate_per_gram']);
        $makingCalc  = $this->calculateMakingCharges($netMetalWeight, $input['karat'], $input['rate_per_gram'], $input['making_charge_type'], $input['making_charge_value']);

        // Multiple stones
        $stonesCalc = $this->calculateMultipleStones($input['stones'] ?? [], $input['ratti_type'] ?? 'sunari');

        // Hallmark charges
        $hallmarkCharge = (float)($input['hallmark_charge'] ?? 0);

        // Custom charges
        $customCharges = (float)($input['custom_charges'] ?? 0);

        // Profit margin
        $subtotalBeforeProfit = $metalCalc['pure_value'] + $wastageCalc['wastage_value']
            + $makingCalc['making_charges'] + $stonesCalc['total_cost']
            + $hallmarkCharge + $customCharges;

        $profitMargin  = (float)($input['profit_margin'] ?? 0);
        $profitAmount  = ($subtotalBeforeProfit * $profitMargin) / 100;
        $subtotal      = $subtotalBeforeProfit + $profitAmount;

        // Tax
        $taxAmount = ($subtotal * (float)($input['tax_percentage'] ?? 0)) / 100;

        // Discount
        $discountAmount = ($subtotal * (float)($input['discount_percentage'] ?? 0)) / 100;

        // Old gold exchange
        $oldGoldCalc    = ['exchange_value' => 0];
        $oldGoldDeduct  = 0;
        if (!empty($input['old_gold_weight']) && $input['old_gold_weight'] > 0) {
            $oldGoldCalc   = $this->calculateOldGoldExchange($input['old_gold_weight'], $input['old_gold_karat'] ?? 22, $input['rate_per_gram']);
            $oldGoldDeduct = $oldGoldCalc['exchange_value'];
        }

        $finalTotal = $subtotal + $taxAmount - $discountAmount - $oldGoldDeduct;

        return [
            'gross_weight_grams'   => round($grossWeightGrams, 4),
            'net_metal_weight'     => round($netMetalWeight, 4),
            'karat'                => $input['karat'],
            'purity_percentage'    => $this->getKaratPurity($input['karat']),
            'pure_weight'          => $metalCalc['pure_weight'],
            'rate_per_gram'        => $input['rate_per_gram'],
            'metal_value'          => $metalCalc['pure_value'],
            'wastage_type'         => $input['wastage_type'],
            'wastage_input'        => $input['wastage_value'],
            'wastage_weight'       => $wastageCalc['wastage_weight'],
            'wastage_value'        => $wastageCalc['wastage_value'],
            'making_charges'       => $makingCalc['making_charges'],
            'making_charge_description' => $makingCalc['description'],
            'stones'               => $stonesCalc,
            'hallmark_charge'      => $hallmarkCharge,
            'custom_charges'       => $customCharges,
            'profit_margin'        => $profitMargin,
            'profit_amount'        => round($profitAmount, 2),
            'subtotal'             => round($subtotal, 2),
            'tax_percentage'       => $input['tax_percentage'] ?? 0,
            'tax_amount'           => round($taxAmount, 2),
            'discount_percentage'  => $input['discount_percentage'] ?? 0,
            'discount_amount'      => round($discountAmount, 2),
            'old_gold'             => $oldGoldCalc,
            'old_gold_deduction'   => round($oldGoldDeduct, 2),
            'final_total'          => round(max(0, $finalTotal), 2),
            // legacy fields for backward compat
            'gross_weight_input'   => $input['gross_weight'],
            'input_unit'           => $input['unit'],
            'stone_weight'         => $input['stone_weight'] ?? 0,
            'stone_unit'           => $input['stone_unit'] ?? 'carat',
            'stone_weight_carats'  => $stonesCalc['total_carats'],
            'stone_price_per_carat'=> 0,
            'stone_total_cost'     => $stonesCalc['total_cost'],
            'ratti_type'           => $input['ratti_type'] ?? 'sunari',
        ];
    }

    /**
     * Get all supported units
     */
    public function getSupportedUnits(): array
    {
        return [
            'g' => 'Grams (g)',
            'mg' => 'Milligrams (mg)',
            'tola' => 'Tola',
            'ratti' => 'Ratti',
            'carat' => 'Carat (ct)',
            'troy_oz' => 'Troy Ounce',
            'oz' => 'Standard Ounce',
        ];
    }

    /**
     * Get conversion table for a weight value in grams
     */
    public function getConversionTable($grams, $rattiType = 'sunari'): array
    {
        return [
            'grams' => round($grams, 4),
            'milligrams' => round($this->fromGrams($grams, 'mg'), 2),
            'tola' => round($this->fromGrams($grams, 'tola'), 4),
            'ratti' => round($this->fromGrams($grams, 'ratti', $rattiType), 4),
            'carat' => round($this->fromGrams($grams, 'carat'), 4),
            'troy_ounce' => round($this->fromGrams($grams, 'troy_oz'), 4),
            'standard_ounce' => round($this->fromGrams($grams, 'oz'), 4),
        ];
    }
}
