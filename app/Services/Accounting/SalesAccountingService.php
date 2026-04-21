<?php

namespace App\Services\Accounting;

use App\Models\Cashbook;
use App\Models\ChartOfAccount;
use App\Models\CustomerLedger;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use Illuminate\Support\Facades\DB;

class SalesAccountingService
{
    private array $codes;

    public function __construct(private AccountingService $accountingService)
    {
        $this->codes = config('accounting.codes', []);
    }

    private function getCode(string $key): string
    {
        if (isset($this->codes[$key])) {
            return $this->codes[$key];
        }

        $code = config("accounting.codes.{$key}");
        if (! $code) {
            throw new \Exception("Accounting code for '{$key}' is not defined in config/accounting.php");
        }

        return $code;
    }

    public function reverseSaleAccounting(string $invoiceNo, string $reason): void
    {
        $this->accountingService->reverseJournalEntry($invoiceNo, $reason);
    }

    public function postSaleToAccounting(PosSale $sale): void
    {
        DB::transaction(function () use ($sale) {
            // Reverse previous entries for this sale if any (idempotency)
            $this->accountingService->reverseJournalEntry($sale->invoice_no, 'Re-posting sale after update');

            // Skip main revenue posting if it's a memo (consignment)
            // We only track it as a memorandum entry or skip it entirely depending on policy.
            // Tier-1: Stock is already deducted, but revenue/receivable is not recognized.
            if ($sale->is_memo) {
                // We still want to track metal movement for memos to know customer's physical liability
                $this->postToMetalLedger($sale);
                $sale->addAuditLog('memo_tracked', "Memo #{$sale->invoice_no} tracked without revenue recognition. Metal ledger updated.");

                return;
            }

            $items = [];

            // 1. ASSETS (DEBITS)

            // Payments Received
            foreach ($sale->payments as $payment) {
                if ($payment->amount <= 0 || $payment->status !== 'completed') {
                    continue;
                }

                $account = $this->getPaymentAccount($payment->payment_method);
                $items[] = [
                    'account_id' => $account->id,
                    'debit' => $payment->amount,
                    'credit' => 0,
                    'description' => "Payment for Invoice #{$sale->invoice_no} ({$payment->payment_method})",
                ];

                // Cashbook integration
                $this->postCashbookEntry($sale, $payment);
            }

            // Accounts Receivable (Outstanding balance)
            if ($sale->outstanding_balance > 0) {
                $accRec = $this->accountingService->getAccountByCode($this->getCode('receivable'));
                $items[] = [
                    'account_id' => $accRec->id,
                    'debit' => $sale->outstanding_balance,
                    'credit' => 0,
                    'description' => "Outstanding balance for Invoice #{$sale->invoice_no}",
                ];
            }

            // Discounts Allowed (Expense - Debit)
            if ($sale->discount > 0) {
                $discountAcc = $this->accountingService->getAccountByCode($this->getCode('discount_allowed'));
                $items[] = [
                    'account_id' => $discountAcc->id,
                    'debit' => $sale->discount,
                    'credit' => 0,
                    'description' => "Sale-level discount on Invoice #{$sale->invoice_no}",
                ];
            }

            // Item-level discounts
            $itemDiscounts = $sale->items->sum('discount_amount');
            if ($itemDiscounts > 0) {
                $discountAcc = $this->accountingService->getAccountByCode($this->getCode('discount_allowed'));
                $items[] = [
                    'account_id' => $discountAcc->id,
                    'debit' => $itemDiscounts,
                    'credit' => 0,
                    'description' => "Item-level discounts on Invoice #{$sale->invoice_no}",
                ];
            }

            // 2. REVENUE & LIABILITIES (CREDITS)

            // Categorize and split revenue based on jewelry types
            $revenueSplits = $this->calculateDetailedRevenueSplits($sale);

            foreach ($revenueSplits as $code => $amount) {
                if ($amount <= 0) {
                    continue;
                }
                $account = $this->accountingService->getAccountByCode($code);
                $items[] = [
                    'account_id' => $account->id,
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => "Revenue ({$account->account_name}) - Invoice #{$sale->invoice_no}",
                ];
            }

            // Making Charges Income
            if ($sale->making_charges > 0) {
                $makingAcc = $this->accountingService->getAccountByCode($this->getCode('making_charges'));
                $items[] = [
                    'account_id' => $makingAcc->id,
                    'debit' => 0,
                    'credit' => $sale->making_charges,
                    'description' => "Making Charges - Invoice #{$sale->invoice_no}",
                ];
            }

            // Wastage Income/Recovery (if any) - recorded as part of revenue or separate account
            // In many shops, wastage is included in the gold price or recorded separately
            if ($sale->wastage_amount > 0) {
                // For now, we add it to Gold Sales or a separate Recovery account
                $goldAcc = $this->accountingService->getAccountByCode($this->getCode('gold_sales'));
                $items[] = [
                    'account_id' => $goldAcc->id,
                    'debit' => 0,
                    'credit' => $sale->wastage_amount,
                    'description' => "Wastage Recovery - Invoice #{$sale->invoice_no}",
                ];
            }

            // Sales Tax (GST) Payable
            if ($sale->tax_amount > 0) {
                $taxAcc = $this->accountingService->getAccountByCode($this->getCode('tax_payable'));
                $items[] = [
                    'account_id' => $taxAcc->id,
                    'debit' => 0,
                    'credit' => $sale->tax_amount,
                    'description' => "GST Collected - Invoice #{$sale->invoice_no}",
                ];
            }

            // Final Balancing check and Journal Posting
            $this->accountingService->createJournalEntry([
                'reference_number' => $sale->invoice_no,
                'entry_date' => $sale->sale_time->toDateString(),
                'narration' => "POS Sale #{$sale->invoice_no} posted to accounting",
                'reference_type' => 'sale',
                'branch_id' => $sale->branch_id,
                'items' => $items,
            ]);

            // 3. CUSTOMER LEDGER INTEGRATION
            $this->postToCustomerLedger($sale);
            $this->postToMetalLedger($sale);

            $sale->addAuditLog('accounting_posted', "Sale #{$sale->invoice_no} posted with balanced entry and customer ledger updated.");
        });
    }

