<?php

namespace App\Services;

use App\Models\Supplier;
use App\Models\SupplierCreditLimit;
use Illuminate\Database\Eloquent\Collection;

class SupplierService
{
    /**
     * Create a new supplier
     */
    public function createSupplier(array $data): Supplier
    {
        $supplier = Supplier::create($data);

        // Create default credit limit if provided
        if (isset($data['credit_limit'])) {
            $this->createCreditLimit($supplier, [
                'branch_id' => $data['branch_id'],
                'approved_credit_limit' => $data['credit_limit'],
                'credit_days' => $data['payment_days'] ?? 30,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);
        }

        return $supplier;
    }

    /**
     * Create credit limit for supplier
     */
    public function createCreditLimit(Supplier $supplier, array $data): SupplierCreditLimit
    {
        return SupplierCreditLimit::create([
            ...$data,
            'supplier_id' => $supplier->id,
            'available_credit' => $data['approved_credit_limit'],
            'effective_from' => now(),
            'created_by' => $data['created_by'] ?? auth()->id(),
        ]);
    }

    /**
     * Update supplier information
     */
    public function updateSupplier(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);

        return $supplier;
    }

    /**
     * Get supplier with complete details
     */
    public function getSupplierWithDetails($supplierId)
    {
        return Supplier::with([
            'purchaseOrders',
            'payments',
            'priceHistories',
            'creditLimits',
            'analytics',
        ])->findOrFail($supplierId);
    }

    /**
     * Get supplier rating
     */
    public function calculateSupplierRating(Supplier $supplier): float
    {
        $qualityWeight = 0.4;
        $deliveryWeight = 0.4;
        $paymentWeight = 0.2;

        $qualityScore = $supplier->quality_score / 100;
        $deliveryScore = $supplier->delivery_score / 100;
        $paymentScore = $this->getPaymentScore($supplier) / 100;

        return round(
            ($qualityScore * $qualityWeight) +
            ($deliveryScore * $deliveryWeight) +
            ($paymentScore * $paymentWeight),
            2
        );
    }

    /**
     * Get payment score for supplier
     */
    public function getPaymentScore(Supplier $supplier): int
    {
        $creditLimit = $supplier->creditLimits()->active()->first();

        if (! $creditLimit) {
            return 50;
        }

        if ($creditLimit->has_overdue) {
            return 40;
        }

        $utilizationPercentage = $creditLimit->getUtilizationPercentage();

        if ($utilizationPercentage <= 50) {
            return 100;
        } elseif ($utilizationPercentage <= 75) {
            return 85;
        } elseif ($utilizationPercentage <= 90) {
            return 70;
        } else {
            return 50;
        }
    }

    /**
     * Get outstanding balance for supplier (including opening balance)
     */
    public function getOutstandingBalance(Supplier $supplier): float
    {
        $purchaseBalance = $supplier->purchaseOrders()
            ->where('status', '!=', 'Cancelled')
            ->whereNotIn('payment_status', ['Paid'])
            ->sum('amount_due');

        $returnAmount = $supplier->purchaseReturns()
            ->where('status', 'Processed')
            ->sum('refund_amount');

        $openingBalance = $supplier->opening_balance ?? 0;

        return $openingBalance + $purchaseBalance - $returnAmount;
    }

    /**
     * Get detailed balance breakdown
     */
    public function getBalanceBreakdown(Supplier $supplier): array
    {
        $openingBalance = $supplier->opening_balance ?? 0;
        $totalPurchases = $supplier->purchaseOrders()
            ->where('status', '!=', 'Cancelled')
            ->sum('total_amount');
        $totalPaid = $supplier->payments()->sum('amount_paid');
        $totalReturns = $supplier->purchaseReturns()
            ->where('status', 'Processed')
            ->sum('refund_amount');

        $outstanding = $openingBalance + $totalPurchases - $totalPaid - $totalReturns;

        return [
            'opening_balance' => $openingBalance,
            'total_purchases' => $totalPurchases,
            'total_paid' => $totalPaid,
            'total_returns' => $totalReturns,
            'outstanding' => $outstanding,
        ];
    }

    /**
     * Get total purchases amount
     */
    public function getTotalPurchases(Supplier $supplier): float
    {
        return $supplier->purchaseOrders()
            ->where('status', '!=', 'Cancelled')
            ->sum('total_amount');
    }

    /**
     * Check if supplier can create new purchase order
     */
    public function canCreatePurchaseOrder(Supplier $supplier, $amount = 0): bool
    {
        if ($supplier->status === 'Blocked' || $supplier->status === 'Inactive') {
            return false;
        }

        $creditLimit = $supplier->creditLimits()->active()->first();

        if (! $creditLimit) {
            return true;
        }

        if ($creditLimit->has_overdue) {
            return false;
        }

        return $creditLimit->available_credit >= $amount;
    }

    /**
     * Get all active suppliers
     */
    public function getActiveSuppliersForBranch($branchId): Collection
    {
        return Supplier::where('branch_id', $branchId)
            ->where('status', 'Active')
            ->get();
    }

    /**
     * Get suppliers with overdue payments
     */
    public function getSuppliersWithOverduePayments($branchId = null)
    {
        $query = Supplier::whereHas('creditLimits', function ($q) {
            $q->where('has_overdue', true);
        });

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->get();
    }

    /**
     * Block supplier (due to overdue or other reasons)
     */
    public function blockSupplier(Supplier $supplier, ?string $reason = null): void
    {
        $supplier->status = 'Blocked';
        $supplier->notes = ($supplier->notes ?? '')."\n[BLOCKED] ".($reason ?? 'Overdue payments');
        $supplier->save();

        // Also block all credit limits
        $supplier->creditLimits()->update(['status' => 'Blocked']);
    }

    /**
     * Unblock supplier
     */
    public function unblockSupplier(Supplier $supplier): void
    {
        $supplier->status = 'Active';
        $supplier->save();

        $supplier->creditLimits()->update(['status' => 'Active']);
    }

    /**
     * Search suppliers
     */
    public function searchSuppliers($branchId, $searchTerm)
    {
        return Supplier::where('branch_id', $branchId)
            ->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%")
                    ->orWhere('phone_primary', 'like', "%{$searchTerm}%")
                    ->orWhere('gstin', 'like', "%{$searchTerm}%");
            })
            ->get();
    }

    /**
     * Get supplier comparison data
     */
    public function compareSuppliers(array $supplierIds)
    {
        return Supplier::whereIn('id', $supplierIds)
            ->with(['purchaseOrders', 'payments', 'analytics'])
            ->get();
    }

    /**
     * Suspend supplier temporarily
     */
    public function suspendSupplier(Supplier $supplier, $days = 30): void
    {
        $supplier->status = 'OnHold';
        $supplier->save();

        // Suspend all credit limits
        $supplier->creditLimits()->update([
            'status' => 'Suspended',
            'effective_to' => now()->addDays($days),
        ]);
    }

    /**
     * Get average lead time for supplier
     */
    public function getAverageLeadTime(Supplier $supplier)
    {
        $orders = $supplier->purchaseOrders()
            ->whereNotNull('received_date')
            ->whereNotNull('po_date')
            ->get();

        if ($orders->isEmpty()) {
            return 0;
        }

        $totalDays = $orders->sum(function ($order) {
            return $order->po_date->diffInDays($order->received_date);
        });

        return round($totalDays / $orders->count(), 2);
    }
}
