<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\GeneralLedger;
use App\Models\JournalEntry;
use App\Services\Accounting\AccountingReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountingController extends Controller
{
    public function __construct(private AccountingReportService $reportService) {}

    /**
     * Accounting Dashboard
     */
    public function index(): View
    {
        $today = now()->toDateString();
        $pl = $this->reportService->getProfitAndLoss(now()->startOfMonth()->toDateString(), $today);
        $bs = $this->reportService->getBalanceSheet($today);

        $accounts = ChartOfAccount::active()->limit(10)->get();

        return view('accounting.index', compact('pl', 'bs', 'accounts'));
    }

    /**
     * Chart of Accounts
     */
    public function chartOfAccounts(): View
    {
        $accounts = ChartOfAccount::with('parent')
            ->orderBy('account_code')
            ->get();

        return view('accounting.chart-of-accounts', compact('accounts'));
    }

    /**
     * General Ledger View
     */
    public function ledger(Request $request): View
    {
        $accountId = $request->get('account_id');
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $query = GeneralLedger::with('account')
            ->whereBetween('date', [$startDate, $endDate]);

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        $entries = $query->orderBy('date')->orderBy('id')->get();
        $accounts = ChartOfAccount::active()->orderBy('account_name')->get();

        return view('accounting.ledger', compact('entries', 'accounts', 'accountId', 'startDate', 'endDate'));
    }

    /**
     * Trial Balance Report
     */
    public function trialBalance(Request $request): View
    {
        $date = $request->get('date', now()->toDateString());
        $data = $this->reportService->getTrialBalance($date);

        return view('accounting.reports.trial-balance', compact('data', 'date'));
    }

    /**
     * Profit & Loss Report
     */
    public function profitLoss(Request $request): View
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());
        $data = $this->reportService->getProfitAndLoss($startDate, $endDate);

        return view('accounting.reports.profit-loss', compact('data', 'startDate', 'endDate'));
    }

    /**
     * Balance Sheet Report
     */
    public function balanceSheet(Request $request): View
    {
        $date = $request->get('date', now()->toDateString());
        $data = $this->reportService->getBalanceSheet($date);

        return view('accounting.reports.balance-sheet', compact('data', 'date'));
    }

    /**
     * Journal Entries List
     */
    public function journals(): View
    {
        $journals = JournalEntry::with(['items.account', 'createdBy'])
            ->latest('entry_date')
            ->paginate(20);

        return view('accounting.journals.index', compact('journals'));
    }
}
