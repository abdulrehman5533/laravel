<?php

namespace App\Services;

use App\Models\Cashbook;
use App\Models\CustomerLedger;
use App\Models\GeneralLedger;
use App\Models\Installment;
use App\Models\InstallmentSchedule;
use App\Models\Sale;
use App\Models\SupplierLedger;

class AccountsService
{
    /**
     * Create customer ledger entry from sale
     */
    public function createCustomerLedgerEntry($sale)
    {
        $customer = $sale->customer;

        // Get or create customer ledger
        $ledger = CustomerLedger::firstOrCreate(
            ['customer_id' => $customer->id],
            ['opening_balance' => 0, 'status' => 'active']
        );

        // Create debit entry for sale
        $ledger->entries()->create([
            'date' => $sale->date,
            'reference_type' => 'Sale',
            'reference_id' => $sale->id,
            'description' => 'Sale Invoice #'.$sale->invoice_number,
            'debit' => $sale->total_amount,
            'credit' => 0,
            'type' => 'debit',
        ]);

        // Update ledger balance
        $ledger->updateBalance();
    }

    /**
     * Record customer payment in ledger
     */
    public function recordCustomerPayment($paymentData)
    {
        $ledger = CustomerLedger::findOrFail($paymentData['customer_ledger_id']);

        // Create credit entry for payment
        $ledger->entries()->create([
            'date' => $paymentData['payment_date'],
            'reference_type' => 'Payment',
            'reference_id' => $paymentData['payment_id'],
            'description' => 'Payment Received - '.$paymentData['payment_method'],
            'debit' => 0,
            'credit' => $paymentData['amount'],
            'type' => 'credit',
        ]);

        // Create cashbook entry
        Cashbook::create([
            'branch_id' => $paymentData['branch_id'],
            'user_id' => auth()->id(),
            'date' => $paymentData['payment_date'],
            'entry_type' => 'cash_in',
            'category' => 'Customer Payment',
            'amount' => $paymentData['amount'],
            'payment_method' => $paymentData['payment_method'],
            'reference_type' => 'CustomerPayment',
            'reference_id' => $paymentData['payment_id'],
            'description' => 'Payment from Customer: '.$paymentData['customer_name'],
            'status' => 'verified',
        ]);

        // Update ledger balance
        $ledger->updateBalance();
    }

    /**
     * Create supplier ledger entry from purchase
     */
    public function createSupplierLedgerEntry($purchaseOrder)
    {
        $supplier = $purchaseOrder->supplier;

        // Get or create supplier ledger
        $ledger = SupplierLedger::firstOrCreate(
            ['supplier_id' => $supplier->id],
            ['opening_balance' => 0, 'status' => 'active']
        );

        // Create credit entry for purchase (liability)
        $ledger->entries()->create([
            'date' => $purchaseOrder->date,
            'reference_type' => 'PurchaseOrder',
            'reference_id' => $purchaseOrder->id,
            'description' => 'Purchase Order #'.$purchaseOrder->po_number,
            'debit' => 0,
            'credit' => $purchaseOrder->total_amount,
            'type' => 'credit',
        ]);

        // Update ledger balance
        $ledger->updateBalance();
    }

    /**
     * Record supplier payment
     */
    public function recordSupplierPayment($paymentData)
    {
        $ledger = SupplierLedger::findOrFail($paymentData['supplier_ledger_id']);

        // Create debit entry for payment
        $ledger->entries()->create([
            'date' => $paymentData['payment_date'],
            'reference_type' => 'Payment',
            'reference_id' => $paymentData['payment_id'],
            'description' => 'Payment Made - '.$paymentData['payment_method'],
            'debit' => $paymentData['amount'],
            'credit' => 0,
            'type' => 'debit',
        ]);

        // Create cashbook entry
        Cashbook::create([
            'branch_id' => $paymentData['branch_id'],
            'user_id' => auth()->id(),
            'date' => $paymentData['payment_date'],
            'entry_type' => 'cash_out',
            'category' => 'Supplier Payment',
            'amount' => $paymentData['amount'],
            'payment_method' => $paymentData['payment_method'],
            'reference_type' => 'SupplierPayment',
            'reference_id' => $paymentData['payment_id'],
            'description' => 'Payment to Supplier: '.$paymentData['supplier_name'],
            'status' => 'verified',
        ]);

        // Update ledger balance
        $ledger->updateBalance();
    }

