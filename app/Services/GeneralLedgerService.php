<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\GeneralLedger;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;

class GeneralLedgerService
{
    /**
     * Post a single entry to the General Ledger
     */
    public function postEntry(ChartOfAccount $account, string $type, float $amount, string $description, ?int $referenceId = null, ?int $branchId = null): GeneralLedger
    {
        $branchId = $branchId ?? ($account->branch_id ?? 1);
        $runningBalance = $account->current_balance;

        // Normal balance logic
        // Assets/Expenses: Debit increases (+), Credit decreases (-)
        // Liabilities/Equity/Revenue: Credit increases (+), Debit decreases (-)
        $isNormalDebit = in_array($account->account_type, ['Asset', 'Expense']);

        if ($type === 'debit') {
            $runningBalance = $isNormalDebit ? ($runningBalance + $amount) : ($runningBalance - $amount);
        } else {
            $runningBalance = $isNormalDebit ? ($runningBalance - $amount) : ($runningBalance + $amount);
        }

        $entry = GeneralLedger::create([
            'account_id' => $account->id,
            'branch_id' => $branchId,
            'date' => now()->toDateString(),
            'entry_type' => $referenceId ? 'journal_entry' : 'auto_entry',
            'reference_id' => $referenceId,
            'debit' => $type === 'debit' ? $amount : 0,
            'credit' => $type === 'credit' ? $amount : 0,
            'running_balance' => $runningBalance,
            'description' => $description,
            'created_by' => auth()->id() ?? 1,
        ]);

        $account->update(['current_balance' => $runningBalance]);

        return $entry;
    }

    /**
     * Helper to get account by mapping key
     */
    public function getAccountByMapping(string $key): ChartOfAccount
    {
        $account = ChartOfAccount::where('mapping_key', $key)->first();
        if (! $account) {
            throw new \Exception("Chart of Account mapping missing for: {$key}");
        }

        return $account;
    }

    /**
     * Automated POS Sale Posting
     */
    public function postPosSale(\App\Models\PosSale $sale): void
    {
        $items = [
            ['key' => 'ar_customers', 'debit' => $sale->total, 'credit' => 0, 'desc' => "Sale Invoice #{$sale->invoice_no}"],
            ['key' => 'sales_gold', 'debit' => 0, 'credit' => $sale->subtotal, 'desc' => 'Gold Sales Revenue'],
        ];

        if ($sale->tax_amount > 0) {
            $items[] = ['key' => 'tax_gst_payable', 'debit' => 0, 'credit' => $sale->tax_amount, 'desc' => 'Tax on Sale'];
        }

        if ($sale->making_charges > 0) {
            $items[] = ['key' => 'income_making_charges', 'debit' => 0, 'credit' => $sale->making_charges, 'desc' => 'Making Charges Income'];
        }

        if ($sale->discount > 0) {
            $items[] = ['key' => 'expense_discount', 'debit' => $sale->discount, 'credit' => 0, 'desc' => 'Sales Discount'];
        }

        $this->createAndPostJournal("POS Sale: {$sale->invoice_no}", $items, $sale->branch_id, 'PosSale', $sale->id);
    }

    /**
     * Automated POS Payment Posting
     */
    public function postPosPayment(\App\Models\PosPayment $payment): void
    {
        $items = [];
        $method = strtolower($payment->payment_method);

        if ($method === 'cash') {
            $items[] = ['key' => 'cash_in_hand', 'debit' => $payment->amount, 'credit' => 0, 'desc' => 'Cash payment for Sale'];
        } elseif ($method === 'bank' || $method === 'online' || $method === 'cheque') {
            $items[] = ['key' => 'bank_main', 'debit' => $payment->amount, 'credit' => 0, 'desc' => 'Bank payment for Sale'];
        } elseif ($method === 'card') {
            $items[] = ['key' => 'bank_card', 'debit' => $payment->amount, 'credit' => 0, 'desc' => 'Card payment for Sale'];
        } elseif ($method === 'urd_purchase') {
            $items[] = ['key' => 'inv_urd', 'debit' => $payment->amount, 'credit' => 0, 'desc' => 'URD Purchase (Exchange) for Sale'];
        } elseif ($method === 'bhav_cut') {
            // Bhav Cut: No cash moves, but AR remains the same (it was already debited during sale)
            // Wait, if the sale was already recorded at a certain price, and Bhav Cut is at a different price?
            // Usually, if a sale is "Metal Sale", it might have been recorded at 0 or placeholder.
            // For now, assume AR was already recorded at the sale's total.
            return;
        } else {
            // Default to bank for others
            $items[] = ['key' => 'bank_main', 'debit' => $payment->amount, 'credit' => 0, 'desc' => ucfirst($method).' payment for Sale'];
        }

        $items[] = ['key' => 'ar_customers', 'debit' => 0, 'credit' => $payment->amount, 'desc' => 'Customer payment received'];

        $this->createAndPostJournal("POS Payment: {$payment->id}", $items, $payment->sale->branch_id, 'PosPayment', $payment->id);
    }

