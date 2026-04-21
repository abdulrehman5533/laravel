<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Supplier;

class SupplierPerformanceService
{
    /**
     * Calculate supplier performance score
     */
    public function calculatePerformanceScore(Supplier $supplier): float
    {
        $qualityScore = $supplier->quality_score ?? 80;
        $deliveryScore = $supplier->delivery_score ?? 80;
        $paymentScore = $this->calculatePaymentScore($supplier);
        $reliabilityScore = $this->calculateReliabilityScore($supplier);

        return round(
            ($qualityScore * 0.3) +
            ($deliveryScore * 0.3) +
            ($paymentScore * 0.2) +
            ($reliabilityScore * 0.2),
            2
        );
    }

    /**
     * Calculate payment compliance score
     */
    public function calculatePaymentScore(Supplier $supplier): float
    {
        $totalPayments = $supplier->payments()->count();
        if ($totalPayments === 0) {
            return 100;
        }

        $overduePayments = $supplier->purchaseOrders()
            ->whereIn('payment_status', ['Overdue'])
            ->count();

        return max(0, 100 - ($overduePayments / $totalPayments * 100));
    }

    /**
     * Calculate reliability score based on delivery history
     */
    public function calculateReliabilityScore(Supplier $supplier): float
    {
        $totalOrders = $supplier->purchaseOrders()
            ->where('status', 'Received')
            ->count();

        if ($totalOrders === 0) {
            return 100;
        }

        $overdueDeliveries = $supplier->purchaseOrders()
            ->where('status', 'Received')
            ->whereRaw('DATE(actual_delivery_date) > DATE(expected_delivery_date)')
            ->count();

        return max(0, 100 - ($overdueDeliveries / $totalOrders * 100));
    }

    /**
     * Get average delivery time in days
     */
    public function getAverageDeliveryTime(Supplier $supplier): float
    {
        $orders = $supplier->purchaseOrders()
            ->where('status', 'Received')
            ->whereNotNull('po_date')
            ->whereNotNull('actual_delivery_date')
            ->get();

        if ($orders->isEmpty()) {
            return 0;
        }

        $totalDays = $orders->sum(function ($order) {
            return $order->po_date->diffInDays($order->actual_delivery_date);
        });

        return round($totalDays / $orders->count(), 2);
    }

    /**
     * Get on-time delivery percentage
     */
    public function getOnTimeDeliveryPercentage(Supplier $supplier): float
    {
        $totalOrders = $supplier->purchaseOrders()
            ->where('status', 'Received')
            ->count();

        if ($totalOrders === 0) {
            return 100;
        }

        $onTimeOrders = $supplier->purchaseOrders()
            ->where('status', 'Received')
            ->whereRaw('DATE(actual_delivery_date) <= DATE(expected_delivery_date)')
            ->count();

        return round(($onTimeOrders / $totalOrders) * 100, 2);
    }

    /**
     * Get pending payment orders
     */
    public function getPendingPaymentOrders(Supplier $supplier)
    {
        return $supplier->purchaseOrders()
            ->whereIn('payment_status', ['Unpaid', 'Partial', 'Overdue'])
            ->orderBy('expected_delivery_date')
            ->get();
    }

    /**
     * Check if supplier has overdue payments
     */
    public function hasOverduePayments(Supplier $supplier): bool
    {
        return $supplier->purchaseOrders()
            ->where('payment_status', 'Overdue')
            ->exists();
    }

    /**
     * Get total overdue amount
     */
    public function getTotalOverdueAmount(Supplier $supplier): float
    {
        return $supplier->purchaseOrders()
            ->where('payment_status', 'Overdue')
            ->sum('amount_due');
    }

    /**
     * Get days until next payment due
     */
    public function getDaysUntilDue(PurchaseOrder $order): int
    {
        if ($order->payment_status === 'Paid') {
            return 0;
        }

        $dueDate = $order->po_date->addDays($order->supplier->payment_days ?? 30);

        return max(0, now()->diffInDays($dueDate, false));
    }

    /**
     * Generate supplier scorecard
     */
    public function generateScorecard(Supplier $supplier): array
    {
        return [
            'overall_score' => $this->calculatePerformanceScore($supplier),
            'quality_score' => $supplier->quality_score ?? 0,
            'delivery_score' => $supplier->delivery_score ?? 0,
            'payment_score' => $this->calculatePaymentScore($supplier),
            'reliability_score' => $this->calculateReliabilityScore($supplier),
            'avg_delivery_time' => $this->getAverageDeliveryTime($supplier),
            'on_time_delivery_pct' => $this->getOnTimeDeliveryPercentage($supplier),
            'pending_orders' => $supplier->purchaseOrders()
                ->whereIn('payment_status', ['Unpaid', 'Partial', 'Overdue'])
                ->count(),
            'has_overdue_payments' => $this->hasOverduePayments($supplier),
            'total_overdue_amount' => $this->getTotalOverdueAmount($supplier),
            'total_orders' => $supplier->purchaseOrders()->count(),
            'total_spent' => $supplier->purchaseOrders()
                ->where('status', '!=', 'Cancelled')
                ->sum('total_amount'),
        ];
    }

    /**
     * Rank suppliers by performance
     */
    public function rankSuppliersForBranch($branchId): array
    {
        $suppliers = Supplier::where('branch_id', $branchId)
            ->where('status', 'Active')
            ->get();

        $ranked = $suppliers->map(function ($supplier) {
            return [
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name,
                'score' => $this->calculatePerformanceScore($supplier),
            ];
        })->sortByDesc('score')->values();

        return $ranked->toArray();
    }
}
