<?php

namespace App\Services;

use App\Models\InventoryProduct;
use App\Models\PurchaseItem;
use App\Models\PurchaseOrder;
use App\Models\SupplierPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(
        private GeneralLedgerService $glService,
        private MetalLedgerService $metalLedgerService
    ) {}

    /**
     * Create a new purchase order
     */
    public function createPurchaseOrder(array $data): PurchaseOrder
    {
        $purchaseOrder = PurchaseOrder::create($data);

        if (isset($data['items'])) {
            $this->addItemsToPurchaseOrder($purchaseOrder, $data['items']);
        }

        $this->recalculateOrderTotals($purchaseOrder);

        return $purchaseOrder;
    }

    /**
     * Add items to a purchase order
     */
    public function addItemsToPurchaseOrder(PurchaseOrder $purchaseOrder, array $items): void
    {
        foreach ($items as $item) {
            $item['purchase_order_id'] = $purchaseOrder->id;

            // Calculate Fine if not provided for Wholesale
            if (! isset($item['fine']) && isset($item['net_weight']) && isset($item['tunch'])) {
                $wastage = $item['wastage'] ?? 0;
                $item['fine'] = ($item['net_weight'] * ($item['tunch'] + $wastage)) / 100;
            }

            $purchaseItem = PurchaseItem::create($item);
            $purchaseItem->calculateLineAmount();
        }
    }

    /**
     * Issue item for Tunch Testing
     */
    public function issueForTesting(PurchaseItem $item, ?string $labName = null)
    {
        $item->update([
            'tunch_testing_status' => 'issued',
            'quality_remarks' => 'Issued for testing'.($labName ? " to $labName" : ''),
        ]);

        $item->purchaseOrder->update(['status' => 'Testing']);

        return $item;
    }

    /**
     * Approve Purchase Item
     */
    public function approveItem(PurchaseItem $item, ?float $finalTunch = null)
    {
        $updateData = ['is_approved' => true];
        if ($finalTunch !== null) {
            $updateData['tunch'] = $finalTunch;
            $wastage = $item->wastage ?? 0;
            $updateData['fine'] = ($item->net_weight * ($finalTunch + $wastage)) / 100;
        }

        $item->update($updateData);
        $item->calculateLineAmount();

        return $item;
    }

    /**
     * Update purchase order
     */
    public function updatePurchaseOrder(PurchaseOrder $purchaseOrder, array $data): PurchaseOrder
    {
        $purchaseOrder->update($data);

        if (isset($data['items'])) {
            $purchaseOrder->items()->delete();
            $this->addItemsToPurchaseOrder($purchaseOrder, $data['items']);
        }

        $this->recalculateOrderTotals($purchaseOrder);

        return $purchaseOrder;
    }

    /**
     * Recalculate purchase order totals
     */
    public function recalculateOrderTotals(PurchaseOrder $purchaseOrder): void
    {
        $purchaseOrder->sub_total = $purchaseOrder->items()->sum('line_amount');
        $purchaseOrder->gst_amount = ($purchaseOrder->sub_total * $purchaseOrder->gst_percentage) / 100;
        $purchaseOrder->discount_amount = ($purchaseOrder->sub_total * $purchaseOrder->discount_percentage) / 100;
        $purchaseOrder->total_amount = $purchaseOrder->sub_total +
                                      $purchaseOrder->gst_amount +
                                      $purchaseOrder->other_charges -
                                      $purchaseOrder->discount_amount;
        $purchaseOrder->amount_due = $purchaseOrder->total_amount - $purchaseOrder->amount_paid;

        $purchaseOrder->save();
    }

    /**
     * Record a payment against purchase order
     */
    public function recordPayment(PurchaseOrder $purchaseOrder, array $paymentData): SupplierPayment
    {
        $payment = SupplierPayment::create([
            ...$paymentData,
            'branch_id' => $purchaseOrder->branch_id,
            'supplier_id' => $purchaseOrder->supplier_id,
            'purchase_order_id' => $purchaseOrder->id,
        ]);

        // Update purchase order amounts
        $purchaseOrder->amount_paid += $payment->amount_paid;
        $purchaseOrder->amount_due = $purchaseOrder->total_amount - $purchaseOrder->amount_paid;

        // Update payment status
        if ($purchaseOrder->amount_paid >= $purchaseOrder->total_amount) {
            $purchaseOrder->payment_status = 'Paid';
        } elseif ($purchaseOrder->amount_paid > 0) {
            $purchaseOrder->payment_status = 'Partial';
        }

        $purchaseOrder->save();

        return $payment;
    }

    /**
     * Check and update overdue status
     */
    public function checkAndUpdateOverdueStatus(PurchaseOrder $purchaseOrder): void
    {
        if ($purchaseOrder->expected_delivery_date && $purchaseOrder->status !== 'Received') {
            $daysOverdue = now()->diffInDays(
                Carbon::parse($purchaseOrder->expected_delivery_date)
            );

            if ($daysOverdue > 0) {
                $purchaseOrder->is_overdue = true;
                $purchaseOrder->days_overdue = $daysOverdue;
                $purchaseOrder->save();
            }
        }
    }

    /**
     * Receive purchase order items
     */
    public function receivePurchaseOrder(
        PurchaseOrder $purchaseOrder,
        array $receivedQuantities = []
    ): void {
        $purchaseOrder->received = true;
        $purchaseOrder->received_date = now();
        $purchaseOrder->status = 'Received';

        // Update received quantities
        foreach ($purchaseOrder->items as $item) {
            if (isset($receivedQuantities[$item->id])) {
                $item->quantity_received = $receivedQuantities[$item->id];
                $item->quantity_remaining = $item->quantity - $item->quantity_received - $item->quantity_returned;
                $item->save();
            }
        }

        $purchaseOrder->save();
    }

    /**
     * Get overdue purchase orders
     */
    public function getOverduePurchaseOrders($branchId = null, $supplierId = null)
    {
        $query = PurchaseOrder::where('is_overdue', true)
            ->where('status', '!=', 'Received');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        return $query->get();
    }

    /**
     * Get pending payments
     */
    public function getPendingPayments($branchId = null, $supplierId = null)
    {
        $query = PurchaseOrder::whereIn('payment_status', ['Unpaid', 'Partial', 'Overdue']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        return $query->get();
    }

    /**
     * Generate purchase report for date range
     */
    public function generatePurchaseReport($branchId, $fromDate, $toDate)
    {
        return PurchaseOrder::where('branch_id', $branchId)
            ->whereBetween('po_date', [$fromDate, $toDate])
            ->with(['supplier', 'items'])
            ->get();
    }

    /**
     * Calculate average lead time for supplier
     */
    public function getAverageLeadTime($supplierId)
    {
        $orders = PurchaseOrder::where('supplier_id', $supplierId)
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

    /**
     * Post purchase order to general ledger and metal ledger
     */
    public function postPurchaseToGL(PurchaseOrder $purchaseOrder): void
    {
        DB::transaction(function () use ($purchaseOrder) {
            // 1. Post to General Ledger
            $this->glService->postPurchase($purchaseOrder);

            // 2. Post to Metal Ledger
            $grossWeight = $purchaseOrder->items()->sum('gross_weight');
            $fineWeight = $purchaseOrder->items()->sum('fine');

            if ($grossWeight > 0) {
                $this->metalLedgerService->recordSupplierTransaction($purchaseOrder->supplier, [
                    'weight' => $grossWeight,
                    'fine_weight' => $fineWeight,
                    'transaction_type' => 'purchase',
                    'transaction_date' => $purchaseOrder->po_date ?? now(),
                    'reference_type' => 'PurchaseOrder',
                    'reference_id' => $purchaseOrder->id,
                    'description' => "Purchase Order #{$purchaseOrder->po_number}",
                ]);
            }
        });
    }

    /**
     * Post supplier payment to general ledger
     */
    public function postPaymentToGL(SupplierPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $this->glService->postSupplierPayment($payment);
        });
    }

    /**
     * Add received stock to inventory
     */
    public function addStockToInventory(PurchaseOrder $purchaseOrder): void
    {
        DB::transaction(function () use ($purchaseOrder) {
            foreach ($purchaseOrder->items as $item) {
                if (! $item->product_id) {
                    continue;
                }

                $product = InventoryProduct::find($item->product_id);
                if (! $product) {
                    continue;
                }

                $receivedQty = $item->quantity_received ?? $item->quantity;
                $receivedWeight = $item->net_weight ?? 0;
                $receivedPieces = (int) ($item->quantity_received ?? $item->quantity);

                // Using the enhanced updateStock method from InventoryProduct model
                $product->updateStock(
                    $receivedQty,
                    'add',
                    "purchase:{$purchaseOrder->po_number}",
                    $receivedWeight,
                    $receivedPieces
                );

                // Update product weights if needed
                if ($item->gross_weight > 0) {
                    $product->increment('gross_weight', $item->gross_weight);
                }
            }
        });
    }

    /**
     * Reverse stock when purchase is cancelled
     */
    public function reverseStockFromCancelledPurchase(PurchaseOrder $purchaseOrder): void
    {
        DB::transaction(function () use ($purchaseOrder) {
            foreach ($purchaseOrder->items as $item) {
                if (! $item->product_id) {
                    continue;
                }

                $product = InventoryProduct::find($item->product_id);
                if (! $product) {
                    continue;
                }

                $receivedQty = $item->quantity_received ?? $item->quantity;
                $receivedWeight = $item->net_weight ?? 0;
                $receivedPieces = (int) ($item->quantity_received ?? $item->quantity);

                $product->updateStock(
                    $receivedQty,
                    'subtract',
                    "purchase_cancel:{$purchaseOrder->po_number}",
                    $receivedWeight,
                    $receivedPieces
                );
            }
        });
    }

    /**
     * Record purchase return GL entries
     */
    public function postPurchaseReturnToGL($return): void
    {
        DB::transaction(function () use ($return) {
            // 1. Post to General Ledger
            $this->glService->postPurchaseReturn($return);

            // 2. Post to Metal Ledger
            // Assume return has metal weights
            if (isset($return->gross_weight) && $return->gross_weight > 0) {
                $this->metalLedgerService->recordSupplierTransaction($return->supplier, [
                    'weight' => $return->gross_weight,
                    'fine_weight' => $return->fine_weight ?? 0,
                    'transaction_type' => 'return',
                    'transaction_date' => now(),
                    'reference_type' => 'PurchaseReturn',
                    'reference_id' => $return->id,
                    'description' => "Purchase Return #{$return->return_number}",
                ]);
            }
        });
    }
}
