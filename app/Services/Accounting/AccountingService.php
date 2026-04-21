<?php

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use App\Models\GeneralLedger;
use App\Models\JournalEntry;
use App\Models\PeriodLock;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    /**
     * Create a balanced Journal Entry and post to General Ledger
     *
     * @param  array  $data  [entry_date, reference_number, narration, items => [[account_id, debit, credit, description]]]
     */
    public function createJournalEntry(array $data): JournalEntry
    {
        // 1. Period Lock Check
        if ($this->isPeriodLocked($data['entry_date'], $data['branch_id'] ?? null)) {
            throw new \Exception("The accounting period for {$data['entry_date']} is locked.");
        }

        return DB::transaction(function () use ($data) {
            // 2. Strict Debit = Credit Validation
            $totalDebit = collect($data['items'])->sum('debit');
            $totalCredit = collect($data['items'])->sum('credit');

            // Using BC Math if available or small epsilon for precision
            if (abs($totalDebit - $totalCredit) > 0.0001) {
                throw new \Exception("Journal entry is not balanced. Total Debit: {$totalDebit}, Total Credit: {$totalCredit}");
            }

            $journal = JournalEntry::create([
                'reference_number' => $data['reference_number'],
                'entry_date' => $data['entry_date'],
                'narration' => $data['narration'],
                'status' => $data['status'] ?? 'posted',
                'created_by' => Auth::id(),
                'approved_by' => ($data['status'] ?? 'posted') === 'posted' ? Auth::id() : null,
                'approved_at' => ($data['status'] ?? 'posted') === 'posted' ? now() : null,
            ]);

            foreach ($data['items'] as $item) {
                $journal->items()->create([
                    'account_id' => $item['account_id'],
                    'debit' => $item['debit'],
                    'credit' => $item['credit'],
                    'description' => $item['description'] ?? $data['narration'],
                ]);

                if ($journal->status === 'posted') {
                    $this->postToLedger($journal, $item, $data['reference_type'] ?? 'journal');
                }
            }

            return $journal;
        });
    }

    public function getAccountByCode(string $code): ChartOfAccount
    {
        $account = ChartOfAccount::where('account_code', $code)->first();
        if (! $account) {
            throw new \Exception("Chart of Account with code '{$code}' not found in the registry.");
        }

        return $account;
    }

    /**
     * Check if a period is locked for a given date
     */
    public function isPeriodLocked(string $date, ?int $branchId = null): bool
    {
        $query = PeriodLock::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->where('status', 'locked');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->exists();
    }

    /**
     * Post an entry to the General Ledger and update account balance
     */
    private function postToLedger(JournalEntry $journal, array $item, string $referenceType): void
    {
        // Use pessimistic locking for the account to prevent race conditions
        $account = ChartOfAccount::where('id', $item['account_id'])->lockForUpdate()->firstOrFail();

        $balanceChange = $item['debit'] - $item['credit'];
        $newBalance = $account->current_balance + $balanceChange;

        GeneralLedger::create([
            'account_id' => $account->id,
            'date' => $journal->entry_date,
            'entry_type' => 'auto_entry',
            'reference_type' => $referenceType,
            'reference_id' => $journal->id,
            'debit' => $item['debit'],
            'credit' => $item['credit'],
            'running_balance' => $newBalance,
            'description' => $item['description'] ?? $journal->narration,
            'created_by' => Auth::id(),
        ]);

        // Update current balance using the locked model
        $account->current_balance = $newBalance;
        $account->save();
    }

    /**
     * Perform year-end closing
     * Zeros out Revenue and Expense accounts and transfers to Retained Earnings
     */
    public function closeFinancialYear(string $endDate, int $branchId, int $userId): void
    {
        DB::transaction(function () use ($endDate, $branchId, $userId) {
            $startDate = Carbon::parse($endDate)->startOfYear()->toDateString();

            // 1. Calculate Net Profit/Loss
            $revenueAccounts = ChartOfAccount::where('account_type', 'Revenue')->get();
            $expenseAccounts = ChartOfAccount::where('account_type', 'Expense')->get();

            $totalRevenue = 0;
            foreach ($revenueAccounts as $account) {
                $totalRevenue += $account->current_balance; // Revenue usually has credit balance (negative in some systems, but here we assume absolute current_balance matches normal direction)
                // Actually, let's be more precise and check the Ledger
            }

            // Safer way: Use the FinancialReportService logic
            $reportService = app(\App\Services\FinancialReportService::class);
            $pl = $reportService->getProfitAndLoss($startDate, $endDate, $branchId);
            $netProfit = $pl['net_profit'];

            if ($netProfit == 0) {
                return;
            }

            // 2. Create Closing Journal Entry
            $retainedEarningsAcc = $this->getAccountByCode(config('accounting.codes.retained_earnings', '3020'));

            $items = [];

            // Zero out Revenue (Debit Revenue to decrease)
            foreach ($revenueAccounts as $account) {
                if ($account->current_balance != 0) {
                    $items[] = [
                        'account_id' => $account->id,
                        'debit' => $account->current_balance,
                        'credit' => 0,
                        'description' => 'Year-end closing: Zeroing Revenue',
                    ];
                }
            }

            // Zero out Expenses (Credit Expense to decrease)
            foreach ($expenseAccounts as $account) {
                if ($account->current_balance != 0) {
                    $items[] = [
                        'account_id' => $account->id,
                        'debit' => 0,
                        'credit' => $account->current_balance,
                        'description' => 'Year-end closing: Zeroing Expense',
                    ];
                }
            }

            // Transfer to Retained Earnings
            if ($netProfit > 0) {
                // Profit: Credit Retained Earnings
                $items[] = [
                    'account_id' => $retainedEarningsAcc->id,
                    'debit' => 0,
                    'credit' => $netProfit,
                    'description' => 'Year-end closing: Net Profit transfer',
                ];
            } else {
                // Loss: Debit Retained Earnings
                $items[] = [
                    'account_id' => $retainedEarningsAcc->id,
                    'debit' => abs($netProfit),
                    'credit' => 0,
                    'description' => 'Year-end closing: Net Loss transfer',
                ];
            }

            $this->createJournalEntry([
                'reference_number' => 'YEC-'.Carbon::parse($endDate)->year,
                'entry_date' => $endDate,
                'narration' => 'Financial Year End Closing for '.Carbon::parse($endDate)->year,
                'branch_id' => $branchId,
                'items' => $items,
                'reference_type' => 'year_end_close',
            ]);

            // 3. Lock the period
            PeriodLock::create([
                'branch_id' => $branchId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'period_type' => 'annual',
                'status' => 'locked',
                'locked_by' => $userId,
                'locked_at' => now(),
                'lock_reason' => 'Annual Year-end Closing',
            ]);
        });
    }

    /**
     * Reverse a journal entry
     */
    public function reverseJournalEntry(string $referenceNumber, string $reason): void
    {
        DB::transaction(function () use ($referenceNumber, $reason) {
            $originalJournal = JournalEntry::where('reference_number', $referenceNumber)->first();
            if (! $originalJournal) {
                return;
            }

            // Create reversal journal
            $reversalData = [
                'reference_number' => 'REV-'.$originalJournal->reference_number.'-'.time(),
                'entry_date' => now()->toDateString(),
                'narration' => "Reversal of {$originalJournal->reference_number}. Reason: {$reason}",
                'items' => [],
            ];

            foreach ($originalJournal->items as $item) {
                $reversalData['items'][] = [
                    'account_id' => $item->account_id,
                    'debit' => $item->credit,
                    'credit' => $item->debit,
                    'description' => 'Reversal: '.$item->description,
                ];
            }

            $this->createJournalEntry($reversalData);
            $originalJournal->update(['status' => 'cancelled']);
        });
    }
}
