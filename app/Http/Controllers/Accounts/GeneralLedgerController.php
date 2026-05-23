<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\GeneralLedger;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GeneralLedgerController extends Controller
{
    public function index(Request $request)
    {
        $accountId  = $request->get('account_id');
        $fromDate   = Carbon::parse($request->get('from_date', Carbon::now()->startOfMonth()));
        $toDate     = Carbon::parse($request->get('to_date', Carbon::now()->endOfMonth()));
        $refType    = $request->get('ref_type');

        $query = GeneralLedger::with('account', 'journalEntry')
            ->whereBetween('date', [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')]);

        if ($accountId) $query->where('account_id', $accountId);
        if ($refType)   $query->where('reference_type', $refType);

        $ledgerEntries  = $query->orderBy('date')->orderBy('id')->paginate(50);
        $accounts       = ChartOfAccount::where('is_active', 1)->orderBy('account_code')->get();

        $openingBalance = 0;
        $closingBalance = 0;
        $selectedAccount = null;

        if ($accountId) {
            $selectedAccount = ChartOfAccount::find($accountId);

            $prevDebits  = GeneralLedger::where('account_id', $accountId)->where('date', '<', $fromDate->format('Y-m-d'))->sum('debit');
            $prevCredits = GeneralLedger::where('account_id', $accountId)->where('date', '<', $fromDate->format('Y-m-d'))->sum('credit');
            $openingBalance = $selectedAccount->opening_balance + ($prevDebits - $prevCredits);

            $curDebits  = GeneralLedger::where('account_id', $accountId)->where('date', '<=', $toDate->format('Y-m-d'))->sum('debit');
            $curCredits = GeneralLedger::where('account_id', $accountId)->where('date', '<=', $toDate->format('Y-m-d'))->sum('credit');
            $closingBalance = $selectedAccount->opening_balance + ($curDebits - $curCredits);

            $tempBalance = $openingBalance;
            foreach ($ledgerEntries as $entry) {
                $tempBalance += ($entry->debit - $entry->credit);
                $entry->running_balance = $tempBalance;
            }
        }

        // Trial Balance data
        $trialBalance = ChartOfAccount::where('is_active', 1)
            ->orderBy('account_code')
            ->get()
            ->map(function ($acc) use ($fromDate, $toDate) {
                $debits  = GeneralLedger::where('account_id', $acc->id)->whereBetween('date', [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')])->sum('debit');
                $credits = GeneralLedger::where('account_id', $acc->id)->whereBetween('date', [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')])->sum('credit');
                $acc->period_debit  = $debits;
                $acc->period_credit = $credits;
                $acc->net_balance   = $acc->opening_balance + $debits - $credits;
                return $acc;
            })->filter(fn($a) => $a->period_debit > 0 || $a->period_credit > 0 || $a->opening_balance != 0);

        return view('accounts.general-ledger.index', compact(
            'ledgerEntries', 'accounts', 'selectedAccount',
            'openingBalance', 'closingBalance', 'fromDate', 'toDate',
            'trialBalance'
        ));
    }

    public function show($accountId)
    {
        $account = ChartOfAccount::findOrFail($accountId);

        $ledgerEntries = GeneralLedger::where('account_id', $accountId)
            ->with('journalEntry')
            ->orderBy('date')->orderBy('id')
            ->paginate(50);

        $runningBalance = $account->opening_balance;
        foreach ($ledgerEntries as $entry) {
            $runningBalance += ($entry->debit - $entry->credit);
            $entry->running_balance = $runningBalance;
        }

        return view('accounts.general-ledger.show', compact('account', 'ledgerEntries'));
    }

    // ==================== CHART OF ACCOUNTS ====================

    public function coaIndex()
    {
        $accounts = ChartOfAccount::with('parent')->orderBy('account_code')->get();
        $parents  = ChartOfAccount::whereNull('parent_account_id')->orderBy('account_code')->get();
        return view('accounts.general-ledger.coa', compact('accounts', 'parents'));
    }

    public function coaStore(Request $request)
    {
        $data = $request->validate([
            'account_code'     => 'required|string|max:20|unique:chart_of_accounts,account_code',
            'account_name'     => 'required|string|max:255',
            'account_type'     => 'required|in:Asset,Liability,Equity,Income,Expense',
            'account_category' => 'nullable|string|max:100',
            'parent_account_id'=> 'nullable|exists:chart_of_accounts,id',
            'opening_balance'  => 'nullable|numeric',
            'description'      => 'nullable|string',
        ]);

        $data['is_active']       = true;
        $data['current_balance'] = $data['opening_balance'] ?? 0;

        ChartOfAccount::create($data);
        return redirect()->back()->with('success', 'Account created successfully.');
    }

    public function coaUpdate(Request $request, ChartOfAccount $account)
    {
        $data = $request->validate([
            'account_name'     => 'required|string|max:255',
            'account_type'     => 'required|in:Asset,Liability,Equity,Income,Expense',
            'account_category' => 'nullable|string|max:100',
            'opening_balance'  => 'nullable|numeric',
            'is_active'        => 'boolean',
            'description'      => 'nullable|string',
        ]);

        $account->update($data);
        return redirect()->back()->with('success', 'Account updated successfully.');
    }

    // ==================== MANUAL JOURNAL ENTRY ====================

    public function journalIndex()
    {
        $journals = JournalEntry::with(['items.account', 'createdBy'])
            ->latest('entry_date')->paginate(20);
        $accounts = ChartOfAccount::where('is_active', 1)->orderBy('account_code')->get();
        return view('accounts.general-ledger.journals', compact('journals', 'accounts'));
    }

    public function journalStore(Request $request)
    {
        $request->validate([
            'entry_date'    => 'required|date',
            'narration'     => 'required|string|max:500',
            'lines'         => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit'      => 'nullable|numeric|min:0',
            'lines.*.credit'     => 'nullable|numeric|min:0',
        ]);

        $totalDebit  = collect($request->lines)->sum('debit');
        $totalCredit = collect($request->lines)->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withInput()->with('error', 'Journal entry is not balanced. Total Debit must equal Total Credit.');
        }

        DB::transaction(function () use ($request) {
            $refNumber = 'JV-' . date('Ymd') . '-' . str_pad(JournalEntry::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            $journal = JournalEntry::create([
                'reference_number' => $refNumber,
                'entry_date'       => $request->entry_date,
                'narration'        => $request->narration,
                'status'           => 'posted',
                'created_by'       => Auth::id(),
            ]);

            foreach ($request->lines as $line) {
                $debit  = (float)($line['debit']  ?? 0);
                $credit = (float)($line['credit'] ?? 0);
                if ($debit == 0 && $credit == 0) continue;

                JournalEntryItem::create([
                    'journal_entry_id' => $journal->id,
                    'account_id'       => $line['account_id'],
                    'debit'            => $debit,
                    'credit'           => $credit,
                    'description'      => $line['description'] ?? $request->narration,
                ]);

                // Post to General Ledger
                GeneralLedger::create([
                    'account_id'     => $line['account_id'],
                    'date'           => $request->entry_date,
                    'entry_type'     => 'journal',
                    'reference_type' => 'journal_entry',
                    'reference_id'   => $journal->id,
                    'debit'          => $debit,
                    'credit'         => $credit,
                    'description'    => $request->narration,
                    'created_by'     => Auth::id(),
                ]);

                // Update account current balance
                $account = ChartOfAccount::find($line['account_id']);
                $account->increment('current_balance', $debit - $credit);
            }
        });

        return redirect()->route('accounts.general-ledger.journals')->with('success', 'Journal entry posted successfully.');
    }

    // ==================== EXPORTS ====================

    public function exportPdf(Request $request)
    {
        $accountId = $request->get('account_id');
        $fromDate  = Carbon::parse($request->get('from_date', Carbon::now()->startOfMonth()));
        $toDate    = Carbon::parse($request->get('to_date', Carbon::now()->endOfMonth()));
        $account   = $accountId ? ChartOfAccount::findOrFail($accountId) : null;

        $query = GeneralLedger::with('account', 'journalEntry')
            ->whereBetween('date', [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')]);
        if ($accountId) $query->where('account_id', $accountId);
        $ledgerEntries = $query->orderBy('date')->orderBy('id')->get();

        $openingBalance = 0;
        if ($account) {
            $prevDebits  = GeneralLedger::where('account_id', $accountId)->where('date', '<', $fromDate->format('Y-m-d'))->sum('debit');
            $prevCredits = GeneralLedger::where('account_id', $accountId)->where('date', '<', $fromDate->format('Y-m-d'))->sum('credit');
            $openingBalance = $account->opening_balance + ($prevDebits - $prevCredits);
        }

        $runningBalance = $openingBalance;
        foreach ($ledgerEntries as $entry) {
            $runningBalance += ($entry->debit - $entry->credit);
            $entry->running_balance = $runningBalance;
        }

        $pdf = \PDF::loadView('accounts.general-ledger.pdf', compact('ledgerEntries', 'account', 'openingBalance', 'fromDate', 'toDate'));
        return $pdf->download('general-ledger-' . ($account ? $account->account_code : 'all') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $accountId = $request->get('account_id');
        $fromDate  = Carbon::parse($request->get('from_date', Carbon::now()->startOfMonth()));
        $toDate    = Carbon::parse($request->get('to_date', Carbon::now()->endOfMonth()));
        $account   = $accountId ? ChartOfAccount::find($accountId) : null;

        $query = GeneralLedger::with('account', 'journalEntry')
            ->whereBetween('date', [$fromDate->format('Y-m-d'), $toDate->format('Y-m-d')]);
        if ($accountId) $query->where('account_id', $accountId);
        $entries = $query->orderBy('date')->orderBy('id')->get();

        $filename = 'general-ledger-' . now()->format('Y-m-d') . '.csv';
        $headers  = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename={$filename}"];

        $callback = function () use ($entries, $account, $fromDate, $toDate) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['GENERAL LEDGER REPORT']);
            fputcsv($file, ['Account:', $account ? $account->account_code . ' - ' . $account->account_name : 'All Accounts']);
            fputcsv($file, ['Period:', $fromDate->format('d M Y') . ' to ' . $toDate->format('d M Y')]);
            fputcsv($file, []);
            fputcsv($file, ['Date', 'Reference', 'Account', 'Description', 'Debit', 'Credit', 'Balance']);

            $balance = 0;
            foreach ($entries as $e) {
                $balance += ($e->debit - $e->credit);
                fputcsv($file, [
                    $e->date->format('d/m/Y'),
                    $e->journalEntry->reference_number ?? 'N/A',
                    $e->account->account_name ?? '',
                    $e->description,
                    $e->debit > 0 ? number_format($e->debit, 2) : '',
                    $e->credit > 0 ? number_format($e->credit, 2) : '',
                    number_format($balance, 2),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