    private function postToMetalLedger(PosSale $sale): void
    {
        if (! $sale->pos_customer_id) {
            return;
        }

        // Handle Metal Payments (Gold/Silver coming in from customer)
        foreach ($sale->payments as $payment) {
            if ($payment->status !== 'completed' || ! $payment->metal_type) {
                continue;
            }

            // For Old Gold Payments, we calculate Fine Weight
            // If purity is not provided, we assume 24K for simplicity, but in production, purity is mandatory for old gold.
            $purity = (float) ($payment->meta['purity'] ?? 100);
            $meltingLoss = (float) ($payment->meta['melting_loss'] ?? 0);
            $effectivePurity = $purity - $meltingLoss;

            $fineWeight = $payment->metal_weight * ($effectivePurity / 100);

            $this->recordMetalMovement(
                $sale,
                $payment->metal_type,
                $payment->metal_weight,
                $fineWeight,
                0,
                0,
                'payment',
                $payment->id,
                "Old Gold Payment - Invoice #{$sale->invoice_no} ({$purity}% purity)",
                $effectivePurity
            );
        }

        // Handle Sale Items (Gold/Silver going out to customer)
        foreach ($sale->items as $item) {
            $metalType = $this->identifyCategory($item);
            if (! in_array($metalType, ['gold', 'silver'])) {
                continue;
            }

            // Get purity from item
            $purity = 100;
            if ($item->gold_purity) {
                // Extract numeric purity (e.g. "22K (91.6%)" -> 91.6)
                preg_match('/(\d+\.?\d*)/', $item->gold_purity, $matches);
                if (isset($matches[1])) {
                    $val = (float) $matches[1];
                    $purity = ($val <= 24) ? ($val / 24 * 100) : $val; // Handle 22 vs 91.6
                }
            }

            $grossWeight = $item->weight * $item->quantity;
            $fineWeight = $grossWeight * ($purity / 100);

            $this->recordMetalMovement(
                $sale,
                $metalType,
                0,
                0,
                $grossWeight,
                $fineWeight,
                'sale',
                $item->id,
                "Item Sold - Invoice #{$sale->invoice_no}",
                $purity
            );
        }
    }

    private function recordMetalMovement(
        PosSale $sale,
        string $metalType,
        float $in,
        float $fineIn,
        float $out,
        float $fineOut,
        string $refType,
        int $refId,
        string $desc,
        float $purity = 100
    ): void {
        $customer = $sale->customer;

        $currentGrossBalance = ($metalType === 'gold') ? $customer->current_gold_balance : $customer->current_silver_balance;
        $currentFineBalance = ($metalType === 'gold') ? $customer->current_fine_gold_balance : $customer->current_fine_silver_balance;

        $newGrossBalance = $currentGrossBalance + $in - $out;
        $newFineBalance = $currentFineBalance + $fineIn - $fineOut;

        \App\Models\CustomerMetalLedger::create([
            'customer_id' => $sale->pos_customer_id,
            'branch_id' => $sale->branch_id,
            'transaction_date' => $sale->sale_time->toDateString(),
            'transaction_type' => ($refType === 'payment') ? 'payment' : 'sale',
            'metal_type' => $metalType,
            'purity_percentage' => $purity,
            'weight_in' => $in,
            'fine_weight_in' => $fineIn,
            'weight_out' => $out,
            'fine_weight_out' => $fineOut,
            'running_weight_balance' => $newGrossBalance,
            'running_fine_balance' => $newFineBalance,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'description' => $desc,
            'created_by' => auth()->id() ?? 1,
        ]);

        // Update Customer Running Balances
        if ($metalType === 'gold') {
            $customer->update([
                'current_gold_balance' => $newGrossBalance,
                'current_fine_gold_balance' => $newFineBalance,
            ]);
        } else {
            $customer->update([
                'current_silver_balance' => $newGrossBalance,
                'current_fine_silver_balance' => $newFineBalance,
            ]);
        }
    }

