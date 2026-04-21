<?php

namespace App\Services;

use App\Models\Asset;
use Carbon\Carbon;

class AssetService
{
    public function calculateDepreciation(Asset $asset)
    {
        $now = now();
        $years = $now->diffInYears($asset->purchase_date);

        if ($years <= 0) {
            return $asset->purchase_cost;
        }

        if ($asset->depreciation_method === 'reducing_balance') {
            $value = $asset->purchase_cost;
            for ($i = 0; $i < $years; $i++) {
                $value -= ($value * ($asset->depreciation_rate / 100));
            }

            return max(0, $value);
        }

        // Straight line
        $reduction = ($asset->purchase_cost * ($asset->depreciation_rate / 100)) * $years;

        return max(0, $asset->purchase_cost - $reduction);
    }

    public function predictNextMaintenance(Asset $asset)
    {
        // Advanced logic: if asset is old, maintenance should be more frequent
        $ageYears = now()->diffInYears($asset->purchase_date);
        $baseInterval = $asset->maintenance_interval_days;

        $adjustedInterval = $baseInterval;
        if ($ageYears > 5) {
            $adjustedInterval = $baseInterval * 0.75; // 25% more frequent
        }

        return Carbon::parse($asset->last_maintenance_date ?? $asset->purchase_date)->addDays($adjustedInterval);
    }
}
