<?php

namespace App\Services;

use App\Models\KarigarSettlement;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class KarigarSettlementService
{
    public function __construct(
        private GeneralLedgerService $glService,
        private MetalLedgerService $metalLedgerService
    ) {}

    /**
     * Create a professional settlement for a Karigar
     */
    public function createSettlement(array $data)
    {
        return DB::transaction(function () use ($data) {
            $karigar = Supplier::findOrFail($data['supplier_id']);
            $branchId = $data['branch_id'] ?? auth()->user()->branch_id ?? 1;

            // 1. Calculate totals
            $fineWeightFixed = $data['fine_weight_fixed'] ?? 0;
            $fixedRate = $data['fixed_rate'] ?? 0;
            $metalValue = $fineWeightFixed * $fixedRate;
            $laborAmount = $data['labor_amount'] ?? 0;
            $otherCharges = $data['other_charges'] ?? 0;
            $totalAmount = $metalValue + $laborAmount + $otherCharges;

            // 2. Generate settlement number if not provided
            $settlementNumber = $data['settlement_number'] ?? $this->generateSettlementNumber();

            // 3. Create Settlement Record
            $settlement = KarigarSettlement::create([
                'branch_id' => $branchId,
                'supplier_id' => $karigar->id,
                'settlement_number' => $settlementNumber,
                'date' => $data['date'] ?? now(),
                'metal_type' => $data['metal_type'] ?? 'Gold',
                'fine_weight_fixed' => $fineWeightFixed,
                'fixed_rate' => $fixedRate,
                'metal_value' => $metalValue,
                'labor_amount' => $laborAmount,
                'other_charges' => $otherCharges,
                'total_amount' => $totalAmount,
                'payment_status' => 'Pending',
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id() ?? 1,
            ]);

            // 4. Update Karigar Ledger
            $this->updateLedger($karigar, $settlement);

            return $settlement;
        });
    }

    /**
     * Update Karigar Metal and Cash Ledger
     */
    protected function updateLedger(Supplier $karigar, KarigarSettlement $settlement)
    {
        // 1. Post to General Ledger
        $this->glService->postKarigarSettlement($settlement);

        // 2. Post to Metal Ledger
        if ($settlement->fine_weight_fixed > 0) {
            $this->metalLedgerService->recordSupplierTransaction($karigar, [
                'weight' => $settlement->fine_weight_fixed, // Usually settlements are recorded as pure
                'fine_weight' => $settlement->fine_weight_fixed,
                'transaction_type' => 'bhav_cut', // Reducing metal debt
                'transaction_date' => $settlement->date,
                'reference_type' => 'KarigarSettlement',
                'reference_id' => $settlement->id,
                'description' => "Settlement #{$settlement->settlement_number}: Metal Fixed {$settlement->fine_weight_fixed}g @ {$settlement->fixed_rate}",
            ]);
        }
    }

    /**
     * Generate a unique settlement number
     */
    protected function generateSettlementNumber(): string
    {
        $prefix = 'SET-';
        $lastSettlement = KarigarSettlement::latest()->first();
        $nextId = $lastSettlement ? $lastSettlement->id + 1 : 1;

        return $prefix.str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }
}
