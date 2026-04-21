<?php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;

class AccountingReportService
{
    /**
     * Generate Trial Balance
     */
    public function getTrialBalance(string $asOfDate): array
    {
        return ChartOfAccount::active()
            ->with(['ledgerEntries' => function ($query) use ($asOfDate) {
                $query->where('date', '<=', $asOfDate);
            }])
            ->get()
            ->map(function ($account) {
                $debit = $account->ledgerEntries->sum('debit');
                $credit = $account->ledgerEntries->sum('credit');
                $balance = $debit - $credit;

                return [
                    'code' => $account->account_code,
                    'name' => $account->account_name,
                    'type' => $account->account_type,
                    'debit' => $balance > 0 ? $balance : 0,
                    'credit' => $balance < 0 ? abs($balance) : 0,
                ];
            })
            ->filter(fn ($a) => $a['debit'] > 0 || $a['credit'] > 0)
            ->values()
            ->toArray();
    }

    /**
     * Generate Profit & Loss Statement
     */
    public function getProfitAndLoss(string $startDate, string $endDate): array
    {
        $accounts = ChartOfAccount::whereIn('account_type', ['Revenue', 'Expense'])
            ->with(['ledgerEntries' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            }])
            ->get();

        $revenue = $accounts->where('account_type', 'Revenue')->map(function ($acc) {
            return [
                'name' => $acc->account_name,
                'amount' => $acc->ledgerEntries->sum('credit') - $acc->ledgerEntries->sum('debit'),
            ];
        });

        $expenses = $accounts->where('account_type', 'Expense')->map(function ($acc) {
            return [
                'name' => $acc->account_name,
                'amount' => $acc->ledgerEntries->sum('debit') - $acc->ledgerEntries->sum('credit'),
            ];
        });

        $totalRevenue = $revenue->sum('amount');
        $totalExpenses = $expenses->sum('amount');

        return [
            'revenue' => $revenue->values()->toArray(),
            'expenses' => $expenses->values()->toArray(),
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_profit' => $totalRevenue - $totalExpenses,
        ];
    }

    /**
     * Generate Balance Sheet
     */
    public function getBalanceSheet(string $asOfDate): array
    {
        $accounts = ChartOfAccount::whereIn('account_type', ['Asset', 'Liability', 'Equity'])
            ->with(['ledgerEntries' => function ($query) use ($asOfDate) {
                $query->where('date', '<=', $asOfDate);
            }])
            ->get();

        $assets = $accounts->where('account_type', 'Asset')->map(function ($acc) {
            return [
                'name' => $acc->account_name,
                'amount' => $acc->ledgerEntries->sum('debit') - $acc->ledgerEntries->sum('credit'),
            ];
        });

        $liabilities = $accounts->where('account_type', 'Liability')->map(function ($acc) {
            return [
                'name' => $acc->account_name,
                'amount' => $acc->ledgerEntries->sum('credit') - $acc->ledgerEntries->sum('debit'),
            ];
        });

        $equity = $accounts->where('account_type', 'Equity')->map(function ($acc) {
            return [
                'name' => $acc->account_name,
                'amount' => $acc->ledgerEntries->sum('credit') - $acc->ledgerEntries->sum('debit'),
            ];
        })->values();

        // Net Profit up to this date needs to be added to Equity
        $pl = $this->getProfitAndLoss('1970-01-01', $asOfDate);
        $equity->push(['name' => 'Current Period Profit/Loss', 'amount' => $pl['net_profit']]);

        return [
            'assets' => $assets->values()->toArray(),
            'liabilities' => $liabilities->values()->toArray(),
            'equity' => $equity->toArray(),
            'total_assets' => $assets->sum('amount'),
            'total_liabilities_equity' => $liabilities->sum('amount') + $equity->sum('amount'),
        ];
    }
}
