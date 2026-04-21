<?php

namespace App\Services\Accounting;

use App\Models\PosSale;
use App\Models\TaxConfiguration;
use App\Models\TaxEntry;
use Illuminate\Support\Facades\DB;

class TaxService
{
    /**
     * Get applicable tax rates for a date and category
     * Returns multiple taxes if applicable (e.g., GST + Cess)
     */
    public function getApplicableTaxes(string $date, ?int $branchId = null, ?int $categoryId = null): \Illuminate\Support\Collection
    {
        $query = TaxConfiguration::where('is_active', true)
            ->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date);
            });

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($categoryId) {
            $query->where(function ($q) use ($categoryId) {
                $q->where('product_category', $categoryId)
                    ->orWhereNull('product_category');
            });
        }

        return $query->get();
    }

    /**
     * Record tax entries for a sale
     */
    public function recordTaxEntry(PosSale $sale): void
    {
        if ($sale->tax_amount <= 0) {
            return;
        }

        $date = $sale->sale_time->toDateString();

        // Get all applicable taxes for this sale's branch
        $configs = $this->getApplicableTaxes($date, $sale->branch_id);

        if ($configs->isEmpty()) {
            // Fallback to a default if no specific config found (emergency safeguard)
            $defaultRate = config('accounting.default_tax_rate', 0.17);
            TaxEntry::create([
                'tax_config_id' => null,
                'reference_type' => 'sale',
                'reference_id' => $sale->id,
                'taxable_amount' => $sale->subtotal,
                'tax_amount' => $sale->tax_amount,
                'date' => $date,
                'transaction_type' => 'sale',
                'status' => 'recorded',
            ]);

            return;
        }

        // Split tax if multiple configs (e.g., CGST/SGST)
        // Simplified: assuming tax_amount is already calculated, we split it proportionally to rates if multiple
        $totalRate = $configs->sum('rate');

        foreach ($configs as $config) {
            $proportion = $totalRate > 0 ? ($config->rate / $totalRate) : (1 / $configs->count());
            $allocatedTax = $sale->tax_amount * $proportion;

            TaxEntry::create([
                'tax_config_id' => $config->id,
                'reference_type' => 'sale',
                'reference_id' => $sale->id,
                'taxable_amount' => $sale->subtotal,
                'tax_amount' => $allocatedTax,
                'date' => $date,
                'transaction_type' => 'sale',
                'status' => 'recorded',
            ]);
        }
    }

    /**
     * Get tax summary for a period
     */
    public function getTaxSummary(string $startDate, string $endDate): array
    {
        return TaxEntry::whereBetween('tax_date', [$startDate, $endDate])
            ->select(DB::raw('SUM(taxable_amount) as total_taxable, SUM(tax_amount) as total_tax'))
            ->first()
            ->toArray();
    }
}
