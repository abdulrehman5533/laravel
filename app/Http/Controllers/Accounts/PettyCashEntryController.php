<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Cashbook;
use App\Models\PettyCash;
use App\Models\PettyCashEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PettyCashEntryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage-accounts')->only(['approve', 'reject', 'reconcile']);
    }

    public function store(Request $request, PettyCash $pettycash): RedirectResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:cash_in,cash_out',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['petty_cash_id'] = $pettycash->id;
        $validated['requested_by'] = Auth::id();
        $validated['status'] = 'pending';

        PettyCashEntry::create($validated);

        return redirect()->route('accounts.pettycash.show', $pettycash)->with('success', 'Petty cash entry requested');
    }

    public function approve(Request $request, PettyCashEntry $entry): RedirectResponse
    {
        if ($entry->status !== 'pending') {
            return back()->with('error', 'Only pending entries can be approved');
        }

        $entry->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // 1. Update petty cash balance
        $petty = $entry->pettyCash;
        if ($entry->type === 'cash_out') {
            $petty->current_balance = max(0, $petty->current_balance - $entry->amount);
        } else {
            $petty->current_balance = $petty->current_balance + $entry->amount;
        }
        $petty->save();

        // 2. Post to General Ledger
        $glService = app(\App\Services\GeneralLedgerService::class);
        $glService->postPettyCashEntry($entry);

        return back()->with('success', 'Entry approved');
    }

    public function reject(Request $request, PettyCashEntry $entry): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($entry->status !== 'pending') {
            return back()->with('error', 'Only pending entries can be rejected');
        }

        $entry->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Entry rejected');
    }

    public function reconcile(Request $request, PettyCashEntry $entry): RedirectResponse
    {
        if ($entry->status !== 'approved') {
            return back()->with('error', 'Only approved entries can be reconciled');
        }

        if ($entry->status === 'reconciled') {
            return back()->with('error', 'Entry already reconciled');
        }

        $petty = $entry->pettyCash;

        // Create a cashbook entry to reflect reconciliation
        $cashbook = Cashbook::create([
            'branch_id' => $petty->branch_id,
            'user_id' => Auth::id(),
            'date' => $entry->date,
            'entry_type' => $entry->type === 'cash_out' ? 'cash_out' : 'cash_in',
            'category' => 'Petty Cash Reconcile',
            'amount' => $entry->amount,
            'description' => $entry->description,
            'payment_method' => 'cash',
            'status' => 'verified',
            'reference_type' => 'pettycash_entry',
            'reference_id' => $entry->id,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        $entry->update(['status' => 'reconciled']);

        return back()->with('success', 'Entry reconciled to cashbook');
    }
}
