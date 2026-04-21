<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerMetalLedger;
use App\Models\Supplier;
use App\Models\SupplierLedgerEntry;
use Exception;
use Illuminate\Support\Facades\DB;

class MetalLedgerService
{
    public function __construct(
        private GeneralLedgerService $glService
    ) {}

    /**
     * Records a metal transaction for a customer.
     *
     * @param  array  $data  [metal_type, weight, fine_weight, transaction_type, reference_type, reference_id, description, purity_percentage]
     *
     * @throws Exception
     */
    public function recordCustomerTransaction(Customer $customer, array $data): CustomerMetalLedger
    {
        return DB::transaction(function () use ($customer, $data) {
            $metalType = $data['metal_type'] ?? 'gold';
            $weight = (float) ($data['weight'] ?? 0);
            $fineWeight = (float) ($data['fine_weight'] ?? 0);
            $type = $data['transaction_type']; // sale, return, payment, bhav_cut, adjustment

            $weightIn = 0;
            $weightOut = 0;
            $fineIn = 0;
            $fineOut = 0;

            // In jewelry accounting:
            // weight_out = what we gave to customer (increases customer's metal debt)
            // weight_in = what customer gave us or settlement (decreases customer's metal debt)

            if (in_array($type, ['sale', 'adjustment_out'])) {
                $weightOut = $weight;
                $fineOut = $fineWeight;
            } else {
                // payment, return, bhav_cut, adjustment_in
                $weightIn = $weight;
                $fineIn = $fineWeight;
            }

            // Update Customer Balances
            if ($metalType === 'gold') {
                $customer->current_gold_balance += ($weightOut - $weightIn);
                $customer->current_fine_gold_balance += ($fineOut - $fineIn);
            } else {
                $customer->current_silver_balance += ($weightOut - $weightIn);
                $customer->current_fine_silver_balance += ($fineOut - $fineIn);
            }
            $customer->save();

            return CustomerMetalLedger::create([
                'customer_id' => $customer->id,
                'branch_id' => $customer->branch_id ?? 1,
                'transaction_date' => $data['transaction_date'] ?? now(),
                'transaction_type' => $type,
                'metal_type' => $metalType,
                'purity_percentage' => $data['purity_percentage'] ?? null,
                'weight_in' => $weightIn,
                'fine_weight_in' => $fineIn,
                'weight_out' => $weightOut,
                'fine_weight_out' => $fineOut,
                'running_weight_balance' => ($metalType === 'gold') ? $customer->current_gold_balance : $customer->current_silver_balance,
                'running_fine_balance' => ($metalType === 'gold') ? $customer->current_fine_gold_balance : $customer->current_fine_silver_balance,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'description' => $data['description'] ?? null,
                'created_by' => auth()->id() ?? 1,
            ]);
        });
    }

    /**
     * Adjusts metal balance for a customer (Manual Adjustment).
     */
    public function adjustCustomerBalance(Customer $customer, float $weight, float $fineWeight, string $metalType, string $description): CustomerMetalLedger
    {
        return $this->recordCustomerTransaction($customer, [
            'metal_type' => $metalType,
            'weight' => abs($weight),
            'fine_weight' => abs($fineWeight),
            'transaction_type' => 'adjustment',
            'transaction_date' => now(),
            'description' => $description,
        ]);
    }

    /**
     * Settles metal balance with cash (Bhav Cut / Rate Cut) without a specific sale.
     */
    public function settleMetalWithCash(Customer $customer, float $weight, float $rate, string $metalType, string $description): CustomerMetalLedger
    {
        return DB::transaction(function () use ($customer, $weight, $rate, $metalType, $description) {
            $fineWeight = $weight; // Usually settlement is on fine weight basis or as agreed
            $amount = $weight * $rate;

            $ledgerEntry = $this->recordCustomerTransaction($customer, [
                'metal_type' => $metalType,
                'weight' => $weight,
                'fine_weight' => $fineWeight,
                'transaction_type' => 'bhav_cut',
                'transaction_date' => now(),
                'description' => $description." (Rate: $rate)",
            ]);

            // Post to General Ledger for standalone settlement
            $this->glService->postManualBhavCut($customer, $amount, $description);

            return $ledgerEntry;
        });
    }

    public function recordSupplierTransaction(Supplier $supplier, array $data): SupplierLedgerEntry
    {
        return DB::transaction(function () use ($supplier, $data) {
            $ledger = $supplier->ledgers()->firstOrCreate([
                'branch_id' => $supplier->branch_id ?? 1,
            ]);

            $weight = (float) ($data['weight'] ?? 0);
            $fineWeight = (float) ($data['fine_weight'] ?? 0);
            $type = $data['transaction_type']; // purchase, return, payment, etc.

            // Suppliers:
            // Purchase (Metal IN from supplier) -> increases our debt (positive balance in some systems, negative in others)
            // Payment (Metal OUT to supplier) -> decreases our debt

            $weightDelta = 0;
            $fineDelta = 0;

            if (in_array($type, ['purchase', 'adjustment_in'])) {
                $weightDelta = $weight;
                $fineDelta = $fineWeight;
            } else {
                $weightDelta = -$weight;
                $fineDelta = -$fineWeight;
            }

            $ledger->current_gold_balance += $weightDelta;
            $ledger->current_fine_gold_balance += $fineDelta;
            $ledger->save();

            return SupplierLedgerEntry::create([
                'supplier_ledger_id' => $ledger->id,
                'date' => $data['transaction_date'] ?? now(),
                'type' => $type,
                'gross_weight' => $weight,
                'fine_weight' => $fineWeight,
                'running_gross_weight' => $ledger->current_gold_balance,
                'running_fine_weight' => $ledger->current_fine_gold_balance,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'description' => $data['description'] ?? null,
            ]);
        });
    }
}
