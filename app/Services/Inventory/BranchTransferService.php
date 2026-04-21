<?php

namespace App\Services\Inventory;

use App\Models\InventoryTransfer;
use App\Models\InventoryTransferItem;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BranchTransferService
{
    /**
     * Create a new transfer request
     */
    public function requestTransfer($fromBranchId, $toBranchId, array $items, $notes = null)
    {
        return DB::transaction(function () use ($fromBranchId, $toBranchId, $items, $notes) {
            $transfer = InventoryTransfer::create([
                'transfer_number' => $this->generateTransferNumber(),
                'from_branch_id' => $fromBranchId,
                'to_branch_id' => $toBranchId,
                'status' => 'requested',
                'notes' => $notes,
                'requested_by' => Auth::id(),
            ]);

            foreach ($items as $item) {
                InventoryTransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            return $transfer;
        });
    }

    /**
     * Approve a transfer
     */
    public function approveTransfer($transferId)
    {
        $transfer = InventoryTransfer::findOrFail($transferId);

        if ($transfer->status !== 'requested') {
            throw new Exception('Only requested transfers can be approved.');
        }

        $transfer->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
        ]);

        return $transfer;
    }

    /**
     * Dispatch items (Deduct from source branch)
     */
    public function dispatchTransfer($transferId, $waybill = null, $securitySeal = null)
    {
        return DB::transaction(function () use ($transferId, $waybill, $securitySeal) {
            $transfer = InventoryTransfer::with('items.product')->findOrFail($transferId);

            if ($transfer->status !== 'approved') {
                throw new Exception('Transfer must be approved before dispatch.');
            }

            foreach ($transfer->items as $item) {
                $product = $item->product;

                if ($product->current_stock < $item->quantity) {
                    throw new Exception("Insufficient stock for {$product->name} at source branch.");
                }

                $product->updateStock($item->quantity, 'subtract', "transfer_dispatch:{$transfer->transfer_number}");
            }

            // Tier-1 Security: Generate OTP for receiver
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            $transfer->update([
                'status' => 'dispatched',
                'otp_code' => $otp,
                'waybill_number' => $waybill,
                'security_seal_number' => $securitySeal,
                'dispatched_by' => Auth::id(),
                'dispatched_at' => now(),
            ]);

            return $transfer;
        });
    }

    /**
     * Receive items (Add to destination branch)
     * Includes OTP verification and Shortage Detection
     */
    public function receiveTransfer($transferId, string $otp, array $receivedItems = [])
    {
        return DB::transaction(function () use ($transferId, $otp, $receivedItems) {
            $transfer = InventoryTransfer::with('items.product')->findOrFail($transferId);

            if ($transfer->status !== 'dispatched') {
                throw new Exception('Transfer must be dispatched before receiving.');
            }

            if ($transfer->otp_code !== $otp) {
                throw new Exception('Invalid Security OTP. Receipt rejected.');
            }

            $discrepancyFound = false;
            $discrepancyNotes = '';

            foreach ($transfer->items as $item) {
                $receivedQty = $receivedItems[$item->id] ?? $item->quantity;
                $item->update(['received_quantity' => $receivedQty]);

                if ($receivedQty != $item->quantity) {
                    $discrepancyFound = true;
                    $diff = $item->quantity - $receivedQty;
                    $discrepancyNotes .= "Product {$item->product->sku}: Shortage of {$diff}. ";
                }

                $product = $item->product;

                // Move product record to new branch
                $product->update([
                    'branch_id' => $transfer->to_branch_id,
                    'updated_by' => Auth::id(),
                ]);

                // Only add what was actually received to the destination stock
                $product->updateStock($receivedQty, 'add', "transfer_receive:{$transfer->transfer_number}");
            }

            $transfer->update([
                'status' => 'received',
                'is_otp_verified' => true,
                'discrepancy_found' => $discrepancyFound,
                'discrepancy_notes' => $discrepancyNotes ?: null,
                'received_by' => Auth::id(),
                'received_at' => now(),
            ]);

            return $transfer;
        });
    }

    /**
     * Generate unique transfer number
     */
    protected function generateTransferNumber()
    {
        return 'TRF-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4));
    }
}
