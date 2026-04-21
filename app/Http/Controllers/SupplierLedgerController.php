<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\DebitCreditNote;
use App\Models\SupplierLedger;
use App\Models\SupplierLedgerEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupplierLedgerController extends Controller
{
    public function index(Request $request): View
    {
        $query = SupplierLedger::query();

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $ledgers = $query->with(['vendor', 'branch'])
            ->paginate(20);

        $branches = Branch::where('is_active', true)->get();

        return view('accounts.ledger.supplier.index', compact('ledgers', 'branches'));
    }

    public function show(SupplierLedger $ledger): View
    {
        $ledger->load(['vendor', 'branch', 'entries', 'debitCreditNotes']);

        // Get aging buckets
        $today = now();
        $entries = $ledger->entries()->orderBy('date')->get();

        return view('accounts.ledger.supplier.show', compact('ledger', 'entries'));
    }

    public function statement(SupplierLedger $ledger, Request $request): View
    {
        $query = $ledger->entries()->orderBy('date');

        if ($request->date_from && $request->date_to) {
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        }

        $entries = $query->paginate(30);

        return view('accounts.ledger.supplier.statement', compact('ledger', 'entries'));
    }

    public function createDebitCreditNote(Request $request, SupplierLedger $ledger): RedirectResponse
    {
        $validated = $request->validate([
            'note_type' => 'required|in:debit_note,credit_note',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:500',
        ]);

        $noteNumber = 'DCN-'.date('YmdHis');

        $note = DebitCreditNote::create([
            'supplier_ledger_id' => $ledger->id,
            'note_type' => $validated['note_type'],
            'reference_number' => $noteNumber,
            'date' => $validated['date'],
            'amount' => $validated['amount'],
            'reason' => $validated['reason'],
            'created_by' => Auth::id(),
            'status' => 'pending',
        ]);

        return back()->with('success', 'Debit/Credit note created: '.$noteNumber);
    }

    public function approveNote(DebitCreditNote $note): RedirectResponse
    {
        $note->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Update supplier ledger
        $ledger = $note->ledger;
        $adjustment = $note->note_type === 'credit_note' ? -$note->amount : $note->amount;
        $newBalance = $ledger->current_balance - $adjustment;

        SupplierLedgerEntry::create([
            'supplier_ledger_id' => $ledger->id,
            'date' => $note->date,
            'type' => $note->note_type === 'credit_note' ? 'credit' : 'debit',
            'amount' => $note->amount,
            'reference_type' => $note->note_type,
            'reference_id' => $note->id,
            'running_balance' => $newBalance,
            'description' => $note->reason,
        ]);

        $ledger->update(['current_balance' => $newBalance]);

        return back()->with('success', 'Note approved and ledger updated');
    }
}