    private function postToCustomerLedger(PosSale $sale): void
    {
        if (! $sale->pos_customer_id) {
            return;
        }

        $ledger = CustomerLedger::firstOrCreate(
            ['customer_id' => $sale->pos_customer_id, 'branch_id' => $sale->branch_id],
            ['opening_balance' => 0, 'status' => 'active']
        );

        // Record Sale as Debit
        $ledger->entries()->create([
            'date' => $sale->sale_time->toDateString(),
            'type' => 'debit',
            'amount' => $sale->total,
            'reference_type' => 'sale',
            'reference_id' => $sale->id,
            'description' => ($sale->is_wholesale ? 'Wholesale Sale' : 'Retail Sale')." - Invoice #{$sale->invoice_no}",
            'running_balance' => $ledger->current_balance + $sale->total,
        ]);

        // Record Payments as Credits
        foreach ($sale->payments as $payment) {
            if ($payment->status === 'completed' && $payment->amount > 0) {
                $ledger->entries()->create([
                    'date' => $payment->created_at->toDateString(),
                    'type' => 'credit',
                    'amount' => $payment->amount,
                    'reference_type' => 'payment',
                    'reference_id' => $payment->id,
                    'description' => "Payment received for Invoice #{$sale->invoice_no}",
                    'running_balance' => $ledger->current_balance + $sale->total - $payment->amount, // Approximate, updateBalance will fix it
                ]);
            }
        }

        $ledger->updateBalance();
    }

    private function calculateDetailedRevenueSplits(PosSale $sale): array
    {
        $splits = [
            $this->getCode('gold_sales') => 0,
            $this->getCode('diamond_sales') => 0,
            $this->getCode('silver_sales') => 0,
            $this->getCode('stones_sales') => 0,
        ];

        foreach ($sale->items as $item) {
            $cat = $this->identifyCategory($item);

            // Gold price (Weight * Rate)
            $goldPrice = $item->weight * $item->gold_rate;

            // Product price (Quantity * Unit Price) - typically for non-weight items or fixed price items
            $productPrice = $item->quantity * $item->unit_price;

            $revenue = $goldPrice + $productPrice;

            if ($cat === 'diamond') {
                $splits[$this->getCode('diamond_sales')] += $revenue;
            } elseif ($cat === 'silver') {
                $splits[$this->getCode('silver_sales')] += $revenue;
            } else {
                $splits[$this->getCode('gold_sales')] += $revenue;
            }

            // Stones are often tracked separately even in gold/diamond jewelry
            if ($item->stone_price > 0) {
                $splits[$this->getCode('stones_sales')] += $item->stone_price;
            }
        }

        return $splits;
    }

    private function identifyCategory(PosSaleItem $item): string
    {
        if ($item->inventoryProduct && $item->inventoryProduct->category) {
            $catName = strtolower($item->inventoryProduct->category->name);
            if (str_contains($catName, 'diamond')) {
                return 'diamond';
            }
            if (str_contains($catName, 'silver')) {
                return 'silver';
            }
        }

        // Check purity for hints
        if ($item->gold_purity) {
            $purity = strtolower($item->gold_purity);
            if (str_contains($purity, 'silver')) {
                return 'silver';
            }
        }

        return 'gold';
    }

    private function getPaymentAccount(string $method): ChartOfAccount
    {
        $code = ($method === 'cash') ? $this->getCode('cash') : $this->getCode('bank');

        return $this->accountingService->getAccountByCode($code);
    }

    private function postCashbookEntry(PosSale $sale, $payment): void
    {
        Cashbook::create([
            'branch_id' => $sale->branch_id,
            'user_id' => auth()->id(),
            'date' => $payment->created_at->toDateString(),
            'entry_type' => 'receipt',
            'category' => 'sales',
            'subcategory' => 'pos_sale',
            'amount' => $payment->amount,
            'reference_type' => 'sale',
            'reference_id' => $sale->id,
            'description' => "Payment received - Invoice #{$sale->invoice_no}",
            'payment_method' => $payment->payment_method,
            'status' => 'completed',
        ]);
    }

    public function reverseAccountingEntries(PosSale $sale): void
    {
        $this->accountingService->reverseJournalEntry($sale->invoice_no, 'Manual Reversal/Sale Cancellation');
        Cashbook::where('reference_type', 'sale')->where('reference_id', $sale->id)->delete();

        $ledger = CustomerLedger::where('customer_id', $sale->pos_customer_id)->where('branch_id', $sale->branch_id)->first();
        if ($ledger) {
            $ledger->entries()->where('reference_type', 'sale')->where('reference_id', $sale->id)->delete();
            $ledger->entries()->where('reference_type', 'payment')->where('description', 'like', "%#{$sale->invoice_no}%")->delete();
            $ledger->updateBalance();
        }
    }
}
