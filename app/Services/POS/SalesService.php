<?php

namespace App\Services\POS;

use App\Models\Customer;
use App\Models\InventoryProduct;
use App\Models\PosPayment;
use App\Models\PosReturnRepair;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Services\GeneralLedgerService;
use App\Services\MetalLedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalesService
{
    public function __construct(
        private POSService $posService,
        private PricingService $pricingService,
        private InvoiceService $invoiceService,
        private MetalLedgerService $metalLedgerService,
        private GeneralLedgerService $glService
    ) {}

    public function createSale(array $data): PosSale
    {
        return DB::transaction(function () use ($data) {
            $invoice = 'INV-'.date('Ymd').'-'.Str::random(6);

            $sale = PosSale::create([
                'invoice_no' => $invoice,
                'invoice_type' => $data['invoice_type'] ?? 'Non-Tax',
                'branch_id' => $data['branch_id'] ?? (auth()->user()->branch_id ?? 1),
                'pos_customer_id' => $data['customer_id'] ?? null,
                'created_by' => auth()->id(),
                'sale_time' => now(),
                'currency' => $data['currency'] ?? 'PKR',
                'status' => 'open',
                'payment_status' => 'unpaid',
                'is_wholesale' => $data['is_wholesale'] ?? false,
                'discount_type' => $data['discount_type'] ?? 'fixed',
                'discount_value' => $data['discount_value'] ?? 0,
            ]);

            if ($sale->pos_customer_id) {
                $sale->addAuditLog('sale_created', 'Sale created for customer: '.$sale->customer->name);
            }

            return $sale;
        });
    }

    public function addItemToSale(PosSale $sale, array $itemData): PosSaleItem
    {
        return DB::transaction(function () use ($sale, $itemData) {
            $item = $this->posService->addItem($sale, $itemData);
            $sale->recalculateTotals();

            $sale->addAuditLog('item_added', 'Product: '.($itemData['description'] ?? 'Unknown'), auth()->id());

            return $item;
        });
    }

    public function applyDiscount(PosSale $sale, string $type, float $value): void
    {
        DB::transaction(function () use ($sale, $type, $value) {
            $oldDiscount = $sale->discount_value;

            $sale->update([
                'discount_type' => $type,
                'discount_value' => $value,
            ]);

            $sale->recalculateTotals();

            $sale->addAuditLog('discount_applied', "Discount changed from {$oldDiscount} to {$value} ({$type})");
        });
    }

    public function recordPayment(PosSale $sale, array $paymentData): PosPayment
    {
        return DB::transaction(function () use ($sale, $paymentData) {
            $payment = PosPayment::create([
                'pos_sale_id' => $sale->id,
                'pos_customer_id' => $sale->pos_customer_id,
                'payment_method' => $paymentData['payment_method'],
                'amount' => $paymentData['amount'],
                'metal_type' => $paymentData['metal_type'] ?? null,
                'metal_weight' => $paymentData['metal_weight'] ?? 0,
                'metal_rate' => $paymentData['metal_rate'] ?? null,
                'is_bhav_cut' => $paymentData['is_bhav_cut'] ?? false,
                'currency' => $paymentData['currency'] ?? $sale->currency,
                'exchange_rate' => $paymentData['exchange_rate'] ?? 1.0,
                'bank_name' => $paymentData['bank_name'] ?? null,
                'cheque_number' => $paymentData['cheque_number'] ?? null,
                'transaction_id' => $paymentData['transaction_id'] ?? null,
                'reference' => $paymentData['reference'] ?? null,
                'notes' => $paymentData['notes'] ?? null,
                'recorded_by' => auth()->id(),
                'status' => 'completed',
            ]);

            $sale->recalculateTotals();
            $this->updatePaymentStatus($sale);

            // Record in Metal Ledger if it's a metal payment
            if ($payment->metal_type && $payment->metal_weight > 0 && $sale->pos_customer_id) {
                // Calculate fine weight for payment if not provided
                $purity = 100; // Default to pure
                $fineWeight = $payment->metal_weight * ($purity / 100);

                $this->metalLedgerService->recordCustomerTransaction($sale->customer, [
                    'metal_type' => $payment->metal_type,
                    'weight' => $payment->metal_weight,
                    'fine_weight' => $fineWeight,
                    'transaction_type' => $payment->is_bhav_cut ? 'bhav_cut' : 'payment',
                    'transaction_date' => now(),
                    'reference_type' => 'PosPayment',
                    'reference_id' => $payment->id,
                    'description' => ($payment->is_bhav_cut ? 'Bhav Cut: ' : 'Metal Payment: ')."{$payment->metal_weight}g {$payment->metal_type} @ {$payment->metal_rate}",
                ]);
            }

            // Post payment to General Ledger
            $this->glService->postPosPayment($payment);

            $sale->addAuditLog('payment_recorded', "Payment of {$payment->amount} received".($payment->metal_type ? " with {$payment->metal_weight}g {$payment->metal_type}" : ''), auth()->id());

            if ($sale->outstanding_balance <= 0 && $sale->status !== 'completed') {
                $this->completeSale($sale);
            }

            return $payment;
        });
    }

    public function processBhavCut(PosSale $sale, array $data): PosPayment
    {
        return DB::transaction(function () use ($sale, $data) {
            // A Bhav Cut is a conversion of metal balance to cash amount
            $amount = $data['weight'] * $data['rate'];

            return $this->recordPayment($sale, [
                'payment_method' => 'bhav_cut',
                'amount' => $amount,
                'metal_type' => $data['metal_type'],
                'metal_weight' => $data['weight'],
                'metal_rate' => $data['rate'],
                'is_bhav_cut' => true,
                'notes' => $data['notes'] ?? 'Bhav Cut / Rate Cut Conversion',
            ]);
        });
    }

    public function recordPartialPayment(PosSale $sale, float $amount, array $paymentData): PosPayment
    {
        return DB::transaction(function () use ($sale, $amount, $paymentData) {
            $payment = $this->recordPayment($sale, array_merge($paymentData, ['amount' => $amount]));

            if ($sale->outstanding_balance > 0) {
                $dueDate = $paymentData['due_date'] ?? null;
                if ($dueDate) {
                    $sale->update(['due_date' => $dueDate]);
                }
            }

            return $payment;
        });
    }

    public function updatePayment(PosPayment $payment, array $paymentData): PosPayment
    {
        return DB::transaction(function () use ($payment, $paymentData) {
            $payment->update($paymentData);

            $sale = $payment->sale;
            $sale->recalculateTotals();
            $this->updatePaymentStatus($sale);

            if ($sale->outstanding_balance <= 0) {
                $this->completeSale($sale);
            }

            $sale->addAuditLog('payment_updated', "Payment of {$payment->amount} updated", auth()->id());

            return $payment;
        });
    }

    public function completeSale(PosSale $sale): void
    {
        DB::transaction(function () use ($sale) {
            if ($sale->status === 'completed') {
                return;
            }

            if ($sale->items->isEmpty()) {
                throw new \Exception('Cannot complete sale without items');
            }

            $this->updateInvoiceNumberForType($sale);

            if ($sale->invoice_type === 'Non-Tax') {
                foreach ($sale->items as $item) {
                    $item->update([
                        'tax_percent' => 0,
                        'tax_amount' => 0,
                    ]);
                }
                $sale->update(['tax_amount' => 0]);
                $sale->recalculateTotals();
            }

            foreach ($sale->items as $item) {
                if ($item->product_id && ! $sale->stock_moved) {
                    $product = InventoryProduct::find($item->product_id);
                    if ($product) {
                        if ($product->current_stock < $item->quantity) {
                            throw new \Exception("Insufficient stock for {$product->name}");
                        }
                        $product->updateStock($item->quantity, 'subtract', 'sale:'.$sale->invoice_no);
                    }
                }
            }

            if ($sale->pos_customer_id) {
                $customer = $sale->customer;

                // Capture balances before completing this sale (for carry-forward display)
                $sale->update([
                    'previous_balance_cash' => $customer->current_balance,
                    'previous_balance_gold' => $customer->current_gold_balance,
                    'previous_balance_silver' => $customer->current_silver_balance,
                ]);

                // Record Metal Ledger entries for each item
                foreach ($sale->items as $item) {
                    if ($item->fine_weight > 0) {
                        $this->metalLedgerService->recordCustomerTransaction($customer, [
                            'metal_type' => str_contains(strtolower($item->description), 'silver') ? 'silver' : 'gold',
                            'weight' => $item->net_weight + $item->wastage_weight,
                            'fine_weight' => $item->fine_weight,
                            'transaction_type' => 'sale',
                            'transaction_date' => $sale->sale_time,
                            'reference_type' => 'PosSale',
                            'reference_id' => $sale->id,
                            'description' => "Sale Item: {$item->sku} - {$item->description}",
                            'purity_percentage' => $item->gold_purity ? (float) filter_var($item->gold_purity, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null,
                        ]);
                    }
                }

                $points = (int) floor($sale->total);
                $customer->addLoyaltyPoints($points);
                $customer->updatePurchaseStats($sale->total);

                $sale->update([
                    'loyalty_points_earned' => $points,
                ]);
            }

            $sale->update([
                'status' => 'completed',
                'stock_moved' => true,
            ]);

            // Post to General Ledger
            $this->glService->postPosSale($sale);

            $this->updatePaymentStatus($sale);

            try {
                $this->invoiceService->generateAllFormats($sale);
            } catch (\Exception $e) {
                logger()->error('Failed to generate invoices: '.$e->getMessage());
            }

            $sale->addAuditLog('sale_completed', 'Sale finalized and invoices generated', auth()->id());
        });
    }

    public function cancelSale(PosSale $sale, ?string $reason = null): void
    {
        DB::transaction(function () use ($sale, $reason) {
            if (in_array($sale->status, ['completed', 'returned'])) {
                throw new \Exception('Cannot cancel '.$sale->status.' sale');
            }

            if ($sale->stock_moved) {
                $this->posService->restockSale($sale);
            }

            $sale->update([
                'status' => 'cancelled',
                'payment_status' => 'cancelled',
            ]);

            $sale->addAuditLog('sale_cancelled', $reason ?? 'Sale cancelled', auth()->id());
        });
    }

    public function processSaleReturn(PosSale $sale, array $returnData): PosReturnRepair
    {
        return DB::transaction(function () use ($sale, $returnData) {
            if ($sale->status !== 'completed') {
                throw new \Exception('Only completed sales can be returned');
            }

            $return = PosReturnRepair::create([
                'pos_sale_id' => $sale->id,
                'pos_sale_item_id' => $returnData['item_id'] ?? null,
                'type' => $returnData['type'] ?? 'return',
                'return_date' => now(),
                'reason' => $returnData['reason'] ?? null,
                'notes' => $returnData['notes'] ?? null,
                'status' => 'initiated',
                'refund_method' => $returnData['refund_method'] ?? 'original',
            ]);

            $return->update(['refund_amount' => $this->calculateReturnAmount($return)]);

            $sale->addAuditLog('return_initiated', 'Return initiated: '.($returnData['reason'] ?? ''), auth()->id());

            return $return;
        });
    }

    public function approveReturn(PosReturnRepair $return): void
    {
        DB::transaction(function () use ($return) {
            $return->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
            ]);

            $return->sale->addAuditLog('return_approved', 'Return approved for amount: '.$return->refund_amount, auth()->id());
        });
    }

    public function processReturn(PosReturnRepair $return): void
    {
        DB::transaction(function () use ($return) {
            $sale = $return->sale;

            if ($return->item) {
                $product = $return->item->inventoryProduct;
                if ($product && $sale->stock_moved) {
                    $product->updateStock($return->item->quantity, 'add', 'return:'.$sale->invoice_no);
                }
            }

            $return->update([
                'status' => 'processed',
                'processed_by' => auth()->id(),
            ]);

            if ($return->refund_amount > 0) {
                $this->recordRefundPayment($sale, $return);
            }

            $sale->recalculateTotals();
            $sale->addAuditLog('return_processed', 'Return processed for amount: '.$return->refund_amount, auth()->id());
        });
    }

    public function holdSale(PosSale $sale, ?string $note = null): void
    {
        DB::transaction(function () use ($sale, $note) {
            $this->posService->holdSale($sale, $note);
            $sale->addAuditLog('sale_held', $note ?? 'Sale held', auth()->id());
        });
    }

    public function resumeHeldSale(PosSale $sale): void
    {
        DB::transaction(function () use ($sale) {
            if ($sale->status !== 'held') {
                throw new \Exception('Only held sales can be resumed');
            }

            $sale->update(['status' => 'open']);
            $sale->addAuditLog('sale_resumed', 'Sale resumed', auth()->id());
        });
    }

    public function processURDPurchase(PosSale $sale, array $data): PosPayment
    {
        return DB::transaction(function () use ($sale, $data) {
            // URD Purchase is when shop buys old gold/silver from customer
            // It acts as a payment towards the sale
            $amount = $data['weight'] * $data['rate'];

            $payment = $this->recordPayment($sale, [
                'payment_method' => 'urd_purchase',
                'amount' => $amount,
                'metal_type' => $data['metal_type'],
                'metal_weight' => $data['weight'],
                'metal_rate' => $data['rate'],
                'notes' => $data['notes'] ?? 'URD Purchase (Customer Old Metal)',
            ]);

            // Add to URD Stock (Optional integration depending on if we have a separate URD stock)
            // For now, it's just recorded in metal ledger as IN

            return $payment;
        });
    }

    private function updatePaymentStatus(PosSale $sale): void
    {
        if ($sale->outstanding_balance <= 0) {
            $sale->update(['payment_status' => 'paid']);
        } elseif ($sale->payments()->exists()) {
            $sale->update(['payment_status' => 'partial']);
        } else {
            $sale->update(['payment_status' => 'unpaid']);
        }
    }

    private function calculateReturnAmount(PosReturnRepair $return): float
    {
        if ($return->item) {
            return $return->item->line_total;
        }

        return 0;
    }

    private function recordRefundPayment(PosSale $sale, PosReturnRepair $return): void
    {
        PosPayment::create([
            'pos_sale_id' => $sale->id,
            'pos_customer_id' => $sale->pos_customer_id,
            'payment_method' => $return->refund_method,
            'amount' => -abs($return->refund_amount),
            'currency' => $sale->currency,
            'reference' => 'REFUND-'.$return->id,
            'status' => 'completed',
            'recorded_by' => auth()->id(),
        ]);
    }

    private function updateInvoiceNumberForType(PosSale $sale): void
    {
        if ($sale->invoice_type === 'Non-Tax') {
            $prefix = 'NTX';
        } else {
            $prefix = 'INV';
        }

        $invoiceNo = $prefix.'-'.date('Ymd').'-'.Str::random(6);
        $sale->update(['invoice_no' => $invoiceNo]);
    }
}
