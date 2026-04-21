<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\SupplierCreditLimit;
use Carbon\Carbon;

class CreditManagementService
{
    /**
     * Initialize credit limit for supplier
     */
    public function initializeCreditLimit(
        $supplierId,
        $branchId,
        $approvedLimit,
        $creditDays = 30,
        $creditType = 'Fixed'
    ): SupplierCreditLimit {
        return SupplierCreditLimit::create([
            'supplier_id' => $supplierId,
            'branch_id' => $branchId,
            'approved_credit_limit' => $approvedLimit,
            'available_credit' => $approvedLimit,
            'credit_days' => $creditDays,
            'credit_type' => $creditType,
            'status' => 'Active',
            'effective_from' => now(),
        ]);
    }

    /**
     * Allocate credit (utilize credit on purchase)
     */
    public function allocateCredit($supplierId, $branchId, $amount): bool
    {
        $creditLimit = SupplierCreditLimit::where('supplier_id', $supplierId)
            ->where('branch_id', $branchId)
            ->where('status', 'Active')
            ->first();

        if (! $creditLimit) {
            return false;
        }

        if ($creditLimit->available_credit < $amount) {
            return false; // Insufficient credit
        }

        $creditLimit->updateCreditUsage($amount);

        return true;
    }

    /**
     * Release credit (payment made)
     */
    public function releaseCredit($supplierId, $branchId, $amount): void
    {
        $creditLimit = SupplierCreditLimit::where('supplier_id', $supplierId)
            ->where('branch_id', $branchId)
            ->first();

        if ($creditLimit) {
            $creditLimit->reduceCreditUsage($amount);
        }
    }

    /**
     * Check credit availability
     */
    public function checkCreditAvailability($supplierId, $branchId, $amount): bool
    {
        $creditLimit = SupplierCreditLimit::where('supplier_id', $supplierId)
            ->where('branch_id', $branchId)
            ->where('status', 'Active')
            ->first();

        if (! $creditLimit) {
            return false;
        }

        return $creditLimit->canUtilizeCredit($amount);
    }

    /**
     * Get available credit for supplier
     */
    public function getAvailableCredit($supplierId, $branchId): float
    {
        $creditLimit = SupplierCreditLimit::where('supplier_id', $supplierId)
            ->where('branch_id', $branchId)
            ->where('status', 'Active')
            ->first();

        return $creditLimit ? $creditLimit->available_credit : 0;
    }

    /**
     * Update overdue status
     */
    public function updateOverdueStatus($supplierId, $branchId): void
    {
        $creditLimit = SupplierCreditLimit::where('supplier_id', $supplierId)
            ->where('branch_id', $branchId)
            ->first();

        if (! $creditLimit) {
            return;
        }

        // Calculate overdue amount
        $overdueAmount = $this->calculateOverdueAmount($supplierId);
        $daysOverdue = $this->calculateDaysOverdue($supplierId);

        $creditLimit->updateOverdueStatus($overdueAmount, $daysOverdue);
    }

    /**
     * Calculate total overdue amount for supplier
     */
    public function calculateOverdueAmount($supplierId): float
    {
        $overdueOrders = PurchaseOrder::where('supplier_id', $supplierId)
            ->where('is_overdue', true)
            ->whereIn('payment_status', ['Unpaid', 'Partial', 'Overdue'])
            ->get();

        return $overdueOrders->sum('amount_due');
    }

    /**
     * Calculate days overdue for supplier
     */
    public function calculateDaysOverdue($supplierId): int
    {
        $oldestOverdueOrder = PurchaseOrder::where('supplier_id', $supplierId)
            ->where('is_overdue', true)
            ->whereIn('payment_status', ['Unpaid', 'Partial', 'Overdue'])
            ->orderBy('expected_delivery_date')
            ->first();

        if (! $oldestOverdueOrder) {
            return 0;
        }

        return now()->diffInDays(
            Carbon::parse($oldestOverdueOrder->expected_delivery_date)
        );
    }