    /**
     * Post a standalone Bhav Cut (Metal to Cash conversion)
     */
    public function postManualBhavCut(\App\Models\Customer $customer, float $amount, string $description): void
    {
        $items = [
            ['key' => 'ar_customers', 'debit' => $amount, 'credit' => 0, 'desc' => $description],
            ['key' => 'sales_gold', 'debit' => 0, 'credit' => $amount, 'desc' => 'Gold Metal Settlement (Bhav Cut)'],
        ];

        $this->createAndPostJournal("Bhav Cut Settlement: {$customer->name}", $items, $customer->branch_id, 'Customer', $customer->id);
    }

    /**
     * Automated Purchase Posting
     */
    public function postPurchase(\App\Models\PurchaseOrder $purchase): void
    {
        $items = [
            ['key' => 'inv_gold', 'debit' => $purchase->total_amount - ($purchase->gst_amount ?? 0), 'credit' => 0, 'desc' => "Purchase Invoice #{$purchase->po_number}"],
            ['key' => 'ap_suppliers', 'debit' => 0, 'credit' => $purchase->total_amount, 'desc' => 'Payable to Supplier'],
        ];

        if ($purchase->gst_amount > 0) {
            $items[] = ['key' => 'tax_gst_input', 'debit' => $purchase->gst_amount, 'credit' => 0, 'desc' => 'GST Input on Purchase'];
        }

        $this->createAndPostJournal("Purchase: {$purchase->po_number}", $items, $purchase->branch_id, 'PurchaseOrder', $purchase->id);
    }

    /**
     * Automated Supplier Payment Posting
     */
    public function postSupplierPayment(\App\Models\SupplierPayment $payment): void
    {
        $items = [];
        $method = strtolower($payment->payment_method);

        if ($method === 'bank') {
            $items[] = ['key' => 'bank_main', 'debit' => 0, 'credit' => $payment->amount_paid, 'desc' => 'Bank payment to Supplier'];
        } else {
            $items[] = ['key' => 'cash_in_hand', 'debit' => 0, 'credit' => $payment->amount_paid, 'desc' => 'Cash payment to Supplier'];
        }

        $items[] = ['key' => 'ap_suppliers', 'debit' => $payment->amount_paid, 'credit' => 0, 'desc' => 'Supplier payment made'];

        $this->createAndPostJournal("Supplier Payment: {$payment->id}", $items, $payment->branch_id, 'SupplierPayment', $payment->id);
    }

    /**
     * Automated Purchase Return Posting
     */
    public function postPurchaseReturn($return): void
    {
        $items = [
            ['key' => 'ap_suppliers', 'debit' => $return->return_amount, 'credit' => 0, 'desc' => "Purchase Return #{$return->return_number}"],
            ['key' => 'inv_gold', 'debit' => 0, 'credit' => $return->return_amount - ($return->gst_amount ?? 0), 'desc' => 'Gold Inventory Reversal'],
        ];

        if (($return->gst_amount ?? 0) > 0) {
            $items[] = ['key' => 'tax_gst_input', 'debit' => 0, 'credit' => $return->gst_amount, 'desc' => 'GST Input Reversal'];
        }

        $this->createAndPostJournal("Purchase Return: {$return->return_number}", $items, $return->branch_id, 'PurchaseReturn', $return->id);
    }

    /**
     * Automated Karigar Settlement Posting
     */
    public function postKarigarSettlement(\App\Models\KarigarSettlement $settlement): void
    {
        $items = [
            ['key' => 'ap_suppliers', 'debit' => 0, 'credit' => $settlement->total_amount, 'desc' => "Payable for Settlement #{$settlement->settlement_number}"],
        ];

        if ($settlement->labor_amount > 0) {
            $items[] = ['key' => 'expense_labor', 'debit' => $settlement->labor_amount, 'credit' => 0, 'desc' => 'Labor Charges Expense'];
        }

        if ($settlement->metal_value > 0) {
            // If metal is fixed, it's like we bought that gold and put it in inventory
            $items[] = ['key' => 'inv_gold', 'debit' => $settlement->metal_value, 'credit' => 0, 'desc' => 'Metal Fixed in Settlement'];
        }

        if ($settlement->other_charges > 0) {
            $items[] = ['key' => 'expenses_operating', 'debit' => $settlement->other_charges, 'credit' => 0, 'desc' => 'Other Settlement Charges'];
        }

        $this->createAndPostJournal("Karigar Settlement: {$settlement->settlement_number}", $items, $settlement->branch_id, 'KarigarSettlement', $settlement->id);
    }

