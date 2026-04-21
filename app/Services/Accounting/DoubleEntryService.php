<?php

namespace App\Services\Accounting;

use App\Models\GeneralLedger;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DoubleEntryService
{
    /**
     * Post a balanced journal entry.
     */
    public function post(array $data)
    {
        return DB::transaction(function () use ($data) {
            $journalEntry = JournalEntry::create([
                'date' => $data['date'] ?? now(),
                'reference_type' => $data['reference_type'],
                'reference_id' => $data['reference_id'],
                'description' => $data['description'],
                'created_by' => Auth::id() ?? 1,
            ]);

            foreach ($data['items'] as $item) {
                JournalEntryItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $item['account_id'],
                    'debit' => $item['debit'] ?? 0,
                    'credit' => $item['credit'] ?? 0,
                    'description' => $item['description'] ?? $data['description'],
                ]);

                // Update General Ledger
                $this->updateGeneralLedger($item, $data, $journalEntry->id);
            }

            $this->validateBalance($journalEntry);

            return $journalEntry;
        });
    }

    protected function updateGeneralLedger($item, $data, $journalEntryId)
    {
        GeneralLedger::create([
            'account_id' => $item['account_id'],
            'date' => $data['date'] ?? now(),
            'entry_type' => ($item['debit'] ?? 0) > 0 ? 'debit' : 'credit',
            'reference_type' => 'JournalEntry',
            'reference_id' => $journalEntryId,
            'debit' => $item['debit'] ?? 0,
            'credit' => $item['credit'] ?? 0,
            'description' => $item['description'] ?? $data['description'],
            'created_by' => Auth::id() ?? 1,
        ]);
    }

    protected function validateBalance($journalEntry)
    {
        $totalDebit = $journalEntry->items()->sum('debit');
        $totalCredit = $journalEntry->items()->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.001) {
            throw new \Exception("Journal entry is not balanced. Debit: $totalDebit, Credit: $totalCredit");
        }
    }
}