    /**
     * Suspend credit due to overdue
     */
    public function suspendCreditForOverdue($supplierId, $branchId): void
    {
        $creditLimit = SupplierCreditLimit::where('supplier_id', $supplierId)
            ->where('branch_id', $branchId)
            ->first();

        if ($creditLimit) {
            $creditLimit->status = 'Suspended';
            $creditLimit->save();
        }
    }

    /**
     * Restore credit after payment
     */
    public function restoreCreditAfterPayment($supplierId, $branchId): void
    {
        $creditLimit = SupplierCreditLimit::where('supplier_id', $supplierId)
            ->where('branch_id', $branchId)
            ->first();

        if ($creditLimit && $creditLimit->overdue_amount <= 0) {
            $creditLimit->status = 'Active';
            $creditLimit->save();
        }
    }

    /**
     * Get credit utilization percentage
     */
    public function getCreditUtilizationPercentage($supplierId, $branchId): float
    {
        $creditLimit = SupplierCreditLimit::where('supplier_id', $supplierId)
            ->where('branch_id', $branchId)
            ->first();

        return $creditLimit ? $creditLimit->getUtilizationPercentage() : 0;
    }

    /**
     * Get credit limit expiry warning
     */
    public function getCreditExpiryWarning($supplierId, $branchId, $daysWarning = 7)
    {
        $creditLimit = SupplierCreditLimit::where('supplier_id', $supplierId)
            ->where('branch_id', $branchId)
            ->whereNotNull('effective_to')
            ->where('effective_to', '<=', now()->addDays($daysWarning))
            ->first();

        return $creditLimit;
    }

    /**
     * Adjust credit limit
     */
    public function adjustCreditLimit(
        $supplierId,
        $branchId,
        $newLimit,
        $userId
    ): SupplierCreditLimit {
        $creditLimit = SupplierCreditLimit::where('supplier_id', $supplierId)
            ->where('branch_id', $branchId)
            ->first();

        if (! $creditLimit) {
            return $this->initializeCreditLimit(
                $supplierId,
                $branchId,
                $newLimit
            );
        }

        $creditLimit->approved_credit_limit = $newLimit;
        $creditLimit->available_credit = $newLimit - $creditLimit->current_credit_used;
        $creditLimit->updated_by = $userId;
        $creditLimit->save();

        return $creditLimit;
    }

    /**
     * Auto-recalculate overdue based on frequency
     */
    public function autoRecalculateOverdue($supplierId, $branchId): void
    {
        $creditLimit = SupplierCreditLimit::where('supplier_id', $supplierId)
            ->where('branch_id', $branchId)
            ->first();

        if (! $creditLimit) {
            return;
        }

        $lastCalculation = $creditLimit->last_calculated_at;
        $frequency = $creditLimit->calculation_frequency_days;

        if (! $lastCalculation ||
            now()->diffInDays(Carbon::parse($lastCalculation)) >= $frequency) {

            $this->updateOverdueStatus($supplierId, $branchId);

            $creditLimit->last_calculated_at = now();
            $creditLimit->save();
        }
    }

    /**
     * Get credit summary for supplier
     */
    public function getCreditSummary($supplierId, $branchId): array
    {
        $creditLimit = SupplierCreditLimit::where('supplier_id', $supplierId)
            ->where('branch_id', $branchId)
            ->first();

        if (! $creditLimit) {
            return [];
        }

        return [
            'approved_limit' => $creditLimit->approved_credit_limit,
            'current_used' => $creditLimit->current_credit_used,
            'available' => $creditLimit->available_credit,
            'utilization_percentage' => $creditLimit->getUtilizationPercentage(),
            'overdue_amount' => $creditLimit->overdue_amount,
            'days_overdue' => $creditLimit->days_overdue,
            'status' => $creditLimit->status,
            'is_exceeding' => $creditLimit->isExceedingLimit(),
        ];
    }
}