    /**
     * Automated Expense Posting
     */
    public function postExpense(\App\Models\Expense $expense): void
    {
        $items = [
            ['key' => 'cash_in_hand', 'debit' => 0, 'credit' => $expense->amount, 'desc' => "Expense: {$expense->description}"],
            ['key' => 'expenses_operating', 'debit' => $expense->amount, 'credit' => 0, 'desc' => 'Expense Category: '.($expense->category->name ?? 'General')],
        ];

        $this->createAndPostJournal("Expense: {$expense->id}", $items, $expense->branch_id, 'Expense', $expense->id);
    }

    /**
     * Automated Petty Cash Posting
     */
    public function postPettyCashEntry(\App\Models\PettyCashEntry $entry): void
    {
        $branchId = $entry->pettyCash->branch_id;

        if ($entry->type === 'cash_in') {
            // Replenishing petty cash from main cash
            $items = [
                ['key' => 'cash_in_hand', 'debit' => 0, 'credit' => $entry->amount, 'desc' => 'Petty Cash Replenishment'],
                ['key' => 'cash_petty', 'debit' => $entry->amount, 'credit' => 0, 'desc' => 'Petty Cash Replenishment'],
            ];
        } else {
            // Expense from petty cash
            $items = [
                ['key' => 'cash_petty', 'debit' => 0, 'credit' => $entry->amount, 'desc' => "Petty Cash Expense: {$entry->description}"],
                ['key' => 'expenses_operating', 'debit' => $entry->amount, 'credit' => 0, 'desc' => "Petty Cash Category: {$entry->category}"],
            ];
        }

        $this->createAndPostJournal("Petty Cash Entry: {$entry->id}", $items, $branchId, 'PettyCashEntry', $entry->id);
    }

    private function createAndPostJournal(string $narration, array $mappings, ?int $branchId, ?string $refType = null, ?int $refId = null): JournalEntry
    {
        $entry = JournalEntry::create([
            'reference_number' => strtoupper(substr($refType ?? 'JE', 0, 3)).'-'.date('YmdHis').'-'.rand(10, 99),
            'branch_id' => $branchId,
            'entry_date' => now()->toDateString(),
            'narration' => $narration,
            'status' => 'posted', // Auto-posted
            'created_by' => auth()->id() ?? 1,
        ]);

        foreach ($mappings as $map) {
            $account = $this->getAccountByMapping($map['key']);

            JournalEntryItem::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $account->id,
                'debit' => $map['debit'],
                'credit' => $map['credit'],
                'description' => $map['desc'],
            ]);

            $this->postEntry(
                $account,
                $map['debit'] > 0 ? 'debit' : 'credit',
                max($map['debit'], $map['credit']),
                $map['desc'],
                $entry->id,
                $branchId
            );
        }

        return $entry;
    }

    public function createJournalEntry(string $narration, array $items): JournalEntry
    {
        $entry = JournalEntry::create([
            'reference_number' => 'JE-'.date('YmdHis'),
            'entry_date' => now()->toDateString(),
            'narration' => $narration,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        foreach ($items as $item) {
            JournalEntryItem::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $item['account_id'],
                'debit' => $item['debit'] ?? 0,
                'credit' => $item['credit'] ?? 0,
                'description' => $item['description'] ?? null,
            ]);
        }

        return $entry;
    }

    public function postJournalEntry(JournalEntry $entry): void
    {
        if (! $entry->isBalanced()) {
            throw new \Exception('Journal entry is not balanced. Total debits must equal total credits.');
        }

        foreach ($entry->items as $item) {
            $this->postEntry(
                $item->account,
                $item->debit > 0 ? 'debit' : 'credit',
                max($item->debit, $item->credit),
                "Journal Entry: {$entry->reference_number}",
                $entry->id
            );
        }

        $entry->update(['status' => 'posted']);
    }

    public function getAccountBalance(ChartOfAccount $account, ?string $asDate = null): float
    {
        $query = GeneralLedger::where('account_id', $account->id);

        if ($asDate) {
            $query->where('date', '<=', $asDate);
        }

        $debit = $query->sum('debit');
        $credit = $query->sum('credit');

        return $debit - $credit;
    }

    public function getTrialBalance(?string $asDate = null): array
    {
        $accounts = ChartOfAccount::where('is_active', true)->get();

        $balances = [];
        foreach ($accounts as $account) {
            $balance = $this->getAccountBalance($account, $asDate);
            if ($balance != 0) {
                $balances[] = [
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'account_type' => $account->account_type,
                    'balance' => $balance,
                    'is_debit' => $balance > 0,
                ];
            }
        }

        return $balances;
    }
}
