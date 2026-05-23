<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FinancialReportsController extends Controller
{
    /**
     * Generate Trial Balance
     */
    public function trialBalance(Request $request)
    {
        $data = $this->getTrialBalanceData($request);

        return view('accounts.financial-reports.trial-balance', $data);
    }

    /**
     * Print Trial Balance
     */
    public function printTrialBalance(Request $request)
    {
        $data = $this->getTrialBalanceData($request);

        return view('accounts.financial-reports.trial-balance-print', $data);
    }

    /**
     * Generate Profit & Loss Statement
     */
    public function profitLoss(Request $request)
    {
        $data = $this->getProfitLossData($request);

        return view('accounts.financial-reports.profit-loss', $data);
    }

    /**
     * Print Profit & Loss
     */
    public function printProfitLoss(Request $request)
    {
        $data = $this->getProfitLossData($request);

        return view('accounts.financial-reports.profit-loss-print', $data);
    }

    /**
     * Generate Balance Sheet
     */
    public function balanceSheet(Request $request)
    {
        $data = $this->getBalanceSheetData($request);

        return view('accounts.financial-reports.balance-sheet', $data);
    }

    /**
     * Print Balance Sheet
     */
    public function printBalanceSheet(Request $request)
    {
        $data = $this->getBalanceSheetData($request);

        return view('accounts.financial-reports.balance-sheet-print', $data);
    }

    /**
     * Export financial reports to PDF
     */
    public function exportPdf(Request $request)
    {
        $report = $request->get('report', 'trial-balance');

        if ($report === 'trial-balance') {
            $data = $this->getTrialBalanceData($request);
            $view = 'accounts.financial-reports.trial-balance-pdf';
        } elseif ($report === 'profit-loss') {
            $data = $this->getProfitLossData($request);
            $view = 'accounts.financial-reports.profit-loss-pdf';
        } elseif ($report === 'balance-sheet') {
            $data = $this->getBalanceSheetData($request);
            $view = 'accounts.financial-reports.balance-sheet-pdf';
        } else {
            abort(404, 'Report type not found');
        }

        $pdf = Pdf::loadView($view, $data);

        return $pdf->download($report.'-'.now()->format('Y-m-d').'.pdf');
    }

    /**
     * Export financial reports to Excel
     */
    public function exportExcel(Request $request)
    {
        $report = $request->get('report', 'trial-balance');

        if ($report === 'trial-balance') {
            return Excel::download(
                new \App\Exports\TrialBalanceExport($request),
                'trial-balance-'.now()->format('Y-m-d').'.xlsx'
            );
        } elseif ($report === 'profit-loss') {
            return Excel::download(
                new \App\Exports\ProfitLossExport($request),
                'profit-loss-'.now()->format('Y-m-d').'.xlsx'
            );
        } elseif ($report === 'balance-sheet') {
            return Excel::download(
                new \App\Exports\BalanceSheetExport($request),
                'balance-sheet-'.now()->format('Y-m-d').'.xlsx'
            );
        } else {
            abort(404, 'Report type not found');
        }
    }

    private function getTrialBalanceData(Request $request)
    {
        $asAtDate = $request->get('as_at_date') ? Carbon::parse($request->get('as_at_date')) : Carbon::now();

        $accounts = ChartOfAccount::active()->with(['ledgerEntries' => function ($query) use ($asAtDate) {
            $query->where('created_at', '<=', $asAtDate);
        }])->orderBy('account_code')->get();

        $totalDebits = 0;
        $totalCredits = 0;
        $balances = [];

        foreach ($accounts as $account) {
            $debitSum = $account->ledgerEntries->where('type', 'DEBIT')->sum('amount');
            $creditSum = $account->ledgerEntries->where('type', 'CREDIT')->sum('amount');

            $type     = $account->account_type;
            $category = strtoupper($account->account_category ?? '');

            if (in_array($type, ['Asset', 'Expense'])) {
                $balance = $account->opening_balance + $debitSum - $creditSum;
                $debit = $balance > 0 ? $balance : 0;
                $credit = $balance < 0 ? abs($balance) : 0;
            } else {
                $balance = $account->opening_balance + $creditSum - $debitSum;
                $credit = $balance > 0 ? $balance : 0;
                $debit = $balance < 0 ? abs($balance) : 0;
            }

            if ($debit != 0 || $credit != 0) {
                $balances[] = [
                    'account' => $account,
                    'debit' => $debit,
                    'credit' => $credit,
                ];

                $totalDebits += $debit;
                $totalCredits += $credit;
            }
        }

        return [
            'trialBalance' => $balances,
            'totalDebits' => $totalDebits,
            'totalCredits' => $totalCredits,
            'asOfDate' => $asAtDate,
        ];
    }

    private function getProfitLossData(Request $request)
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::now()->startOfYear();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::now();

        $accounts = ChartOfAccount::active()
            ->withSum(['ledgerEntries as debits' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])->where('type', 'DEBIT');
            }], 'amount')
            ->withSum(['ledgerEntries as credits' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])->where('type', 'CREDIT');
            }], 'amount')
            ->get();

        $revenues = [];
        $totalRevenue = 0;

        $costOfSales = [];
        $totalCogs = 0;

        $operatingExpenses = [];
        $totalOperatingExpenses = 0;

        $otherIncomeExpense = [];
        $totalOther = 0;

        foreach ($accounts as $account) {
            $debits = $account->debits ?? 0;
            $credits = $account->credits ?? 0;

            $balance = 0;
            $type     = $account->account_type;
            $category = strtoupper($account->account_category ?? '');

            if ($type === 'Income') {
                $balance = $credits - $debits;
                if ($balance != 0) {
                    if (in_array($category, ['SALES', 'SERVICE', 'MAKING CHARGES'])) {
                        $revenues[] = ['account_name' => $account->account_name, 'amount' => $balance];
                        $totalRevenue += $balance;
                    } else {
                        $otherIncomeExpense[] = ['account_name' => $account->account_name, 'amount' => $balance];
                        $totalOther += $balance;
                    }
                }
            } elseif ($type === 'Expense') {
                $balance = $debits - $credits;
                if ($balance != 0) {
                    if ($category === 'COGS') {
                        $costOfSales[] = ['account_name' => $account->account_name, 'amount' => $balance];
                        $totalCogs += $balance;
                    } else {
                        $operatingExpenses[] = ['account_name' => $account->account_name, 'amount' => $balance];
                        $totalOperatingExpenses += $balance;
                    }
                }
            }
        }

        $grossProfit = $totalRevenue - $totalCogs;
        $netProfit = $grossProfit - $totalOperatingExpenses + $totalOther;

        return [
            'revenues' => $revenues,
            'totalRevenue' => $totalRevenue,
            'costOfSales' => $costOfSales,
            'totalCogs' => $totalCogs,
            'grossProfit' => $grossProfit,
            'operatingExpenses' => $operatingExpenses,
            'totalOperatingExpenses' => $totalOperatingExpenses,
            'otherIncomeExpense' => $otherIncomeExpense,
            'totalOther' => $totalOther,
            'netProfit' => $netProfit,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    private function getBalanceSheetData(Request $request)
    {
        $asAtDate = $request->get('as_at_date') ? Carbon::parse($request->get('as_at_date')) : Carbon::now();

        $accounts = ChartOfAccount::active()
            ->withSum(['ledgerEntries as debits' => function ($query) use ($asAtDate) {
                $query->where('created_at', '<=', $asAtDate)->where('type', 'DEBIT');
            }], 'amount')
            ->withSum(['ledgerEntries as credits' => function ($query) use ($asAtDate) {
                $query->where('created_at', '<=', $asAtDate)->where('type', 'CREDIT');
            }], 'amount')
            ->get();

        // Assets
        $currentAssets = [];
        $fixedAssets = [];
        $otherAssets = [];
        $totalAssets = 0;
        $totalCurrentAssets = 0;
        $totalFixedAssets = 0;
        $totalOtherAssets = 0;

        // Liabilities
        $currentLiabilities = [];
        $longTermLiabilities = [];
        $totalLiabilities = 0;
        $totalCurrentLiabilities = 0;
        $totalLongTermLiabilities = 0;

        // Equity
        $equity = [];
        $totalEquity = 0;

        // Profit/Loss tracking
        $totalRev = 0;
        $totalExp = 0;

        foreach ($accounts as $account) {
            $debits = $account->debits ?? 0;
            $credits = $account->credits ?? 0;
            $type     = $account->account_type;
            $category = strtoupper($account->account_category ?? '');

            $balance = 0;
            if (in_array($type, ['Asset', 'Expense'])) {
                $balance = $account->opening_balance + $debits - $credits;
            } else {
                $balance = $account->opening_balance + $credits - $debits;
            }

            if ($type === 'Asset') {
                if ($balance != 0) {
                    $item = ['account_name' => $account->account_name, 'amount' => $balance];
                    if (in_array($category, ['CASH', 'BANK', 'RECEIVABLE', 'INVENTORY', 'TAX'])) {
                        $currentAssets[] = $item;
                        $totalCurrentAssets += $balance;
                    } elseif ($category === 'FIXED') {
                        $fixedAssets[] = $item;
                        $totalFixedAssets += $balance;
                    } else {
                        $otherAssets[] = $item;
                        $totalOtherAssets += $balance;
                    }
                    $totalAssets += $balance;
                }
            } elseif ($type === 'Liability') {
                if ($balance != 0) {
                    $item = ['account_name' => $account->account_name, 'amount' => $balance];
                    if (in_array($category, ['PAYABLE', 'TAX', 'CURRENT'])) {
                        $currentLiabilities[] = $item;
                        $totalCurrentLiabilities += $balance;
                    } else {
                        $longTermLiabilities[] = $item;
                        $totalLongTermLiabilities += $balance;
                    }
                    $totalLiabilities += $balance;
                }
            } elseif ($type === 'Equity') {
                if ($balance != 0) {
                    $equity[] = ['account_name' => $account->account_name, 'amount' => $balance];
                    $totalEquity += $balance;
                }
            } elseif ($type === 'Income') {
                $totalRev += $balance;
            } elseif ($type === 'Expense') {
                $totalExp += $balance;
            }
        }

        $currentNetProfit = $totalRev - $totalExp;
        $totalEquity += $currentNetProfit;

        if ($currentNetProfit != 0) {
            $equity[] = ['account_name' => 'Retained Earnings (Current Period)', 'amount' => $currentNetProfit];
        }

        $totalLiabilitiesEquity = $totalLiabilities + $totalEquity;

        return [
            'currentAssets' => $currentAssets,
            'totalCurrentAssets' => $totalCurrentAssets,
            'fixedAssets' => $fixedAssets,
            'totalFixedAssets' => $totalFixedAssets,
            'otherAssets' => $otherAssets,
            'totalOtherAssets' => $totalOtherAssets,
            'totalAssets' => $totalAssets,

            'currentLiabilities' => $currentLiabilities,
            'totalCurrentLiabilities' => $totalCurrentLiabilities,
            'longTermLiabilities' => $longTermLiabilities,
            'totalLongTermLiabilities' => $totalLongTermLiabilities,

            'equity' => $equity,
            'totalEquity' => $totalEquity,

            'totalLiabilitiesEquity' => $totalLiabilitiesEquity,
            'currentNetProfit' => $currentNetProfit,
            'asOfDate' => $asAtDate,
        ];
    }
}
