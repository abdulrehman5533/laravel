<?php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

class PurchaseAccountingService
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

    public function postPurchaseToAccounting(PurchaseOrder $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            $this->accountingService->reverseJournalEntry($purchase->purchase_no, 'Re-posting purchase');

            $items = [];

            // 1. ASSETS (DEBITS) - Inventory Increase
            $inventorySplits = $this->calculateInventorySplits($purchase);

            foreach ($inventorySplits as $code => $amount) {
                if ($amount <= 0) {
                    continue;
                }
                $account = $this->accountingService->getAccountByCode($code);
                $items[] = [
                    'account_id' => $account->id,
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => "Inventory Purchase ({$account->account_name}) - PO #{$purchase->purchase_no}",
                ];
            }

            // Input Tax (Debit Asset/Liability reduction)
            if ($purchase->tax_amount > 0) {
                $taxAcc = $this->accountingService->getAccountByCode($this->getCode('tax_payable'));
                $items[] = [
                    'account_id' => $taxAcc->id,
                    'debit' => $purchase->tax_amount,
                    'credit' => 0,
                    'description' => "Input Tax on PO #{$purchase->purchase_no}",
                ];
            }

            // 2. LIABILITIES/ASSETS (CREDITS) - Payment/Payable

            // Payments Made
            $totalPaid = 0;
            foreach ($purchase->payments ?? [] as $payment) {
                $account = $this->getPaymentAccount($payment->payment_method);
                $items[] = [
                    'account_id' => $account->id,
                    'debit' => 0,
                    'credit' => $payment->amount,
                    'description' => "Payment to Supplier - PO #{$purchase->purchase_no}",
                ];
                $totalPaid += $payment->amount;
            }

            // Accounts Payable (Balance)
            $outstanding = $purchase->total_amount - $totalPaid;
            if ($outstanding > 0) {
                $payableAcc = $this->accountingService->getAccountByCode($this->getCode('payable'));
                $items[] = [
                    'account_id' => $payableAcc->id,
                    'debit' => 0,
                    'credit' => $outstanding,
                    'description' => "Payable to Supplier - PO #{$purchase->purchase_no}",
                ];
            }

            $this->accountingService->createJournalEntry([
                'reference_number' => $purchase->purchase_no,
                'entry_date' => $purchase->purchase_date->toDateString(),
                'narration' => "Purchase Order #{$purchase->purchase_no} posted to accounting",
                'reference_type' => 'purchase',
                'branch_id' => $purchase->branch_id,
                'items' => $items,
            ]);
        });
    }

    private function calculateInventorySplits(PurchaseOrder $purchase): array
    {
        $splits = [
            $this->getCode('gold_inventory') => 0,
            $this->getCode('diamond_inventory') => 0,
            $this->getCode('silver_inventory') => 0,
            $this->getCode('stones_inventory') => 0,
        ];

        foreach ($purchase->items as $item) {
            $cat = $this->identifyCategory($item);
            $amount = $item->subtotal; // Cost price total

            if ($cat === 'diamond') {
                $splits[$this->getCode('diamond_inventory')] += $amount;
            } elseif ($cat === 'silver') {
                $splits[$this->getCode('silver_inventory')] += $amount;
            } elseif ($cat === 'stones') {
                $splits[$this->getCode('stones_inventory')] += $amount;
            } else {
                $splits[$this->getCode('gold_inventory')] += $amount;
            }
        }

        return $splits;
    }

    private function identifyCategory($item): string
    {
        if ($item->product && $item->product->category) {
            $catName = strtolower($item->product->category->name);
            if (str_contains($catName, 'diamond')) {
                return 'diamond';
            }
            if (str_contains($catName, 'silver')) {
                return 'silver';
            }
            if (str_contains($catName, 'stone') || str_contains($catName, 'gem')) {
                return 'stones';
            }
        }

        return 'gold';
    }

    private function getPaymentAccount(string $method): ChartOfAccount
    {
        $code = ($method === 'cash') ? $this->getCode('cash') : $this->getCode('bank');

        return $this->accountingService->getAccountByCode($code);
    }
}
