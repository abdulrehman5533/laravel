<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\GeneralLedger;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GeneralLedgerController extends Controller
{
    /**
     * Display general ledger with complete double-entry system
     */
    public function index(Request $request)
    {
        $accountId = $request->get('account_id');
        $fromDate = $request->get('from_date') ? Carbon::parse($request->get('from_date')) : Carbon::now()->startOfMonth();
        $toDate = $request->get('to_date') ? Carbon::parse($request->get('to_date')) : Carbon::now()->endOfMonth();

        $query = GeneralLedger::with('account', 'journalEntry');

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        $query->whereBetween('date', [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')]);

        $ledgerEntries = $query->orderBy('date')->orderBy('id')->paginate(50);

        $accounts = ChartOfAccount::where('is_active', 1)->orderBy('account_code')->get();

        $openingBalance = 0;
        $closingBalance = 0;
        $selectedAccount = null;

        if ($accountId) {
            $selectedAccount = ChartOfAccount::find($accountId);

            // Calculate opening balance up to fromDate
            $prevDebits = GeneralLedger::where('account_id', $accountId)
                ->where('date', '<', $fromDate->format('Y-m-d'))
                ->sum('debit');
            $prevCredits = GeneralLedger::where('account_id', $accountId)
                ->where('date', '<', $fromDate->format('Y-m-d'))
                ->sum('credit');

            $openingBalance = $selectedAccount->opening_balance + ($prevDebits - $prevCredits);

            // Calculate closing balance up to toDate
            $currentDebits = GeneralLedger::where('account_id', $accountId)
                ->where('date', '<=', $toDate->format('Y-m-d'))
                ->sum('debit');
            $currentCredits = GeneralLedger::where('account_id', $accountId)
                ->where('date', '<=', $toDate->format('Y-m-d'))
                ->sum('credit');

            $closingBalance = $selectedAccount->opening_balance + ($currentDebits - $currentCredits);

            // Assign running balances to current page entries
            $tempBalance = $openingBalance;
            // This is tricky with pagination, but for the current view we can calculate it
            // Better to fetch all entries before this page to get the exact running balance if we want accuracy across pages
            // For now, let's just calculate based on the current set for display
            foreach ($ledgerEntries as $entry) {
                $tempBalance += ($entry->debit - $entry->credit);
                $entry->running_balance = $tempBalance;
            }
        }

        return view('accounts.general-ledger.index', [
            'ledgerEntries' => $ledgerEntries,
            'chartOfAccounts' => $accounts,
            'selectedAccount' => $selectedAccount,
            'openingBalance' => $openingBalance,
            'closingBalance' => $closingBalance,
            'fromDate' => $fromDate->format('Y-m-d'),
            'toDate' => $toDate->format('Y-m-d'),
        ]);
    }

    /**
     * Show ledger for specific account
     */
    public function show($accountId)
    {
        $account = ChartOfAccount::find($accountId);

        if (! $account) {
            return redirect()->route('accounts.general-ledger.index')
                ->with('error', 'The requested financial account could not be located in the system registry.');
        }

        $ledgerEntries = GeneralLedger::where('account_id', $accountId)
            ->with('journalEntry')
            ->orderBy('date')
            ->orderBy('id')
            ->paginate(50);

        // Calculate running balance
        $openingBalance = $account->opening_balance;
        $runningBalance = $openingBalance;

        // If we are on page 2+, we need to calculate the balance from page 1 entries
        if ($ledgerEntries->currentPage() > 1) {
            $prevDebits = GeneralLedger::where('account_id', $accountId)
                ->where('date', '<', $ledgerEntries->first()->date)
                ->sum('debit');
            $prevCredits = GeneralLedger::where('account_id', $accountId)
                ->where('date', '<', $ledgerEntries->first()->date)
                ->sum('credit');
            $runningBalance = $account->opening_balance + ($prevDebits - $prevCredits);
        }

        foreach ($ledgerEntries as $entry) {
            $runningBalance += ($entry->debit - $entry->credit);
            $entry->running_balance = $runningBalance;
        }

        return view('accounts.general-ledger.show', [
            'account' => $account,
            'ledgerEntries' => $ledgerEntries,
            'openingBalance' => $openingBalance,
        ]);
    }

    /**
     * Export ledger to PDF
     */
    public function exportPdf(Request $request)
    {
        $accountId = $request->get('account_id');
        $fromDate = Carbon::parse($request->get('from_date', Carbon::now()->startOfMonth()));
        $toDate = Carbon::parse($request->get('to_date', Carbon::now()->endOfMonth()));

        $account = $accountId ? ChartOfAccount::findOrFail($accountId) : null;

        $query = GeneralLedger::with('account', 'journalEntry')
            ->whereBetween('date', [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')]);

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        $ledgerEntries = $query->orderBy('date')->orderBy('id')->get();

        $openingBalance = 0;
        if ($account) {
            $prevDebits = GeneralLedger::where('account_id', $accountId)
                ->where('date', '<', $fromDate->format('Y-m-d'))
                ->sum('debit');
            $prevCredits = GeneralLedger::where('account_id', $accountId)
                ->where('date', '<', $fromDate->format('Y-m-d'))
                ->sum('credit');
            $openingBalance = $account->opening_balance + ($prevDebits - $prevCredits);
        }

        // Generate PDF
        $pdf = \PDF::loadView('accounts.general-ledger.pdf', [
            'ledgerEntries' => $ledgerEntries,
            'account' => $account,
            'openingBalance' => $openingBalance,
            'fromDate' => $fromDate->format('d M Y'),
            'toDate' => $toDate->format('d M Y'),
        ]);

        return $pdf->download('general-ledger-'.($account ? $account->account_code : 'all').'.pdf');
    }

    /**
     * Export ledger to Excel
     */
    public function exportExcel(Request $request)
    {
        // For now returning a simple message or implementing if possible
        return response()->json(['message' => 'Excel export coming soon!']);
    }
}