    /**
     * Create installment schedule from sale
     */
    public function createInstallmentSchedule($sale, $installmentPlanData)
    {
        $installment = Installment::create([
            'sale_id' => $sale->id,
            'customer_id' => $sale->customer_id,
            'total_amount' => $sale->total_amount,
            'paid_amount' => $installmentPlanData['down_payment'] ?? 0,
            'outstanding_amount' => $sale->total_amount - ($installmentPlanData['down_payment'] ?? 0),
            'no_of_installments' => $installmentPlanData['no_of_installments'],
            'frequency' => $installmentPlanData['frequency'],
            'start_date' => now(),
            'status' => 'active',
        ]);

        // Generate schedule
        $remainingAmount = $installment->outstanding_amount;
        $emiAmount = $remainingAmount / $installmentPlanData['no_of_installments'];
        $currentDate = now();

        for ($i = 1; $i <= $installmentPlanData['no_of_installments']; $i++) {
            $dueDate = $currentDate->copy()->add($i, 'months');

            InstallmentSchedule::create([
                'installment_id' => $installment->id,
                'schedule_number' => $i,
                'due_date' => $dueDate,
                'amount' => $emiAmount,
                'paid_amount' => 0,
                'status' => 'pending',
            ]);
        }

        return $installment;
    }

    /**
     * Record installment payment
     */
    public function recordInstallmentPayment($schedule, $paymentData)
    {
        $schedule->update([
            'paid_amount' => $paymentData['amount'],
            'payment_date' => now(),
            'status' => $paymentData['amount'] >= $schedule->amount ? 'paid' : 'partial',
        ]);

        // Update installment
        $installment = $schedule->installment;
        $totalPaid = $installment->schedules()->sum('paid_amount') + $paymentData['amount'];
        $installment->update([
            'paid_amount' => $totalPaid,
            'outstanding_amount' => $installment->total_amount - $totalPaid,
            'status' => $totalPaid >= $installment->total_amount ? 'completed' : 'active',
        ]);

        // Create cashbook entry
        Cashbook::create([
            'branch_id' => $paymentData['branch_id'],
            'user_id' => auth()->id(),
            'date' => now(),
            'entry_type' => 'cash_in',
            'category' => 'Installment Payment',
            'amount' => $paymentData['amount'],
            'payment_method' => $paymentData['payment_method'],
            'reference_type' => 'InstallmentSchedule',
            'reference_id' => $schedule->id,
            'description' => "Installment #{$schedule->schedule_number} from Customer",
            'status' => 'verified',
        ]);

        // Update customer ledger
        $ledger = $installment->customer->ledger;
        if ($ledger) {
            $ledger->entries()->create([
                'date' => now(),
                'reference_type' => 'InstallmentPayment',
                'reference_id' => $schedule->id,
                'description' => "Installment Payment #{$schedule->schedule_number}",
                'debit' => 0,
                'credit' => $paymentData['amount'],
                'type' => 'credit',
            ]);
            $ledger->updateBalance();
        }
    }

    /**
     * Get aging analysis for customer ledger
     */
    public function getCustomerAging($customerId)
    {
        $ledger = CustomerLedger::where('customer_id', $customerId)->first();

        if (! $ledger) {
            return null;
        }

        $entries = $ledger->entries()->where('type', 'debit')->get();
        $current = 0;
        $days30 = 0;
        $days60 = 0;
        $days90 = 0;
        $days90plus = 0;

        $today = now();

        foreach ($entries as $entry) {
            $daysDiff = $entry->date->diffInDays($today);
            $amount = $entry->debit - $entry->credit; // Net amount

            if ($daysDiff <= 30) {
                $current += $amount;
            } elseif ($daysDiff <= 60) {
                $days30 += $amount;
            } elseif ($daysDiff <= 90) {
                $days60 += $amount;
            } elseif ($daysDiff <= 180) {
                $days90 += $amount;
            } else {
                $days90plus += $amount;
            }
        }

        return [
            'current' => $current,
            'days_30' => $days30,
            'days_60' => $days60,
            'days_90' => $days90,
            'days_90_plus' => $days90plus,
            'total' => $current + $days30 + $days60 + $days90 + $days90plus,
        ];
    }

