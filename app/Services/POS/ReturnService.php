<?php

namespace App\Services\POS;

use App\Models\PosPayment;
use App\Models\PosReturnRepair;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use Illuminate\Support\Facades\DB;

class ReturnService
{
    public function createReturn(PosSale $sale, array $data): PosReturnRepair
    {
        return DB::transaction(function () use ($sale, $data) {
            $return = PosReturnRepair::create([
                'pos_sale_id' => $sale->id,
                'pos_sale_item_id' => $data['item_id'] ?? null,
                'type' => $data['type'] ?? 'return',
                'return_date' => now(),
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
                'refund_method' => $data['refund_method'] ?? 'original',
                'status' => 'initiated',
            ]);

            $refundAmount = $this->calculateRefundAmount($return, $data);
            $return->update(['refund_amount' => $refundAmount]);

            $sale->addAuditLog('return_initiated', "Return initiated: {$data['reason']} - Amount: {$refundAmount}", auth()->id());

            return $return;
        });
    }

    public function approveReturn(PosReturnRepair $return, ?string $approvalNotes = null): void
    {
        DB::transaction(function () use ($return, $approvalNotes) {
            $return->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
            ]);

            if ($approvalNotes) {
                $return->update(['notes' => $return->notes."\nApproval Notes: {$approvalNotes}"]);
            }

            $return->sale->addAuditLog('return_approved', "Return approved. Refund amount: {$return->refund_amount}", auth()->id());
        });
    }

    public function rejectReturn(PosReturnRepair $return, string $reason): void
    {
        DB::transaction(function () use ($return, $reason) {
            $return->update([
                'status' => 'rejected',
                'notes' => $return->notes."\nRejection Reason: {$reason}",
            ]);

            $return->sale->addAuditLog('return_rejected', "Return rejected. Reason: {$reason}", auth()->id());
        });
    }

    public function processReturn(PosReturnRepair $return): void
    {
        DB::transaction(function () use ($return) {
            if ($return->status !== 'approved') {
                throw new \Exception('Return must be approved before processing');
            }

            $sale = $return->sale;

            if ($return->item) {
                $this->restockItem($return->item);
            }

            $return->update([
                'status' => 'processed',
                'processed_by' => auth()->id(),
            ]);

            if ($return->refund_amount > 0) {
                $this->issueRefund($sale, $return);
            }

            $sale->recalculateTotals();
            $sale->addAuditLog('return_processed', "Return processed. Refund method: {$return->refund_method}", auth()->id());
        });
    }

    public function processExchange(PosReturnRepair $return, array $newItemData): void
    {
        DB::transaction(function () use ($return, $newItemData) {
            if ($return->status !== 'approved') {
                throw new \Exception('Return must be approved before processing');
            }

            if ($return->type !== 'exchange') {
                throw new \Exception('This return is not an exchange type');
            }

            if ($return->item) {
                $this->restockItem($return->item);
            }

            $sale = $return->sale;

            $return->update([
                'status' => 'processed',
                'processed_by' => auth()->id(),
            ]);

            $diffAmount = $return->item->line_total - $newItemData['line_total'];

            if ($diffAmount > 0) {
                $return->update(['refund_amount' => $diffAmount]);
                $this->issueRefund($sale, $return);
            } elseif ($diffAmount < 0) {
                $paidAmount = abs($diffAmount);
                PosPayment::create([
                    'pos_sale_id' => $sale->id,
                    'pos_customer_id' => $sale->pos_customer_id,
                    'payment_method' => 'cash',
                    'amount' => $paidAmount,
                    'currency' => $sale->currency,
                    'reference' => 'EXCHANGE-'.$return->id,
                    'status' => 'completed',
                    'recorded_by' => auth()->id(),
                ]);
            }

            $sale->recalculateTotals();
            $sale->addAuditLog('exchange_processed', 'Exchange processed. Item exchanged.', auth()->id());
        });
    }

    public function issueRefund(PosSale $sale, PosReturnRepair $return): PosPayment
    {
        return DB::transaction(function () use ($sale, $return) {
            $payment = PosPayment::create([
                'pos_sale_id' => $sale->id,
                'pos_customer_id' => $sale->pos_customer_id,
                'payment_method' => $return->refund_method,
                'amount' => -abs($return->refund_amount),
                'currency' => $sale->currency,
                'reference' => 'REFUND-'.$return->id,
                'notes' => 'Refund for return: '.($return->reason ?? ''),
                'status' => 'completed',
                'recorded_by' => auth()->id(),
            ]);

            $sale->recalculateTotals();

            return $payment;
        });
    }

    public function restockItem(PosSaleItem $item): void
    {
        $product = $item->inventoryProduct;
        if ($product) {
            $product->updateStock($item->quantity, 'add', 'return:'.$item->sale->invoice_no);
        }
    }

    public function getReturnHistory(PosSale $sale)
    {
        return $sale->returns()
            ->with(['item', 'approvedByUser', 'processedByUser'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function getPendingReturns()
    {
        return PosReturnRepair::where('status', 'initiated')
            ->with(['sale', 'item', 'sale.customer'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function getApprovedReturns()
    {
        return PosReturnRepair::where('status', 'approved')
            ->with(['sale', 'item', 'approvedByUser'])
            ->orderByDesc('created_at')
            ->get();
    }

    private function calculateRefundAmount(PosReturnRepair $return, array $data): float
    {
        if (isset($data['custom_amount'])) {
            return $data['custom_amount'];
        }

        if ($return->item) {
            $percentage = $data['percentage'] ?? 100;

            return ($return->item->line_total * $percentage) / 100;
        }

        return 0;
    }
}
