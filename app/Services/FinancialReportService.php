<?php

namespace App\Services;

use App\Models\Cashbook;
use App\Models\ChartOfAccount;
use App\Models\CustomerLedger;
use App\Models\Expense;
use App\Models\GeneralLedger;
use App\Models\Installment;
use App\Models\SupplierLedger;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    /**
     * Get Profit & Loss statement
     */
    public function getProfitAndLoss($fromDate, $toDate, $branchId = null)
    {
        try {
            // Revenue from GL
            $revenueAccounts = ChartOfAccount::where('account_type', 'Revenue')->pluck('id');
            $revenue = GeneralLedger::whereIn('account_id', $revenueAccounts)
                ->whereBetween('date', [$fromDate, $toDate])
                ->sum(DB::raw('credit - debit')); // Revenue usually has credit balance

            // COGS from GL
            $cogsAccounts = ChartOfAccount::where('account_category', 'COGS')->pluck('id');
            $cogs = GeneralLedger::whereIn('account_id', $cogsAccounts)
                ->whereBetween('date', [$fromDate, $toDate])
                ->sum(DB::raw('debit - credit'));

            // Gross Profit
            $grossProfit = $revenue - $cogs;

            // Other Operating Expenses
            $expenseAccounts = ChartOfAccount::where('account_type', 'Expense')
                ->whereNotIn('account_category', ['COGS'])
                ->pluck('id');

            $operatingExpenses = GeneralLedger::whereIn('account_id', $expenseAccounts)
                ->whereBetween('date', [$fromDate, $toDate])
                ->sum(DB::raw('debit - credit'));

            // Net Profit
            $netProfit = $grossProfit - $operatingExpenses;

            return [
                'revenue' => $revenue ?? 0,
                'cogs' => $cogs ?? 0,
                'gross_profit' => $grossProfit ?? 0,
                'gross_profit_margin' => $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0,
                'operating_expenses' => $operatingExpenses ?? 0,
                'net_profit' => $netProfit ?? 0,
                'net_profit_margin' => $revenue > 0 ? ($netProfit / $revenue) * 100 : 0,
            ];
        } catch (\Exception $e) {
            return [
                'revenue' => 0,
                'cogs' => 0,
                'gross_profit' => 0,
                'gross_profit_margin' => 0,
                'operating_expenses' => 0,
                'net_profit' => 0,
                'net_profit_margin' => 0,
            ];
        }
    }

    /**
     * Get Balance Sheet
     */
    public function getBalanceSheet($asOfDate, $branchId = null)
    {
        $asOfDate = Carbon::parse($asOfDate)->endOfDay();

        // Assets
        $assets = $this->getAccountBalanceByType('asset', $asOfDate, $branchId);
        $totalAssets = collect($assets)->sum('balance');

        // Liabilities
        $liabilities = $this->getAccountBalanceByType('liability', $asOfDate, $branchId);
        $totalLiabilities = collect($liabilities)->sum('balance');

        // Equity
        $equity = $this->getAccountBalanceByType('equity', $asOfDate, $branchId);
        $totalEquity = collect($equity)->sum('balance');

        return [
            'assets' => $assets,
            'total_assets' => $totalAssets,
            'liabilities' => $liabilities,
            'total_liabilities' => $totalLiabilities,
            'equity' => $equity,
            'total_equity' => $totalEquity,
            'total_liabilities_and_equity' => $totalLiabilities + $totalEquity,
            'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
        ];
    }

    /**
     * Get Trial Balance
     */
    public function getTrialBalance($asOfDate, $branchId = null)
    {
        $asOfDate = Carbon::parse($asOfDate)->endOfDay();

        $accounts = ChartOfAccount::where('is_active', true)->get();

        $trialBalance = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            $gl = GeneralLedger::where('account_id', $account->id)
                ->whereDate('date', '<=', $asOfDate)
                ->when($branchId, fn ($q) => $q->whereHas('journalEntry', fn ($j) => $j->where('branch_id', $branchId)))
                ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                ->first();

            if ($gl && ($gl->total_debit > 0 || $gl->total_credit > 0)) {
                $trialBalance[] = [
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'debit' => $gl->total_debit ?? 0,
                    'credit' => $gl->total_credit ?? 0,
                ];

                $totalDebit += $gl->total_debit ?? 0;
                $totalCredit += $gl->total_credit ?? 0;
            }
        }

        return [
            'accounts' => $trialBalance,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
        ];
    }

    /**
     * Get Cashbook Report
     */
    public function getCashbookReport($fromDate, $toDate, $branchId = null)
    {
        $query = Cashbook::query()
            ->whereBetween('date', [$fromDate, $toDate])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with(['branch', 'user'])
            ->orderBy('date', 'asc');

        $entries = $query->get();

        $summary = [
            'total_cash_in' => $entries->where('entry_type', 'cash_in')->sum('amount'),
            'total_cash_out' => $entries->where('entry_type', 'cash_out')->sum('amount'),
            'net_balance' => 0,
        ];

        $summary['net_balance'] = $summary['total_cash_in'] - $summary['total_cash_out'];

        return [
            'entries' => $entries,
            'summary' => $summary,
        ];
    }

    /**
     * Get Customer Ledger Report
     */
    public function getCustomerLedgerReport($fromDate, $toDate, $branchId = null)
    {
        $query = CustomerLedger::query()->with(['customer', 'entries']);

        $customers = $query->get();

        $report = [];
        $totalOutstanding = 0;

        foreach ($customers as $ledger) {
            $entries = $ledger->entries()
                ->whereBetween('date', [$fromDate, $toDate])
                ->get();

            if ($entries->count() > 0) {
                $debit = $entries->sum('debit');
                $credit = $entries->sum('credit');
                $balance = $ledger->current_balance;

                $report[] = [
                    'customer_id' => $ledger->customer_id,
                    'customer_name' => $ledger->customer->name,
                    'opening_balance' => $ledger->opening_balance,
                    'debit' => $debit,
                    'credit' => $credit,
                    'closing_balance' => $balance,
                ];

                $totalOutstanding += $balance > 0 ? $balance : 0;
            }
        }

        return [
            'report' => $report,
            'total_outstanding' => $totalOutstanding,
        ];
    }

    /**
     * Get Supplier Ledger Report
     */
    public function getSupplierLedgerReport($fromDate, $toDate, $branchId = null)
    {
        $query = SupplierLedger::query()->with(['supplier', 'entries']);

        $suppliers = $query->get();

        $report = [];
        $totalPayable = 0;

        foreach ($suppliers as $ledger) {
            $entries = $ledger->entries()
                ->whereBetween('date', [$fromDate, $toDate])
                ->get();

            if ($entries->count() > 0) {
                $debit = $entries->sum('debit');
                $credit = $entries->sum('credit');
                $balance = $ledger->current_balance;

                $report[] = [
                    'supplier_id' => $ledger->supplier_id,
                    'supplier_name' => $ledger->supplier->name,
                    'opening_balance' => $ledger->opening_balance,
                    'debit' => $debit,
                    'credit' => $credit,
                    'closing_balance' => $balance,
                ];

                $totalPayable += $balance > 0 ? $balance : 0;
            }
        }

        return [
            'report' => $report,
            'total_payable' => $totalPayable,
        ];
    }

    /**
     * Get Expense Report
     */
    public function getExpenseReport($fromDate, $toDate, $branchId = null)
    {
        $query = Expense::query()
            ->whereBetween('date', [$fromDate, $toDate])
            ->where('status', 'approved')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with(['category', 'branch']);

        $expenses = $query->get();

        $byCategory = $expenses->groupBy('category.name')
            ->map(fn ($items) => [
                'total' => $items->sum('amount'),
                'count' => $items->count(),
                'items' => $items,
            ]);

        return [
            'total_expenses' => $expenses->sum('amount'),
            'by_category' => $byCategory,
            'expenses' => $expenses,
        ];
    }

    /**
     * Get Installment Report
     */
    public function getInstallmentReport($branchId = null)
    {
        $query = Installment::query()
            ->with(['customer', 'sale', 'schedules']);

        $installments = $query->get();

        $activeCount = $installments->where('status', 'active')->count();
        $completedCount = $installments->where('status', 'completed')->count();
        $totalAmount = $installments->sum('total_amount');
        $paidAmount = $installments->sum('paid_amount');
        $outstandingAmount = $installments->sum('outstanding_amount');

        // Get overdue
        $today = now()->toDateString();
        $overdue = $installments->filter(function ($inst) use ($today) {
            return $inst->schedules->where('due_date', '<', $today)->where('status', '!=', 'paid')->count() > 0;
        })->count();

        return [
            'total_installments' => $installments->count(),
            'active_count' => $activeCount,
            'completed_count' => $completedCount,
            'overdue_count' => $overdue,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'outstanding_amount' => $outstandingAmount,
            'collection_rate' => $totalAmount > 0 ? ($paidAmount / $totalAmount) * 100 : 0,
            'installments' => $installments,
        ];
    }

    /**
     * Get outstanding summary
     */
    public function getOutstandingSummary($branchId = null)
    {
        $customerOutstanding = CustomerLedger::query()
            ->when($branchId, fn ($q) => $q->whereHas('customer', fn ($c) => $c->where('branch_id', $branchId)))
            ->where('status', 'active')
            ->sum('current_balance');

        $supplierOutstanding = SupplierLedger::query()
            ->when($branchId, fn ($q) => $q->whereHas('supplier', fn ($s) => $s->where('branch_id', $branchId)))
            ->where('status', 'active')
            ->sum('current_balance');

        $installmentOutstanding = Installment::query()
            ->where('status', '!=', 'completed')
            ->sum('outstanding_amount');

        return [
            'customer_receivable' => $customerOutstanding,
            'supplier_payable' => $supplierOutstanding,
            'installment_outstanding' => $installmentOutstanding,
            'net_position' => $customerOutstanding - $supplierOutstanding,
        ];
    }

    /**
     * Helper: Get account balance by type
     */
    private function getAccountBalanceByType($type, $asOfDate, $branchId = null)
    {
        try {
            $accounts = ChartOfAccount::where('account_type', $type)
                ->where('is_active', true)
                ->get();

            $result = [];

            foreach ($accounts as $account) {
                $gl = GeneralLedger::where('account_id', $account->id)
                    ->whereDate('date', '<=', $asOfDate)
                    ->when($branchId, fn ($q) => $q->whereHas('journalEntry', fn ($j) => $j->where('branch_id', $branchId)))
                    ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                    ->first();

                $totalDebit = $gl?->total_debit ?? 0;
                $totalCredit = $gl?->total_credit ?? 0;

                // Normal balance direction
                if (in_array(strtolower($type), ['asset', 'expense'])) {
                    $balance = $totalDebit - $totalCredit;
                } else {
                    $balance = $totalCredit - $totalDebit;
                }

                if ($balance != 0) {
                    $result[] = [
                        'code' => $account->account_code,
                        'name' => $account->account_name,
                        'balance' => $balance,
                    ];
                }
            }

            return $result;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Error calculating account balance for type {$type}: ".$e->getMessage());

            return [];
        }
    }
}