    /**
     * Get aggregated customer aging summary across all customers (for dashboard)
     */
    public function getCustomerAgingSummary($branchId = null)
    {
        $today = now();

        $query = CustomerLedger::query()->where('status', 'active');
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $ledgers = $query->with(['entries' => function ($q) {
            $q->where('type', 'debit');
        }])->get();

        $summary = [
            'current' => 0,
            'days_30' => 0,
            'days_60' => 0,
            'days_90' => 0,
            'days_90_plus' => 0,
            'total' => 0,
        ];

        foreach ($ledgers as $ledger) {
            foreach ($ledger->entries as $entry) {
                $daysDiff = $entry->date->diffInDays($today);
                $amount = ($entry->debit ?? 0) - ($entry->credit ?? 0);

                if ($daysDiff <= 30) {
                    $summary['current'] += $amount;
                } elseif ($daysDiff <= 60) {
                    $summary['days_30'] += $amount;
                } elseif ($daysDiff <= 90) {
                    $summary['days_60'] += $amount;
                } elseif ($daysDiff <= 180) {
                    $summary['days_90'] += $amount;
                } else {
                    $summary['days_90_plus'] += $amount;
                }

                $summary['total'] += $amount;
            }
        }

        return $summary;
    }

    /**
     * Get supplier aging analysis
     */
    public function getSupplierAging($supplierId)
    {
        $ledger = SupplierLedger::where('supplier_id', $supplierId)->first();

        if (! $ledger) {
            return null;
        }

        $entries = $ledger->entries()->where('type', 'credit')->get();
        $current = 0;
        $days30 = 0;
        $days60 = 0;
        $days90 = 0;
        $days90plus = 0;

        $today = now();

        foreach ($entries as $entry) {
            $daysDiff = $entry->date->diffInDays($today);
            $amount = $entry->credit - $entry->debit; // Net amount

            if ($daysDiff <= 30) {
                $current += $amount;
            } elseif ($daysDiff <= 60) {
                $days30 += $amount;
            } elseif ($daysDiff <= 90) {
                $days60 += $amount;
            } elseif ($daysDiff <= 180) {
                $days90 += $amount;
            } else {
                $days90plus += $amount;
            }
        }

        return [
            'current' => $current,
            'days_30' => $days30,
            'days_60' => $days60,
            'days_90' => $days90,
            'days_90_plus' => $days90plus,
            'total' => $current + $days30 + $days60 + $days90 + $days90plus,
        ];
    }

    /**
     * Get overdue installments
     */
    public function getOverdueInstallments()
    {
        $today = now()->toDateString();

        return InstallmentSchedule::where('due_date', '<', $today)
            ->where('status', '!=', 'paid')
            ->with(['installment.customer', 'installment.sale'])
            ->get();
    }

    /**
     * Calculate daily cash position
     */
    public function getDailyCashPosition($branchId, $date)
    {
        $cashIn = Cashbook::where('branch_id', $branchId)
            ->whereDate('date', $date)
            ->where('entry_type', 'cash_in')
            ->sum('amount');

        $cashOut = Cashbook::where('branch_id', $branchId)
            ->whereDate('date', $date)
            ->where('entry_type', 'cash_out')
            ->sum('amount');

        return [
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'net_position' => $cashIn - $cashOut,
        ];
    }

    /**
     * Get expense summary by category
     */
    public function getExpenseSummary($fromDate, $toDate, $branchId = null)
    {
        $query = \App\Models\Expense::query()
            ->whereBetween('date', [$fromDate, $toDate])
            ->where('status', 'approved');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->groupBy('category_id')
            ->with('category')
            ->selectRaw('category_id, SUM(amount) as total, COUNT(*) as count')
            ->get();
    }

    /**
     * Post journal entry to general ledger
     */
    public function postJournalEntryToGL($journalEntry)
    {
        foreach ($journalEntry->items as $item) {
            GeneralLedger::create([
                'chart_of_account_id' => $item->chart_of_account_id,
                'journal_entry_id' => $journalEntry->id,
                'date' => $journalEntry->date,
                'debit' => $item->debit,
                'credit' => $item->credit,
                'description' => $journalEntry->description,
                'reference' => $journalEntry->reference,
            ]);
        }
    }

    /**
     * Get account balance
     */
    public function getAccountBalance($chartOfAccountId, $toDate = null)
    {
        $query = GeneralLedger::where('chart_of_account_id', $chartOfAccountId);

        if ($toDate) {
            $query->whereDate('date', '<=', $toDate);
        }

        $debit = $query->sum('debit');
        $credit = $query->sum('credit');

        return [
            'debit' => $debit,
            'credit' => $credit,
            'balance' => $debit - $credit,
        ];
    }
}
