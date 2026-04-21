<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Cashbook;
use App\Models\ChartOfAccount;
use App\Models\CustomerLedger;
use App\Models\Expense;
use App\Models\GeneralLedger;
use App\Models\Installment;
use App\Models\SupplierLedger;
use App\Services\AccountsService;
use App\Services\FinancialReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountsReportController extends Controller
{
    protected $reportService;

    protected $accountsService;

    public function __construct(FinancialReportService $reportService, AccountsService $accountsService)
    {
        $this->reportService = $reportService;
        $this->accountsService = $accountsService;
    }

    public function dashboard(Request $request): View
    {
        $branchId = $request->query('branch_id');
        $fromDate = $request->query('from_date', now()->subMonth()->toDateString());
        $toDate = $request->query('to_date', now()->toDateString());

        // Daily cashbook summary
        $cashIn = Cashbook::where('entry_type', 'cash_in')
            ->whereBetween('date', [$fromDate, $toDate])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('amount');

        $cashOut = Cashbook::where('entry_type', 'cash_out')
            ->whereBetween('date', [$fromDate, $toDate])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('amount');

        // Expenses
        $expenses = Expense::whereBetween('date', [$fromDate, $toDate])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', 'approved')
            ->sum('amount');

        // Outstanding amounts
        $customerOutstanding = CustomerLedger::where('status', 'active')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('current_balance');

        $supplierOutstanding = SupplierLedger::where('status', 'active')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('current_balance');

        // Installment summary
        $activeInstallments = Installment::where('status', 'active')
            ->sum('outstanding_amount');

        // Outstanding summary
        $outstandingSummary = $this->reportService->getOutstandingSummary($branchId);

        // Get P&L for dashboard
        $profitLoss = $this->reportService->getProfitAndLoss($fromDate, $toDate, $branchId);

        $branches = Branch::where('is_active', true)->get();

        // Monthly cashflow for last 12 months
        $months = collect();
        $monthlyCashIn = collect();
        $monthlyCashOut = collect();
        for ($i = 11; $i >= 0; $i--) {
            $mStart = Carbon::now()->subMonths($i)->startOfMonth()->toDateString();
            $mEnd = Carbon::now()->subMonths($i)->endOfMonth()->toDateString();
            $months->push(Carbon::parse($mStart)->format('M Y'));

            $monthlyCashIn->push(
                Cashbook::where('entry_type', 'cash_in')
                    ->whereBetween('date', [$mStart, $mEnd])
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->sum('amount')
            );

            $monthlyCashOut->push(
                Cashbook::where('entry_type', 'cash_out')
                    ->whereBetween('date', [$mStart, $mEnd])
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->sum('amount')
            );
        }

        // Expense breakdown by category
        $expenseBreakdown = Expense::selectRaw('category_id, sum(amount) as total')
            ->whereBetween('date', [$fromDate, $toDate])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', 'approved')
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->map(fn ($r) => [
                'label' => $r->category->name ?? 'Uncategorized',
                'value' => $r->total,
            ]);

        // Aging buckets for receivables
        $aging = $this->accountsService->getCustomerAgingSummary($branchId);

        return view('accounts.reports.dashboard', compact(
            'cashIn', 'cashOut', 'expenses', 'customerOutstanding',
            'supplierOutstanding', 'activeInstallments', 'branches', 'branchId',
            'outstandingSummary', 'profitLoss', 'months', 'monthlyCashIn', 'monthlyCashOut',
            'expenseBreakdown', 'aging'
        ));
    }

    public function dayBook(Request $request): View
    {
        $query = Cashbook::query();

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->date) {
            $query->whereDate('date', $request->date);
        }

        $entries = $query->with(['branch', 'user'])
            ->orderBy('date')
            ->orderBy('id')
            ->paginate(50);

        $branches = Branch::where('is_active', true)->get();

        return view('accounts.reports.day-book', compact('entries', 'branches'));
    }

    public function trialBalance(Request $request): View
    {
        $fromDate = $request->query('from_date', now()->subMonth()->toDateString());
        $toDate = $request->query('to_date', now()->toDateString());

        $accounts = ChartOfAccount::where('is_active', true)
            ->with(['ledgerEntries' => function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('date', [$fromDate, $toDate]);
            }])
            ->get();

        $totalDebits = $accounts->sum(fn ($acc) => $acc->ledgerEntries->sum('debit'));
        $totalCredits = $accounts->sum(fn ($acc) => $acc->ledgerEntries->sum('credit'));
        $isBalanced = abs($totalDebits - $totalCredits) < 0.01;

        return view('accounts.reports.trial-balance', compact('accounts', 'totalDebits', 'totalCredits', 'isBalanced'));
    }

    public function ledgerStatement(Request $request): View
    {
        $accountId = $request->query('account_id');
        $fromDate = $request->query('from_date', now()->subMonth()->toDateString());
        $toDate = $request->query('to_date', now()->toDateString());

        if (! $accountId) {
            return back()->with('error', 'Please select an account');
        }

        $account = ChartOfAccount::findOrFail($accountId);
        $entries = $account->ledgerEntries()
            ->whereBetween('date', [$fromDate, $toDate])
            ->orderBy('date')
            ->orderBy('id')
            ->paginate(50);

        $accounts = ChartOfAccount::where('is_active', true)->get();

        return view('accounts.reports.ledger-statement', compact('account', 'entries', 'accounts'));
    }

    public function balanceSheet(Request $request): View
    {
        $asDate = $request->query('as_date', now()->toDateString());

        $accounts = ChartOfAccount::where('is_active', true)->get()->groupBy('account_type');

        $balanceSheet = [];
        foreach ($accounts as $type => $typeAccounts) {
            $balanceSheet[$type] = $typeAccounts->sum('current_balance');
        }

        return view('accounts.reports.balance-sheet', compact('balanceSheet', 'asDate'));
    }

    public function profitLoss(Request $request): View
    {
        $fromDate = $request->query('from_date', now()->subMonth()->toDateString());
        $toDate = $request->query('to_date', now()->toDateString());

        // Revenue accounts
        $revenue = GeneralLedger::whereHas('account', fn ($q) => $q->where('account_type', 'Revenue'))
            ->whereBetween('date', [$fromDate, $toDate])
            ->sum('credit');

        // Expense accounts
        $expenses = GeneralLedger::whereHas('account', fn ($q) => $q->where('account_type', 'Expense'))
            ->whereBetween('date', [$fromDate, $toDate])
            ->sum('debit');

        $netProfit = $revenue - $expenses;

        return view('accounts.reports.profit-loss', compact('revenue', 'expenses', 'netProfit', 'fromDate', 'toDate'));
    }

    public function outstandingSummary(Request $request): View
    {
        $branchId = $request->query('branch_id');

        $customerOutstanding = CustomerLedger::where('status', 'active')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with('customer')
            ->orderBy('current_balance', 'desc')
            ->paginate(30);

        $supplierOutstanding = SupplierLedger::where('status', 'active')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with('vendor')
            ->orderBy('current_balance', 'desc')
            ->paginate(30);

        $branches = Branch::where('is_active', true)->get();

        return view('accounts.reports.outstanding-summary', compact(
            'customerOutstanding', 'supplierOutstanding', 'branches', 'branchId'
        ));
    }

    public function installmentOverdue(): View
    {
        $overdueInstallments = Installment::where('status', 'active')
            ->with(['customer', 'plan', 'schedules' => function ($q) {
                $q->where('status', 'pending')
                    ->where('due_date', '<', now()->toDateString());
            }])
            ->get();

        return view('accounts.reports.installment-overdue', compact('overdueInstallments'));
    }

    public function taxSummary(Request $request): View
    {
        $fromDate = $request->query('from_date', now()->subMonth()->toDateString());
        $toDate = $request->query('to_date', now()->toDateString());
        $branchId = $request->query('branch_id');

        $taxEntries = \App\Models\TaxEntry::whereBetween('date', [$fromDate, $toDate])
            ->when($branchId, fn ($q) => $q->whereHas('taxConfig', fn ($q2) => $q2->where('branch_id', $branchId)))
            ->with(['taxConfig'])
            ->get()
            ->groupBy(fn ($entry) => $entry->taxConfig->tax_type);

        $branches = Branch::where('is_active', true)->get();

        return view('accounts.reports.tax-summary', compact('taxEntries', 'branches', 'branchId'));
    }

    public function auditTrail(Request $request): View
    {
        $query = \App\Models\AuditLog::query();

        if ($request->module) {
            $query->where('module', $request->module);
        }

        if ($request->action) {
            $query->where('action', $request->action);
        }

        if ($request->date_from && $request->date_to) {
            $query->whereBetween('created_at', [$request->date_from, $request->date_to]);
        }

        $logs = $query->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('accounts.reports.audit-trail', compact('logs'));
    }

    public function exportToPdf(Request $request)
    {
        $reportType = $request->query('type');

        // This would integrate with a PDF library like DomPDF or Spatie/Pdf
        return back()->with('info', 'PDF export feature coming soon');
    }

    public function exportToExcel(Request $request)
    {
        // This would integrate with Laravel Excel (Maatwebsite)
        return back()->with('info', 'Excel export feature coming soon');
    }
}
